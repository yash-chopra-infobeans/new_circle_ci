<?php
/**
 * Base theme product helpers.
 *
 * These helper functions are mostly related to theme blocks. Anything else product related should
 * live in the products plugin.
 *
 * Future: maybe some of this could be moved to the product plugin actually?
 *
 * @package idg-base-theme
 * @see client-mu-plugins/idg-products
 */

use IDG\Products\Vendors\Amazon;
use IDG\Territories\Geolocation;
use function IDG\Base_Theme\Utils\is_amp;
use IDG\Products\Article;

if ( ! function_exists( 'idg_get_product_pricing' ) ) {
	/**
	 * Get a product's pricing.
	 *
	 * @param array $product - A product.
	 * @return array
	 */
	function idg_get_product_pricing( $product ) {
		$pricing = [];
		if ( ! empty( $product['direct_links'] ) && is_array( $product['direct_links'] ) ) {
			foreach ( $product['direct_links'] as $direct_link ) {
				$pricing[] = idg_transform_product_pricing( (array) $direct_link );
			}
		}
		
		$amazon_items = Amazon::fetch( [ $product ] );
		// Only need amazon items specific to this product.
		$product_asins = Amazon::get_asin_codes_from_products( [ $product ] );
		if ( empty( $amazon_items ) ) {
			return idg_sort_product_pricing( $pricing );
		}

		$amazon_pricing = array_filter(
			array_map(
				function( $asin ) use ( $amazon_items ) {
					return idg_transform_product_pricing( $amazon_items[ $asin ], 'amazon' );
				},
				$product_asins
			)
		);

		// Lowest price, in stock record.
		$lowest_price_in_stock = idg_sort_product_pricing( $amazon_pricing )[0] ?? false;

		if ( $lowest_price_in_stock ) {
			$pricing[] = $lowest_price_in_stock;
		}

		return idg_sort_product_pricing( $pricing );
	}
}

