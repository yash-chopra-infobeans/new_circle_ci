<?php

namespace IDG\Products;

use IDG\Third_Party\Base_Data_Layer;
use IDG\Base_Theme\Templates;

use IDG\Products\Vendors\Amazon;

/**
 * Add product data to the datalayer.
 */
class Data_Layer {
	/**
	 * Add actions.
	 */
	public function __construct() {
		add_filter( Base_Data_Layer::FILTER, [ $this, 'add_product_data' ] );
	}

	/**
	 * Add product data to the data layer.
	 *
	 * @param array - $data - The data layer.
	 * @return array
	 */
	public function add_product_data( array $data ) : array {
		$post = get_post();

		if ( ! Templates\article() || ! isset( $post->ID ) ) {
			return array_merge(
				$data,
				[
					'prodIds'           => '',
					'prodCategories'    => '',
					'prodManufacturers' => '',
					'prodNames'         => '',
					'prodVendors'       => '',
				]
			);
		}

		$products = Article::get_products( $post->ID );

		return array_merge(
			$data,
			[
				'prodIds'           => implode( ',', array_keys( $products ) ),
				'prodCategories'    => self::get_product_terms( $products, 'category' ),
				'prodManufacturers' => self::get_product_terms( $products, 'manufacturer' ),
				'prodNames'         => self::get_product_names( $products ),
				'prodVendors'       => self::get_product_vendors( $products ),
			]
		);
	}

	/**
	 * Retrieve product names as comma seperated list.
	 *
	 * @param array $products - The products.
	 * @return string
	 */
	public static function get_product_names( array $products ) : string {
		$names = array_map(
			function( $product ) {
				return $product['name'];
			},
			$products
		);

		return implode( ',', $names );
	}

	/**
	 * Retrieve product terms as comma seperated list.
	 *
	 * @param array  $products - The products.
	 * @param string $taxonomy - The taxonomy.
	 * @param string $key      - The key of the term data to use.
	 * @return string
	 */
	public static function get_product_terms( array $products, string $taxonomy, string $key = 'name' ) : string {
		$names = [];

		foreach ( $products as $product ) {
			if ( isset( $product['terms'][ $taxonomy ] ) ) {
				foreach ( $product['terms'][ $taxonomy ] as $term ) {
					array_push( $names, $term->{$key} );
				}
			}
		}

		return implode( ',', array_unique( $names ) );
	}

	/**
	 * Get product vendors as comma seperated list.
	 *
	 * @param array $products - The products.
	 * @param bool  $include_direct_links - Should direct links be included as vendors.
	 * @return string
	 */
	public static function get_product_vendors( array $products, $include_direct_links = true ) : string {
		$vendors = [];

		foreach ( $products as $product ) {
			if ( isset( $product['geo_info']->purchase_options->vendor_codes ) ) {
				foreach ( $product['geo_info']->purchase_options->vendor_codes as $vendor_option ) {
					array_push( $vendors, $vendor_option->vendor );
				}
			}

			if ( isset( $product['direct_links'] ) && $include_direct_links ) {
				foreach ( $product['direct_links'] as $vendor_option ) {
					array_push( $vendors, $vendor_option->vendor );
				}
			}
		}

		return implode( ',', array_unique( $vendors ) );
	}
}
