<?php
/**
 * API for Product post type.
 *
 * @package IDG Products
 */

namespace IDG\Products\API;

/**
 * Custom endpoint to retrieve a single product. This is mainly
 * designed for use in the gutenberg editor. I would exercise caution
 * using it elsewhere.
 */
class Product {
	const REST_BASE     = 'idg/v1';
	const ENDPOINT      = '/product';
	const BLOCK_MAPPING = [
		'idg-base-theme/product-widget-block' => [
			'productId',
		],
		'idg-base-theme/product-chart-block'  => [
			'productId',
		],
		'idg-base-theme/product-chart-item'   => [
			'productId',
		],
	];

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
			self::REST_BASE,
			self::ENDPOINT . '(?:/(?P<id>\d+))?',
			[
				'methods'             => [ 'GET' ],
				'callback'            => [ $this, 'get' ],
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			]
		);
		register_rest_field(
			'product',
			'meta',
			[
				'get_callback' => [ $this, 'return_customize_review' ],
			]
		);
	}

	/**
	 * Fetches products.
	 *
	 * @param \WP_REST_Request $request The incoming rest request.
	 *
	 * @return array Response data.
	 */
	public function get( \WP_REST_Request $request = null ) {
		if ( empty( $request ) ) {
			return [];
		}
		$product_id = ( ! empty( $request->get_param( 'id' ) ) ) ? absint( $request->get_param( 'id' ) ) : 0;
		$article_id = ( ! empty( $request->get_param( 'article_id' ) ) ) ? absint( $request->get_param( 'article_id' ) ) : 0;

		if ( ! isset( $product_id ) ) {
			return [];
		}

		$product      = \IDG\Products\Product::get( $product_id, $article_id );
		$query_params = $request->get_query_params();

		if ( isset( $query_params['pricing'] ) ) {
			$product['vendor_pricing'] = idg_get_product_pricing( $product );
		}
		return $product;
	}

	/**
	 * Fetches products.
	 *
	 * @param  product          $object The post of product post type.
	 * @param \String          $field_name The field needs to be customize.
	 * @param \WP_REST_Request $request The incoming rest request.
	 * 
	 * This function will return oldest review if there is no active review exists.
	 */ 
	public function return_customize_review( $object, $field_name, $request ) {
		$query_params   = $request->get_query_params();
		$publication_id = 0;
		$article_id     = 0;
		if ( isset( $query_params['article_id'] ) ) {
			$article_id = $query_params['article_id'];
		}       
		if ( ! empty( $article_id ) ) {
			$publication_id = \IDG\Publishing_Flow\Sites::get_post_publication( $article_id )->term_id;
			$publication_id = ( ! empty( $publication_id ) ) ? $publication_id : 0;
		}
		if ( $object[ $field_name ] && $object[ $field_name ]['reviews'] ) {
			$reviews = json_decode( $object[ $field_name ]['reviews'], true );

			foreach ( $reviews as $key => $review ) {
				$time_string                      = get_the_date( 'M j, Y g:i a T', $key );
				$reviews[ $key ]['formattedTime'] = $time_string;
				$reviews[ $key ]['status']        = get_post_status( $key );
			}

			// step 1: find active key associated with article for that product.
			$active_key = $this->find_active_review( $object['id'], $article_id );
			if ( ! empty( $active_key ) && isset( $reviews[ $active_key ] ) ) {
				$reviews[ $active_key ]['manual'] = true;
			}
			// step 2: if active key is not present then check the review of same brand is present or not.
			if ( empty( $active_key ) ) {
				$hosts_arr = [];
				foreach ( $reviews as $key => $review ) {
					if ( 'publish' === $review['status'] ) {
						$hosts_arr[ $review['publication'][0]['term_taxonomy_id'] ][ $key ] = $review;
					}
				}
				foreach ( $hosts_arr as $host_id => $array ) {
					// step 3: if same brand review is present then find the oldest review from the same brand.
					if ( $publication_id === $host_id ) {
						$active_key = $this->sort_array_by_timestamp( $array ); 
						break;      
					}
				}
			}

			// step 4: if active key is present then find the permalink as per the host associated.
			if ( ! empty( $active_key ) ) { 
				
				if ( array_key_exists( $active_key, $reviews ) ) {
					$item = $reviews[ $active_key ];
					unset( $reviews[ $active_key ] );
					$reviews = [ $active_key => $item ] + $reviews;
				}   
				
				$protocol                            = is_ssl() ? 'https' : 'http';
				$slug                                = get_post_field( 'post_name', $active_key );
				$url                                 = get_permalink( $active_key );
				$term_id                             = $reviews[ $active_key ]['publication'][0]['term_id'];
				$origin_url                          = get_term_meta( $term_id, 'publication_host', true );
				$request_url                         = "{$protocol}://{$origin_url}/article/" . $active_key . '/' . $slug . '.html';
				$reviews[ $active_key ]['permalink'] = $request_url;
				$reviews[ $active_key ]['active']    = true;
				$object[ $field_name ]['reviews']    = wp_json_encode( $reviews, true ); 
			}
		}
		return $object[ $field_name ];
	}

	/**
	 * Sort array by timestamp and return first key.
	 *
	 * @param \Array $array The array needs to be sort.
	 */ 
	public function sort_array_by_timestamp( $array ) {
		// if no active review present then oldest review key would return.
		array_multisort( array_map( 'strtotime', array_column( $array, 'datetime' ) ), SORT_DESC, $array );           
		$active_key = array_key_first( $array );
		return $active_key;
	}

	/**
	 * Fetches products.
	 *
	 * @param \Integer $product_id The product_id.
	 * @param \Integer $post_id The post_id.
	 * This function will return oldest review if there is no active review exists.
	 */ 
	public function find_active_review( $product_id, $post_id ) {
		$post        = get_post( $post_id );
		$block_names = array_keys( self::BLOCK_MAPPING );
		if ( ! $post || ! isset( $post->post_content ) ) {
			return [];
		}
		$active_review = 0;
		foreach ( parse_blocks( $post->post_content ) as $block ) {
			if ( ! in_array( $block['blockName'], $block_names, true ) ) {
				continue;
			}
			if ( ! empty( $block['innerBlocks'] ) ) {
				foreach ( $block['innerBlocks'] as $innerblock ) {
					if ( ! in_array( $innerblock['blockName'], $block_names, true ) ) {
						continue;
					}
					$active_review = $this->check_block_content( $product_id, $innerblock );
					if ( ! empty( $active_review ) ) {
						break;
					}
				}
			}
			if ( ! empty( $active_review ) ) {
				break;
			}
			$active_review = $this->check_block_content( $product_id, $block );
			if ( ! empty( $active_review ) ) {
				break;
			}
		}
		return $active_review;
	}

	/**
	 * Check inside the blocks for activeReview.
	 *
	 * @param \Integer $product_id The product_id.
	 * @param \String  $block Block.
	 *  This function will return oldest review if there is no active review exists.
	 */ 
	public function check_block_content( $product_id, $block ) {
		if ( isset( $block['attrs']['productData'] ) ) {
			foreach ( $block['attrs']['productData'] as $block_product ) {
				$block_product_id = $block_product['productId'];
				if ( ! empty( $block_product_id ) && $product_id === $block_product_id ) {
					$active_review = (int) $block_product['activeReview'];  
					break;
				}
			}
		}
		if ( $block['blockName'] === 'idg-base-theme/product-widget-block' ) {
			$block_product_id = $block['attrs']['productId'];
			if ( ! empty( $block_product_id ) && $product_id === $block_product_id ) {
				$active_review = (int) $block['attrs']['activeReview'];  
			}
		}
		if ( $block['blockName'] === 'idg-base-theme/product-chart-item' ) {
			$block_product_id = $block['attrs']['productId'];
			if ( ! empty( $block_product_id ) && $product_id === $block_product_id ) {
				$active_review = (int) $block['attrs']['activeReview'];  
			}
		}
		return $active_review;
	}
}
