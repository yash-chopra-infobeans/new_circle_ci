<?php

if ( class_exists( 'IDG_Product_Pricing_Rest' ) ) {
	return new IDG_Product_Pricing_Rest();
}

use IDG\Territories\Geolocation;
use IDG\Publishing_Flow\Sites;

/**
 * Class to register custom rest route for 'product_pricing'
 */
class IDG_Product_Pricing_Rest {

	/**
	 * Class constant. Endpoint cache group.
	 */
	const CACHE_GROUP = 'idg_product_pricing_rest_endpoint';

	/**
	 * Request object.
	 *
	 * @var WP_REST_Request
	 */
	protected static $request;

	/**
	 * Adds custom rest route
	 */
	public function __construct() {
		wp_cache_add_non_persistent_groups( [ self::CACHE_GROUP ] );
		add_action( 'rest_api_init', [ $this, 'register' ] );
	}

	/**
	 * Registers custom rest route
	 */
	public function register() {
		register_rest_route(
			'idg/v1',
			'product_pricing/(?P<id>\d+)',
			[
				'methods'             => [ 'GET' ],
				'callback'            => [ $this, 'get' ],
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			]
		);
	}

	/**
	 * Return product's pricing details from different vendors.
	 *
	 * @param WP_REST_Request $request The incoming rest request.
	 */
	public function get( WP_REST_Request $request = null ) {

		if ( empty( $request ) ) {
			return [];
		}

		self::$request = $request;
		$product_id    = absint( $request->get_param( 'id' ) );

		if ( empty( $product_id ) ) {
			return [];
		}

		return self::get_product_pricing_details( $product_id );
	}

	/**
	 * Retrieve product's pricing and other details from different vendors and also retrieve direct links.
	 * If it's delivery site, direct links will be fetched from Content hub.
	 *
	 * @param int $product_id Product's ID.
	 *
	 * @return array Product details from different vendors.
	 */
	public static function get_product_pricing_details( int $product_id ) {

		$pricing_information = [];
		$pricing_information = wp_cache_get( "product_pricing_$product_id", self::CACHE_GROUP );

		if ( ! empty( $pricing_information ) && is_array( $pricing_information ) ) {
			return $pricing_information;
		}

		if ( empty( $product_id ) ) {
			return $pricing_information;
		}

		idg_notify_error(
			'Product Chart Rest',
			'Is Editor?',
			[
				'is_editor' => self::is_editor(),
			],
			'info'
		);

		if ( self::is_editor() ) {
			$user_geo_location = 'US';
		} else {
			$user_geo_location = idg_products_get_user_geo_location();
		}

		idg_notify_error(
			'Product Chart Rest',
			'User Geolocation',
			[
				'geo' => $user_geo_location,
			],
			'info'
		);

		$product_meta = idg_products_maybe_get_product_meta_from_origin( $product_id, '', true );

		if ( empty( $product_meta ) || ! is_array( $product_meta ) ) {
			return $pricing_information;
		}

		$product_direct_links = idg_products_get_direct_links( $product_meta, $user_geo_location );

		idg_notify_error(
			'Product Chart Rest',
			'Direct Links',
			[
				'direct_links' => $product_direct_links,
			],
			'info'
		);

		if ( ! empty( $product_direct_links ) && is_array( $product_direct_links ) ) {
			foreach ( $product_direct_links as $product_direct_link ) {
				$streamlined_data = self::streamline_product_details( $product_direct_link );

				if ( ! empty( $streamlined_data ) && is_array( $streamlined_data ) ) {
					$pricing_information[] = $streamlined_data;
				}
			}
		}

		/**
		 * Right now we only care about Amazon, implement other vendors' data processing once we have product vendor DB ready.
		 */
		$amazon_product_details = idg_products_fetch_amazon_product_details( $product_meta, $user_geo_location );

		if ( empty( $amazon_product_details ) || ! is_array( $amazon_product_details ) ) {
			$pricing_information = self::sort_prices( $pricing_information );

			wp_cache_set( "product_pricing_$product_id", $pricing_information, self::CACHE_GROUP );

			return $pricing_information;
		}

		$amazon_streamlined_data = [];

		foreach ( $amazon_product_details as $product_detail ) {
			$streamlined_data = self::streamline_product_details( $product_detail, 'amazon' );

			if ( ! empty( $streamlined_data ) && is_array( $streamlined_data ) ) {
				$amazon_streamlined_data[] = $streamlined_data;
			}
		}

		$amazon_streamlined_data = self::sort_prices( $amazon_streamlined_data )[0]; // Lowest price, in stock record.
		$pricing_information[]   = $amazon_streamlined_data;

		$pricing_information = self::sort_prices( $pricing_information );

		wp_cache_set( "product_pricing_$product_id", $pricing_information, self::CACHE_GROUP );

		return $pricing_information;
	}

