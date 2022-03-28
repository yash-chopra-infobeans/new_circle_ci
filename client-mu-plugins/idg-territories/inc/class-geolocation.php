<?php

namespace IDG\Territories;

/**
 * Management of geolocation.
 */
class Geolocation {
	/**
	 * Add actions.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'init' ], 1 );
	}

	/**
	 * Determine if geo is enabled.
	 *
	 * @return boolean
	 */
	public static function is_enabled() : bool {
		return isset( $_SERVER['GEOIP_COUNTRY_CODE'] ) && ! empty( $_SERVER['GEOIP_COUNTRY_CODE'] );
	}

	/**
	 * If the country code is set at a superglobal level return it.
	 *
	 * @return string|null
	 */
	public static function get_country_code() {
		if ( self::is_enabled() ) {
			return sanitize_text_field( $_SERVER['GEOIP_COUNTRY_CODE'] );
		}

		return null;
	}

	/**
	 * Return the geolocated territory, if there is a match.
	 *
	 * @return Territory|null
	 */
	public static function get_territory() {
		$country_code = self::get_country_code();

		if ( ! $country_code ) {
			return null;
		}

		$territory_term = Territory_Loader::territory_term( $country_code ) ?? null;

		if ( $territory_term ) {
			$territory = Territory_Loader::territory( $territory_term );

			return $territory;
		}

		return null;
	}

	/**
	 * Set the correct headers on init.
	 *
	 * @return void
	 */
	public function init() {
		if ( is_admin() ) {
			return;
		}

		if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
			return;
		}

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}

		add_action(
			'send_headers',
			function() {
				header( 'Vary: X-Country-Code', false );
			}
		);
	}
}
