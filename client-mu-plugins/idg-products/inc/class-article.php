<?php
/**
 * File to handle the products present in an article.
 * 
 * @package idg-products.
 */

namespace IDG\Products;

use IDG\Publishing_Flow\Sites;
use IDG\Base_Theme\Utils;
use IDG\Territories\Geolocation;

/**
 * Retrieve products related to an article.
 */
class Article {
	/**
	 * The cache group for products associated to an article.
	 */
	const CACHE_GROUP = 'idg_article_products';

	/**
	 * Regex to grab id from product links.
	 */
	const PRODUCT_LINKS_REGEX = '/data-product="(.*?)"/';

	/**
	 * An array determining how blocks are associated to products
	 * and where to retrieve their id.
	 */
	const BLOCK_MAPPING = [
		'idg-base-theme/review-block'           => [
			'primaryProductId',
			'comparisonProductId',
		],
		'idg-base-theme/price-comparison-block' => [
			'productId',
		],
		'idg-base-theme/product-chart-block'    => [
			'productData' => [
				'productId',
				'activeReview',
			],
		],
		'idg-base-theme/product-widget-block'   => [
			'productId',
		],
		'idg-base-theme/product-chart-item'     => [
			'productId',
		],
	];

	/**
	 * Add actions.
	 */
	public function __construct() {
		wp_cache_add_non_persistent_groups( [ self::CACHE_GROUP ] );
	}

	/**
	 * Get product ids associated to an article.
	 *
	 * @param int $post_id - The article id.
	 * @return array
	 */
	public static function get_product_ids( int $post_id ) : array {
		$post = get_post( $post_id );

		if ( ! $post || ! isset( $post->post_content ) ) {
			return [];
		}

		preg_match_all( self::PRODUCT_LINKS_REGEX, $post->post_content, $product_link_matches );

		$product_ids = $product_link_matches ? $product_link_matches[1] : [];

		$block_names = array_keys( self::BLOCK_MAPPING );

		foreach ( parse_blocks( $post->post_content ) as $block ) {
			if ( ! in_array( $block['blockName'], $block_names, true ) ) {
				continue;
			}

			foreach ( self::BLOCK_MAPPING[ $block['blockName'] ] as $key => $attribute ) {
				if ( ! is_array( $attribute ) ) {
					array_push( $product_ids, $block['attrs'][ $attribute ] );

					continue;
				}

				foreach ( $attribute as $attr ) {
					$ids = wp_list_pluck( $block['attrs'][ $key ], $attr );

					if ( empty( $ids ) ) {
						continue;
					}

					array_push(
						$product_ids,
						...$ids
					);
				}
			}
		}

		return array_map( 'intval', array_unique( $product_ids ) );
	}

	/**
	 * Get all products associated with an article with object caching.
	 *
	 * @param int $post_id - The article id.
	 * @return array
	 */
	public static function get_products( int $post_id ) : array {
		$cached_products = wp_cache_get( $post_id, self::CACHE_GROUP );

		if ( $cached_products ) {
			return $cached_products;
		}

		$product_ids = self::get_product_ids( $post_id );
		$origin_url  = Sites::get_origin_url();

		if ( empty( $product_ids ) || empty( $origin_url ) ) {
			return [];
		}

		/**
		 * In an ideal world we wouldn't make a remote request on the content hub but
		 * since the data is formatted so differently when making rest requests vs
		 * WP_Query, for time, it was quicker and tidier to do it this way.
		 */
		$include_ids = implode( ',', $product_ids );
		$protocol    = is_ssl() ? 'https' : 'http';
		$request_url = "{$protocol}://{$origin_url}/wp-json/wp/v2/product?include={$include_ids}&article_id=" . $post_id . '&per_page=100&_embed=1';
		$response    = wp_safe_remote_get( $request_url );

		if ( is_wp_error( $response ) ) {
			return [];
		}

		$products = json_decode( wp_remote_retrieve_body( $response ) ) ?? [];

		if ( empty( $products ) || ! is_array( $products ) ) {
			return [];
		}

		$transformed_products = self::transform( $products );

		wp_cache_set( $post_id, $transformed_products, self::CACHE_GROUP );

		return $transformed_products;
	}

	/**
	 * Transform product data to something easier.
	 *
	 * @param array $products - The products to transform.
	 * @return array
	 */
	public static function transform( array $products ) {
		$data = [];

		foreach ( $products as $product ) {
			$data[ $product->id ] = Transform::product( $product );
		}

		return $data;
	}

	/**
	 * Creates amp_products REST endpoint URL
	 *
	 * @return string
	 */
	public static function get_amp_products_endpoint() {
		$request_url = '';
		$request_url = home_url( '/' ) . 'wp-json/idg/v1/amp_products/';

		return $request_url;
	}
}
