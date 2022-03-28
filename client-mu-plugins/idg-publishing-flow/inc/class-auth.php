<?php

namespace IDG\Publishing_Flow;

use WP\OAuth2\Client as oAuth2_Client;

/**
 * Handles the authentication processes for pairing
 * Delivery sites and Content Hub.
 */
class Auth {
	/**
	 * Attach the required filters for the class.
	 */
	public function __construct() {
		// @todo On the delivery sites, we need a way to register the content up url to add in here.
		add_filter(
			'allowed_redirect_hosts',
			function( $allowed_hosts ) {
				return array_merge( $allowed_hosts, [ PUBLISHING_FLOW_EXPECTED_SOURCE_URL ] );
			}
		);
		add_filter( 'register_post_type_args', [ $this, 'update_oauth2_caps' ], 10, 2 );

		add_action( 'init', [ $this, 'intercept_redirect_page' ] );
	}

	/**
	 * To regenerate auth keys, the user requires specific capabilities,
	 * so we intercept the registration and grant the appropriate caps for
	 * the auth post type.
	 *
	 * @param array  $args The arguments passed down to post type registration.
	 * @param string $post_type The post type being registered.
	 * @return array
	 */
	public function update_oauth2_caps( $args, $post_type ) {
		if ( ! class_exists( oAuth2_Client::class ) || oAuth2_Client::POST_TYPE !== $post_type ) {
			return $args;
		}

		$args['capabilities']['edit_post'] = 'edit_users';

		return $args;
	}

	/**
	 * Creates the callback uri that is to be
	 * sent with all auth requests.
	 *
	 * @return string
	 */
	public static function create_callback_uri() : string {
		return get_site_url() . '/auth/delivery/callback';
	}

	/**
	 * Creates a url that can be used for retrieving the
	 * access token from a WordPress OAuth2 service.
	 *
	 * @param string $url The target url.
	 * @param string $client_id The client id provided by the target.
	 * @return string
	 */
	public static function create_token_url( string $url, string $client_id ) : string {
		if ( empty( $url ) || empty( $client_id ) ) {
			return false;
		}

		$callback_uri = self::create_callback_uri();

		$http = 'http';

		if ( \is_ssl() ) {
			$http = 'https';
		}

		return "$http://$url/wp-login.php?action=oauth2_authorize&client_id=$client_id&response_type=code&redirect_uri=$callback_uri";
	}

	/**
	 * Creates the required URL for retrieving the access token.
	 *
	 * @param string $host The host for the URL.
	 * @param string $client_id Client ID/Key as provided by oAuth2.
	 * @param string $code Auth code to retrieve the access token.
	 * @return string
	 */
	public static function create_access_token_uri( $host, $client_id, $code ) : string {
		$callback_uri = self::create_callback_uri();

		return "$host/wp-json/oauth2/access_token?client_id=$client_id&redirect_uri=$callback_uri&code=$code&grant_type=authorization_code";
	}

	/**
	 * Intercept the page before everything else is loaded to
	 * create token requests and attempt redirect pack to
	 * publication taxonomy.
	 *
	 * @return void
	 */
	public function intercept_redirect_page() : void {
		// $wp->request global not set yet, have to use this.
		$server_request  = filter_input( INPUT_SERVER, 'REQUEST_URI', FILTER_VALIDATE_DOMAIN );
		$request_path    = wp_parse_url( $server_request, PHP_URL_PATH );
		$request_referer = wp_get_raw_referer();

		idg_notify_error(
			'ContentHub',
			'intercept redirect page',
			[
				'server_request'  => $server_request,
				'request_path'    => $request_path,
				'request_referer' => $request_referer,
			]
		);

		if ( '/auth/delivery/callback' !== $request_path || ! $request_referer ) {
			return;
		}

		if ( ! is_user_logged_in() && ! current_user_can( 'administrator' ) ) {
			return;
		}

		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- Sorry, but this makes things easier and data comes from known source.
		extract( wp_parse_url( $request_referer ) );

		if ( ! empty( $port ) ) {
			$host .= ":$port";
		}

		$publications  = Sites::get_publications( true );
		$matching_host = false;

		foreach ( $publications as $publication ) {
			$term_host = get_term_meta( $publication->term_id, Sites::TERM_META_HOST, true );

			if ( preg_replace( '#^www\.(.+\.)#i', '$1', $host ) === $term_host ) {
				$matching_host = $publication;
				break;
			}
		}

		idg_notify_error(
			'ContentHub',
			'Check intercept host',
			[
				'publications'  => $publications,
				'host'          => $host,
				'matching_host' => $matching_host,
			]
		);

		if ( ! $matching_host ) {
			return;
		}

		$code      = filter_input( INPUT_GET, 'code', FILTER_SANITIZE_STRING );
		$client_id = get_term_meta( $matching_host->term_id, Sites::TERM_META_CLIENT, true );

		$access_token_uri = self::create_access_token_uri( "https://$host", $client_id, $code );

		$response = wp_remote_post( $access_token_uri );

		idg_notify_error(
			'ContentHub',
			'Check intercept response',
			[
				'access_token_uri' => $access_token_uri,
				'response'         => $response,
			]
		);

		update_term_meta( $matching_host->term_id, Sites::TERM_META_ACCESS_TOKEN, $response['body'] );

		$redirect_to = get_site_url() . "/wp-admin/term.php?taxonomy=publication&tag_ID=$matching_host->term_id";

		wp_safe_redirect( $redirect_to );
		exit;
	}
}