if ( ! function_exists( 'idg_sort_product_pricing' ) ) {
	/**
	 *  * Sort product pricing
	 *
	 * @param array $pricing - The pricing to sort.
	 * @return array
	 */
	function idg_sort_product_pricing( $pricing ) : array {
		if ( empty( $pricing ) || ! is_array( $pricing ) ) {
			return [];
		}

		usort(
			$pricing,
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

		return $pricing;
	}
}

if ( ! function_exists( 'idg_transform_product_pricing' ) ) {
	/**
	 * Unify and transform product pricing to use in theme blocks.
	 *
	 * @param array  $pricing - The pricing data.
	 * @param string $context - Context - can be multiple vendors in the future.
	 * @return array
	 */
	function idg_transform_product_pricing( $pricing, $context = '' ) {
		if ( empty( $pricing ) ) {
			return [];
		}

		if ( 'amazon' === $context ) {
			$has_offers    = ! empty( $pricing['offers'] ) && 0 < count( $pricing['offers'] );
			$prime         = false;
			$free_shipping = false;
			$price         = $pricing['price'] ?? '';
			$in_stock      = false;

			if ( $has_offers ) {
				$top_offer     = $pricing['offers'][0];
				$prime         = $top_offer['prime'];
				$free_shipping = $top_offer['freeShipping']; // phpcs:ignore WordPress.NamingConventions.ValidVariableName -- Part of API response, nothing we can do.
				$price         = $top_offer['price'];
				$in_stock      = $top_offer['inStock']; // phpcs:ignore WordPress.NamingConventions.ValidVariableName -- Part of API response, nothing we can do.
			}

			return [
				'id'           => $pricing['id'],
				'currency'     => $pricing['priceObject']['CurrencyCode'] ?? '', // phpcs:ignore WordPress.NamingConventions.ValidVariableName -- Part of API response, nothing we can do.
				'price'        => $price,
				'title'        => $pricing['title'] ?? '',
				'vendor'       => 'Amazon',
				'link'         => $pricing['link'] ?? '',
				'prime'        => $prime,
				'freeShipping' => $free_shipping,
				'inStock'      => $in_stock,
			];
		}

		$currency_symbol = '';

		if ( isset( $pricing['currency'] ) ) {
			$currency_symbols = json_decode(
				file_get_contents( IDG_TERRITORIES_DIR . '/inc/currency-symbols.json' ),
				true
			);

			$key = 'symbol_native';

			$currency_symbol = $currency_symbols[ $pricing['currency'] ][ $key ] ?? '';
		}

		return [
			'id'       => 0,
			'currency' => $pricing['currency'] ?? '',
			'price'    => $currency_symbol . ( $pricing['price'] ?? '' ),
			'title'    => '',
			'vendor'   => $pricing['vendor'] ?? '',
			'link'     => $pricing['url'] ?? '',
			'inStock'  => true, // We are not including vendor data from IDG's database, once we have that as well, this may not be always true.
		];
	}
}

if ( ! function_exists( 'idg_products_get_delivery_text' ) ) {
	/**
	 * Returns 'Delivery' column's text for a price record.
	 *
	 * @param Array $record Whether or not free shipping available.
	 *
	 * @return string Delivery column text.
	 */
	function idg_products_get_delivery_text( $record ) : string {

		if ( ! empty( $record['freeShipping'] ) && true === $record['freeShipping'] ) {
			return _x( 'Free', 'Delivery charges of a product', 'idg-base-theme' );
		} elseif ( ! empty( $record['inStock'] ) && false === $record['inStock'] ) {
			return __( 'Out of stock', 'idg-base-theme' );
		} else {
			return __( '--', 'idg-base-theme' );
		}

		return '';
	}
}

if ( ! function_exists( 'idg_products_get_vendor_logo' ) ) {
	/**
	 * Returns path to the vendor's logo. Depends on the vendor's slug.
	 *
	 * @param string $vendor Vendor's name.
	 *
	 * @return string Absolute path to the logo file.
	 */
	function idg_products_get_vendor_logo( string $vendor ) {
		$logo = '';
		if ( empty( $vendor ) ) {
			return $logo;
		}
		
		$vendor                = strtolower( str_replace( ' ', '-', $vendor ) ); // Some Vendor => some-vendor.
		$vendor_logo_file_name = "{$vendor}-logo";

		$svg_path = get_template_directory() . '/dist/static/img/' . $vendor_logo_file_name . '.svg';

		if ( file_exists( $svg_path ) ) {
			$logo = get_template_directory_uri() . '/dist/static/img/' . $vendor_logo_file_name . '.svg';
		}

		$png_path = get_template_directory() . '/dist/static/img/' . $vendor_logo_file_name . '.png';

		if ( file_exists( $png_path ) ) {
			$logo = get_template_directory_uri() . '/dist/static/img/' . $vendor_logo_file_name . '.png';
		}

		return $logo;
	}
}

if ( ! function_exists( 'get_price_with_currency' ) ) {
	/**
	 * Returns price with currency symbol.
	 *
	 * @param array $pricing Product's Pricing Array.
	 *
	 * @return string Price String.
	 */
	function get_price_with_currency( array $pricing ) {
		$price = '';
		if ( isset( $pricing['currency'] ) ) {
			$currency_symbols = json_decode(
				file_get_contents( IDG_TERRITORIES_DIR . '/inc/currency-symbols.json' ),
				true
			);

			$key             = 'symbol_native';
			$currency_symbol = $currency_symbols[ $pricing['currency'] ][ $key ] ?? '';
			$price           = $pricing['price'] ? $currency_symbol . $pricing['price'] : '';
		}

		return $price;
	}
}

if ( ! function_exists( 'get_price_with_currency' ) ) {
	/**
	 * Returns price with currency symbol.
	 *
	 * @param array $pricing Product's Pricing Array.
	 *
	 * @return string Price String.
	 */
	function get_price_with_currency( array $pricing ) {
		$price = '';
		if ( isset( $pricing['currency'] ) ) {
			$currency_symbols = json_decode(
				file_get_contents( IDG_TERRITORIES_DIR . '/inc/currency-symbols.json' ),
				true
			);

			$key             = 'symbol_native';
			$currency_symbol = $currency_symbols[ $pricing['currency'] ][ $key ] ?? '';
			$price           = $pricing['price'] ? $currency_symbol . $pricing['price'] : '';
		}

		return $price;
	}
}
