<?php
/**
 * These functions are mostly used to get product details from amazon.
 *
 * @package idg-products plugin
 */

namespace IDG\Products\Vendors;

use IDG\Products\Article;
use IDG\Territories\Geolocation;

/**
 * Amazon vendor.
 */
class Amazon {
	const MAX_ASINS = 10; // ASIN Codes get chunked into this value before sending in API request.

	const CACHE_GROUP = 'idg_amazon_prices';

	const CACHE_EXPIRY = 3600; // 1 Hour

	const FALLBACK_DEFAULT = 'US';

	/**
	 * Api details.
	 *
	 * @var array
	 */
	public static $api_details = null;

	/**
	 * A fallback call flag.
	 *
	 * @var bool
	 */
	public static $call_fallback = false;

	/**
	 * Get api details.
	 *
	 * @return null|array
	 * @SuppressWarnings(PHPMD)
	 */
	public static function get_api_details() {
		$geolocation = Geolocation::get_country_code() ?: self::FALLBACK_DEFAULT;

		if ( self::$api_details &&
			! self::$call_fallback &&
			self::$api_details['geolocation'] === $geolocation
		) {
			return self::$api_details;
		}

		$api_details = cf_get_value( 'global_settings', 'amazon_api_settings', 'amazon_api_fields' );

		if (
			empty( $api_details['amazon_api_origin'] ) ||
			empty( $api_details['amazon_account'] ) ||
			empty( $api_details['amazon_track_ids'] ) ||
			! is_array( $api_details['amazon_track_ids'] )
		) {
			return null;
		}

		$territories          = wp_list_pluck( $api_details['amazon_track_ids'], 'territory' );
		$supports_current_geo = in_array( $geolocation, $territories, true );

		$amazon_track_id = '';
		foreach ( $api_details['amazon_track_ids'] as $track_id ) {
			if ( ! $supports_current_geo && strtoupper( $track_id['territory'] ) === self::FALLBACK_DEFAULT ) {
				$amazon_track_id = $track_id['track_id'];
				continue;
			}

			if ( strtoupper( $track_id['territory'] ) === strtoupper( $geolocation ) ) {
				$amazon_track_id = $track_id['track_id'];
			}
		}

		// Settings haven't been set.
		if ( empty( $amazon_track_id ) ) {
			return null;
		}

		$api_details['amazon_track_id'] = $amazon_track_id;
		$api_details['geolocation']     = $geolocation;

		self::$api_details = $api_details;

		return $api_details;
	}

	/**
	 * Batch fetch amazon items by asin codes.
	 *
	 * @param array $products - Products.
	 * @return array
	 * @SuppressWarnings(PHPMD)
	 */
	public static function fetch( $products = [] ) : array {
		$api_details = self::get_api_details();
		$geolocation = $api_details['geolocation'];

		if ( ! $api_details ) {
			return [];
		}

		if ( empty( $products ) ) {
			$products = Article::get_products( get_the_ID() );
		}

		if ( empty( $products ) ) {
			return [];
		}

		$asin_codes = self::get_asin_codes_from_products( $products );
		if ( empty( $asin_codes ) ) {
			return [];
		}

		$cache_key = "{$api_details['geolocation']}_" . hash( 'md5', wp_json_encode( $asin_codes ) );
		$cached    = wp_cache_get( $cache_key, self::CACHE_GROUP );

		if ( $cached ) {
			return $cached;
		}

		$amazon_api_origin = trim(
			str_replace( [ 'http://', 'https://' ], '', $api_details['amazon_api_origin'] ),
			'/'
		);

		$data    = [];
		$regions = $asin_codes['regions'];
		unset( $asin_codes['regions'] );

		// Chunk up requests into max amount of ASINS allowed.
		$chunks = array_chunk( $asin_codes, self::MAX_ASINS );

		/**
		 * Fallback Mechanism.
		 * 
		 * If the user's current geolocation is not present under territories, 
		 * the API will fallback to US territory.
		 */
		if ( ! empty( $regions ) && is_array( $regions ) ) {
			if ( ! in_array( $geolocation, $regions, true ) ) {
				$geolocation = self::FALLBACK_DEFAULT;
			}
		}

		foreach ( $chunks as $chunk ) {
			$response = self::request(
				"https://{$amazon_api_origin}/affiliate-api/v1.0/{$geolocation}/",
				[
					'amzTrackId' => $api_details['amazon_track_id'],
					'amzAcc'     => $api_details['amazon_account'],
					'asins'      => implode( ',', $chunk ),
				]
			);

			if ( ! empty( $response['code'] ) && 200 === $response['code'] ) {
				foreach ( $response['amazon'] as $item ) {
					if ( isset( $item['id'] ) ) {
						$data[ $item['id'] ] = $item;
					}
				}
			}
		}
		
		wp_cache_set( $cache_key, $data, self::CACHE_GROUP, self::CACHE_EXPIRY ); // phpcs:ignore
		
		return $data;
	}