	/**
	 * Used for making all products' details recieved from different sources into a same format.
	 *
	 * @param array|object $product_details Can be an Array of Object depending on the source.
	 * @param string       $context         Used to identify the source of the product details so attributes can be mapped
	 *                                      accordingly.
	 *
	 * @return array Array containing attributes mapped in target pattern.
	 */
	protected static function streamline_product_details( $product_details, $context = '' ) {

		if ( empty( $product_details ) ) {
			return [];
		}

		if ( 'amazon' === $context ) {
			$has_offers    = ! empty( $product_details['offers'] ) && 0 < count( $product_details['offers'] );
			$prime         = false;
			$free_shipping = false;
			$price         = $product_details['price'] ?? '';
			$in_stock      = false;

			if ( $has_offers ) {
				$top_offer     = $product_details['offers'][0];
				$prime         = $top_offer['prime'];
				$free_shipping = $top_offer['freeShipping']; // phpcs:ignore WordPress.NamingConventions.ValidVariableName -- Part of API response, nothing we can do.
				$price         = $top_offer['price'];
				$in_stock      = $top_offer['inStock']; // phpcs:ignore WordPress.NamingConventions.ValidVariableName -- Part of API response, nothing we can do.
			}

			return [
				'id'           => $product_details['id'],
				'currency'     => $product_details['priceObject']['CurrencyCode'] ?? '', // phpcs:ignore WordPress.NamingConventions.ValidVariableName -- Part of API response, nothing we can do.
				'price'        => $price,
				'title'        => $product_details['title'] ?? '',
				'vendor'       => 'Amazon',
				'link'         => $product_details['link'] ?? '',
				'prime'        => $prime,
				'freeShipping' => $free_shipping,
				'inStock'      => $in_stock,
			];
		}

		$currency_symbol = $product_details['currency_symbol'] ?? '';
		$price           = ! empty( $product_details['price'] ) ?
			$currency_symbol . $product_details['price'] :
			'';


		idg_notify_error(
			'ProductChartRest',
			'Currency info',
			[
				'product_details' => $product_details,
				'currency_symbol' => $currency_symbol,
				'price'           => $price,
			],
			'info'
		);

		return [
			'id'       => 0,
			'currency' => $product_details['currency'] ?? '',
			'price'    => $price,
			'title'    => '',
			'vendor'   => $product_details['vendor'] ?? '',
			'link'     => $product_details['url'] ?? '',
			'inStock'  => true, // We are not including vendor data from IDG's database, once we have that as well, this may not be always true.
		];
	}

	/**
	 * Sort product pricing records in ascending order based on price. 'Out of stock' products will be moved to last.
	 *
	 * @param Array $pricing_information Array of pricing records.
	 *
	 * @return Array Sorted array of records.
	 */
	protected static function sort_prices( $pricing_information ) : array {

		if ( empty( $pricing_information ) || ! is_array( $pricing_information ) ) {
			return [];
		}

		usort(
			$pricing_information,
			function( $record_one, $record_two ) {
				if ( false === $record_one['inStock'] ) {
					if ( false === $record_two['inStock'] ) {
						return 0;
					}
					return 1;
				}
				if ( false === $record_two['inStock'] ) {
					return -1;
				}

				$price_one = filter_var(
					$record_one['price'],
					FILTER_SANITIZE_NUMBER_FLOAT,
					FILTER_FLAG_ALLOW_FRACTION
				);
				$price_two = filter_var(
					$record_two['price'],
					FILTER_SANITIZE_NUMBER_FLOAT,
					FILTER_FLAG_ALLOW_FRACTION
				);

				if ( $price_one === $price_two ) {
					return strtolower( $record_one['vendor'] ) > strtolower( $record_two['vendor'] );
				}

				return $price_one > $price_two;
			}
		);

		return $pricing_information;
	}

	/**
	 * Whether the request was made from Editor or not.
	 *
	 * @return bool
	 */
	protected static function is_editor() : bool {

		if ( empty( self::$request ) || empty( self::$request->get_param( 'source' ) ) ) {
			return false;
		} elseif ( 'block-editor' === self::$request->get_param( 'source' ) ) {
			return true;
		}

		return false;
	}
}
