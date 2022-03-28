<?php
/**
 * File to disallow access.
 * 
 * @package IDG
 */

namespace IDG\Blocked_Access;

/**
 * Class to block access to defined users/groups/countries.
 */
class Blocked_Access {
	const BLOCKED_COUNTRIES = [ 'KP', 'IR' ];

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
		return self::is_enabled() ? strip_tags( $_SERVER['GEOIP_COUNTRY_CODE'] ) : null; //phpcs:ignore -- Detected usage of a non-sanitized input variable.
	}

	/**
	 * Return 403 error, if there is a match.
	 *
	 * @return null
	 */
	public static function check_access() {
		$country_code = self::get_country_code();
		if ( ! $country_code ) {
			return null;
		} elseif ( in_array( self::get_country_code(), self::BLOCKED_COUNTRIES, true ) ) {
			header( 'HTTP/1.0 403 Forbidden' );
			header( 'Vary: X-Country-Code', false );
			die( 'You are not allowed to access.' );
		}
		return null;
	}
}

$blocked_access = new \IDG\Blocked_Access\Blocked_Access();
$blocked_access->check_access();
