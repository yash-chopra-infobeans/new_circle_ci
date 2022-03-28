<?php
/**
 * Registers custom rest route for fetching AMP products JSON.
 * 
 * @package idg-base-theme.
 */

use IDG\Products\Article;
use IDG\Products\Link_Wrapping;
use IDG\Territories\Geolocation;
use IDG\Products\Subtag;

if ( class_exists( 'IDG_Amp_Products_Rest' ) ) {
	return new IDG_Amp_Products_Rest();
}

/**
 * 
 * Class to register custom rest route for fetching AMP products JSON.
 */
class IDG_Amp_Products_Rest {
	/**
	 * Subtag separator.
	 */
	const SEPERATOR = '-';

	/**
	 * The results returned back to the rest route.
	 *
	 * @var array Array of sorted results.
	 */
	protected $results = [];

	/**
	 * Adds custom rest route
	 */
	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register' ] );
	}

	/**
	 * Registers custom rest route
	 */
	public function register() {
		register_rest_route(
			'idg/v1',
			'amp_products/(?P<post_id>\d+)/(?P<product_id>\d+)/(?P<context>[a-z]+)',
			[
				'methods'             => [ 'GET' ],
				'callback'            => [ $this, 'get_amp_products_json' ],
				'permission_callback' => function () {
					return true;
				},
			]
		);
	}

	/**
	 * Get products information for AMP pages.
	 *
	 * @param WP_REST_Request $request The incoming rest request.
	 */
	public function get_amp_products_json( WP_REST_Request $request = null ) {
		if ( empty( $request ) ) {
			return [];
		}

		$price_details          = [];
		$product_id             = absint( $request->get_param( 'product_id' ) );
		$post_id                = absint( $request->get_param( 'post_id' ) );
		$context                = $request->get_param( 'context' );
		$amp_product            = Article::get_products( $post_id );
		$amp_product            = $amp_product[ $product_id ] ?? [];
		$price_details['items'] = [];
		$geo_location           = Geolocation::get_country_code() ?: 'US';
		
		if ( ! empty( $amp_product ) ) {
			$prices = idg_get_product_pricing( $amp_product );
			if ( ! empty( $prices ) && is_array( $prices ) ) {
				$will_have_hidden_records = count( $prices ) > 4;
				foreach ( $prices as $key => $price ) {
					if ( 'pc' === $context || 'pw' === $context ) {
						if ( ( $key > 2 ) || ( false === $price['inStock'] ) ) { // Out of stock items will always be at bottom.
							break;
						}
					}

					/**
					 * Subtag generation for AMP links.
					 */
					if ( ! empty( $price['link'] ) ) {
						$site_id     = Subtag::site_id();
						$medium_code = Subtag::medium_code();
						$position    = Subtag::position();
						$subtag_ids  = [ $site_id, $medium_code, $post_id, $position, $product_id ];
						$subtag      = join( self::SEPERATOR, $subtag_ids );
					}

					$pricing_array = [
						'string'         => $price['price'] ?? '',
						'url'            => $price['link'] ? Link_Wrapping::process_amp_link( $price['link'], $geo_location, $subtag, $post_id ) : '',
						'link_data_attr' => [],
						'price'          => $price['price'] ?? '',
					];

					if ( ! empty( $pricing_array['string'] ) && ! empty( $price['vendor'] ) ) {
						$pricing_array['string'] .= sprintf(
							/* translators: %1$s: Vendor name */
							' at %1$s',
							$price['vendor']
						);
					}

					if ( ( 0 !== $key ) ) {
						$pricing_array['separator'] = 'yes';
					}

					if ( 'pco' === $context ) {
						if ( $will_have_hidden_records && $key >= 4 ) {
							$pricing_array['show_hidden'] = true;
						}
						
						if ( ! empty( $price['vendor'] ) ) {
							$vendor_logo             = idg_products_get_vendor_logo( $price['vendor'] );
							$pricing_array['vendor'] = $price['vendor'];
							$delivery_text           = idg_products_get_delivery_text( $price );
							if ( ! empty( $vendor_logo ) ) {
								$pricing_array['vendor_logo'] = esc_url( $vendor_logo );
							}

							$pricing_array['delivery_text'] = esc_html( $delivery_text );
						}
					}

					$price_details['items'][ $key ]                = ! empty( $pricing_array ) ? $pricing_array : [];
					$price_details['items'][0]['best_price_label'] = esc_html( $amp_product['labels']->best_prices_field_label ?? '' ) . ':';
				}
			}

			$price_details['items'][ $key ]['footer_text'] = true;
			if ( $will_have_hidden_records ) {
				$price_details['items'][ $key ]['will_have_hidden_records'] = true;
			}

			if ( ! empty( $amp_product['geo_info']->pricing ?: '' ) ) {
				$price_value = function_exists( 'get_price_with_currency' ) ? get_price_with_currency( (array) $amp_product['geo_info']->pricing ) : [];
				$rrp_pricing = $amp_product['geo_info']->pricing->price_options ?: $price_value ?? '';

				if ( $rrp_pricing && ! empty( $rrp_pricing ) ) {
					$price_details['items'][0]['rrp_label'] = esc_html( $amp_product['labels']->rrp_field_label ?? '' ) . ':';
					$price_details['items'][0]['rrp_price'] = $rrp_pricing;
				}
			}
		}

		return $price_details;
	}
}
