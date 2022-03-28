<?php
/**
 * API for Product post type.
 *
 * @package IDG Products
 */

namespace IDG\Products;

use IDG\Publishing_Flow\Sites;
use IDG\Base_Theme\Utils;
use IDG\Territories\Geolocation;

/**
 * Retrieve a single product.
 */
class Product {
	const CACHE_GROUP = 'idg_product';

	/**
	 * Add actions.
	 */
	public function __construct() {
		wp_cache_add_non_persistent_groups( [ self::CACHE_GROUP ] );
	}

	/**
	 * Get product ids associated to an article.
	 *
	 * @param int $product_id - The product id to retrieve.
	 * @param int $article_id - The post id.
	 * @return array
	 */
	public static function get( int $product_id, int $article_id ) : array {
		$cached_product = wp_cache_get( $product_id, self::CACHE_GROUP );

		if ( $cached_product ) {
			return $cached_product;
		}

		$origin_url = Sites::get_origin_url();

		if ( empty( $origin_url ) || empty( $product_id ) ) {
			return [];
		}

		/**
		 * In an ideal world we wouldn't make a remote request on the content hub but
		 * since the data is formatted so differently when making rest requests vs
		 * WP_Query, for time, it was quicker and tidier to do it this way.
		 */
		$protocol    = is_ssl() ? 'https' : 'http';
		$request_url = "{$protocol}://{$origin_url}/wp-json/wp/v2/product/{$product_id}?_embed=1&article_id=" . $article_id;
		$response    = wp_safe_remote_get( $request_url );

		if ( is_wp_error( $response ) ) {
			return [];
		}

		$product = json_decode( wp_remote_retrieve_body( $response ) ) ?? [];

		if ( empty( $product ) ) {
			return [];
		}

		$transformed_product = Transform::product( $product );

		wp_cache_set( $product_id, $transformed_product, self::CACHE_GROUP );

		return $transformed_product;
	}
}
