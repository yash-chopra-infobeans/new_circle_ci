<?php

namespace IDG\Asset_Manager;

use \Firebase\JWT\JWT;

/**
 * Class for verifying JWT tokens when set within the Authorization header in a request.
 */
class Webhooks {
	/**
	 * Verify that the incoming post request is coming from a valid source (JW Player).
	 *
	 * @return bool
	 */
	public function verify() : bool {
		$jwt_token = $this->get_token();
		// phpcs:ignore WordPressVIPMinimum.Performance.FetchingRemoteData.FileGetContentsRemoteFile
		$webhook_payload = json_decode( file_get_contents( 'php://input' ), true );

		try {
			$jwt_payload = JWT::decode( $jwt_token, JW_PLAYER_API_WEBHOOK_SECRET, [ 'HS256' ] );
		} catch ( \Exception $e ) {
			$jwt_payload = false;
		}

		if ( ! $jwt_payload ) {
			return false;
		}

		// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		return (array) $jwt_payload == (array) $webhook_payload;
	}

	/**
	 * Get Authorization header.
	 *
	 * @return string
	 */
	protected function get_authorization_header() : string {
		$headers = null;

		if ( isset( $_SERVER['Authorization'] ) ) {
			$headers = trim( $_SERVER['Authorization'] );
		} elseif ( isset( $_SERVER['HTTP_AUTHORIZATION'] ) ) {
			$headers = trim( $_SERVER['HTTP_AUTHORIZATION'] );
		} elseif ( function_exists( 'apache_request_headers' ) ) {
			$request_headers = apache_request_headers();
			$request_headers = array_combine( array_map( 'ucwords', array_keys( $request_headers ) ), array_values( $request_headers ) );

			if ( isset( $request_headers['Authorization'] ) ) {
				$headers = trim( $request_headers['Authorization'] );
			}
		}

		return $headers;
	}

	/**
	 * Get bearer token from Authorization header.
	 *
	 * @return null|string
	 */
	protected function get_token() {
		$headers = $this->get_authorization_header();

		if ( false === empty( $headers ) ) {
			if ( preg_match( '/Bearer\s(\S+)/', $headers, $matches ) ) {
				$str = $matches[1];

				return $str;
			}
		}

		return null;
	}
}