	/**
	 * Make a request.
	 *
	 * @param string $url - The url.
	 * @param array  $query_args - Query args.
	 * @return null|array
	 */
	public static function request( string $url, $query_args = [] ) {
		if ( empty( $url ) ) {
			return null;
		}

		$request = wp_safe_remote_get(
			add_query_arg(
				$query_args,
				$url
			)
		);

		if ( is_wp_error( $request ) ) {
			return null;
		}

		$body = wp_remote_retrieve_body( $request );

		$response = json_decode( $body, JSON_OBJECT_AS_ARRAY );

		return $response;
	}

	/**
	 * Get asin codes from transformed products.
	 *
	 * @param array $products - Transformed products.
	 * @return array
	 */
	public static function get_asin_codes_from_products( array $products ) : array {
		$codes   = [];
		$regions = [];
		if ( ! empty( $products ) ) {
			foreach ( $products as $product ) {
				if ( ! empty( $product['all_region_info'] ) ) {
					foreach ( $product['all_region_info'] as $region => $info ) {
						$regions[] = $region;
					}

					if ( isset( $product['all_region_info']->US->purchase_options->vendor_codes ) ) {
						foreach ( $product['all_region_info']->US->purchase_options->vendor_codes as $vendor ) {
							if ( self::is_asin_code( $vendor ) ) {
								$codes[] = $vendor->code;
							}
						}
					}
				}

				if ( isset( $product['geo_info']->purchase_options->vendor_codes ) ) {
					foreach ( $product['geo_info']->purchase_options->vendor_codes as $vendor ) {
						if ( self::is_asin_code( $vendor ) ) {
							$codes[] = $vendor->code;
						}
					}
				}
			}
		}

		if ( ! empty( $codes ) ) { 
			$codes = self::get_comma_seperated_asin( $codes );
		}

		if ( ! empty( $regions ) ) {
			$codes['regions'] = $regions;
		}

		return array_unique( $codes );
	}

	/**
	 * Get comma seperated ASINS mergerd
	 *
	 * @param array $codes - The ASIN codes.
	 * @return array of merged codes
	 */
	public static function get_comma_seperated_asin( $codes ) {
		$codes            = array_unique( $codes );
		$codes_with_comma = [];     
		foreach ( $codes as $code ) {
			$codes_with_comma[] = explode( ',', $code );
		}

		$codes = call_user_func_array( 'array_merge', $codes_with_comma );
		return $codes;
	}

	/**
	 * Is vendor an asin?
	 *
	 * @param object $vendor - The vendor.
	 * @return boolean
	 */
	public static function is_asin_code( $vendor ) {
		$vendor_name = strtolower( $vendor->vendor );

		if ( strpos( 'amazon', $vendor_name ) !== false ) {
			return true;
		}

		if ( strpos( 'asin', $vendor_name ) !== false ) {
			return true;
		}

		return false;
	}
}
