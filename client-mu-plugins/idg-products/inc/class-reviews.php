<?php

namespace IDG\Products;

/**
 * Link reviews within an article to a product.
 */
class Reviews {
	const META_REVIEWS = 'reviews';

	const REVIEW_BLOCK = 'idg-base-theme/review-block';

	/**
	 * Add actions.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'init' ] );
		add_action( 'save_post', [ $this, 'save_product_reviews' ], 10, 3 );
		add_action( 'delete_post', [ $this, 'delete_product_reviews' ], 10, 2 );
	}

	/**
	 * Register the product review meta.
	 *
	 * @return void
	 */
	public function init() {
		register_post_meta(
			'product',
			self::META_REVIEWS,
			[
				'type'         => 'string',
				'single'       => true,
				'show_in_rest' => true,
			]
		);

		register_post_meta(
			'post',
			self::META_REVIEWS,
			[
				'type'         => 'string',
				'single'       => true,
				'show_in_rest' => true,
			]
		);
	}

	/**
	 * Find the review block within the post content
	 *
	 * @param string $post_content - The post content.
	 * @return bool|array
	 */
	public static function find_review_block( $post_content ) {
		$blocks = parse_blocks( $post_content );

		$key = array_search( self::REVIEW_BLOCK, array_column( $blocks, 'blockName' ), true );

		if ( ! $key ) {
			return false;
		}

		return $blocks[ $key ];
	}

	/**
	 * If a post has a review block, ensure the relevant product is updated.
	 *
	 * @param int    $post_id - The post id.
	 * @param object $post - The post object.
	 * @return void
	 */
	public function save_product_reviews( $post_id, $post ) {
		if ( 'post' !== $post->post_type ) {
			return;
		}

		$review_block = self::find_review_block( $post->post_content );

		$previous_product_ids = json_decode( get_post_meta( $post_id, self::META_REVIEWS, true ), true ) ?? [];

		if ( empty( $previous_product_ids ) && ! $review_block ) {
			return;
		}

		$primary_id    = $review_block ? ( $review_block['attrs']['primaryProductId'] ?? null ) : null;
		$comparison_id = $review_block ? ( $review_block['attrs']['comparisonProductId'] ?? null ) : null;

		$product_ids_to_remove = array_filter(
			$previous_product_ids,
			function( $id ) use ( $primary_id, $comparison_id ) {
				if ( $id === $primary_id || $id === $comparison_id ) {
					return false;
				}

				return true;
			}
		);

		$this->remove_review_from_products( $post_id, $product_ids_to_remove );

		if ( ! $review_block ) {
			update_post_meta( $post_id, self::META_REVIEWS, wp_json_encode( [] ) );
			return;
		}

		$review = $this->create_review_data(
			$post_id,
			$primary_id,
			$comparison_id,
			$review_block['attrs']['editorsChoice'],
			$review_block['attrs']['rating']
		);

		$new_product_ids = [];

		if ( $primary_id ) {
			$new_product_ids[] = $primary_id;
		}

		if ( $comparison_id ) {
			$new_product_ids[] = $comparison_id;
		}

		$this->update_product_review( $primary_id, $post_id, $review );
		$this->update_product_review( $comparison_id, $post_id, $review );

		update_post_meta( $post_id, self::META_REVIEWS, wp_json_encode( $new_product_ids ) );
	}

	/**
	 * Ensure reviews are removed from a product when a post is deleted.
	 *
	 * @param int    $post_id - The post id.
	 * @param object $post - The post object.
	 * @return void
	 */
	public function delete_product_reviews( $post_id, $post ) {
		if ( 'post' !== $post->post_type ) {
			return;
		}

		$review_block = self::find_review_block( $post->post_content );

		if ( ! $review_block ) {
			return;
		}

		$product_ids           = [];
		$primary_product_id    = $review_block['attrs']['primaryProductId'] ?? null;
		$comparison_product_id = $review_block['attrs']['comparisonProductId'] ?? null;

		if ( $primary_product_id ) {
			$product_ids[] = $primary_product_id;
		}

		if ( $comparison_product_id ) {
			$product_ids[] = $comparison_product_id;
		}

		$this->remove_review_from_products( $post_id, $product_ids );
	}

	/**
	 * Remove reviews from products
	 *
	 * @param int   $post_id - The post id.
	 * @param array $product_ids - Product Ids.
	 * @return void
	 */
	public function remove_review_from_products( $post_id, $product_ids ) {
		foreach ( $product_ids as $product_id ) {
			$this->update_product_review( $product_id, $post_id, false );
		}
	}

	/**
	 * Create review data.
	 *
	 * @param int     $post_id - The post id.
	 * @param int     $primary_id - The id of the primary product.
	 * @param int     $comparison_id - The id of the comparison product.
	 * @param boolean $is_editors_choice - Whether it is deemed an editor's choice.
	 * @param float   $rating - The rating in .5 intervals from 0 - 5.
	 * @return array
	 */
	public function create_review_data(
		$post_id,
		$primary_id,
		$comparison_id,
		$is_editors_choice = false,
		$rating = 0
	) {
		$review = [
			'type'        => $comparison_id ? 'comparison' : 'primary',
			'timestamp'   => time(),
			'primary'     => $primary_id,
			'publication' => get_the_terms( $post_id, 'publication' ),
		];

		if ( $comparison_id ) {
			$review['comparison'] = $comparison_id;
		}

		if ( ! $comparison_id ) {
			$review['editors_choice'] = $is_editors_choice;
			$review['rating']         = $rating;
		}

		return $review;
	}

	/**
	 * Update a product's review meta.
	 *
	 * @param int     $product_id - The product id.
	 * @param int     $post_id - The related post id.
	 * @param boolean $review - The review data.
	 * @return void
	 */
	public function update_product_review( $product_id, $post_id, $review = false ) {
		$reviews = json_decode( get_post_meta( $product_id, self::META_REVIEWS, true ), true ) ?? [];

		if ( ! $review && isset( $reviews[ $post_id ] ) ) {
			unset( $reviews[ $post_id ] );
		}

		if ( $review ) {
			$reviews[ $post_id ] = $review;
		}

		update_post_meta( $product_id, self::META_REVIEWS, wp_json_encode( $reviews ) );
	}
}
