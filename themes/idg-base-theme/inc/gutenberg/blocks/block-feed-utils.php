<?php

use function \IDG\Base_Theme\Utils\get_sponsored_posts;

if ( ! function_exists( 'idg_block_feed_args' ) ) {
	/**
	 * Generates standard set of query arguments for feed
	 * based blocks.
	 *
	 * @param array $attributes The block attributes.
	 * @return array
	 */
	function idg_block_feed_args( array $attributes ) : array {
		$amount         = isset( $attributes['amount'] ) ? $attributes['amount'] : 1;
		$offset         = isset( $attributes['offset'] ) ? $attributes['offset'] : 0;
		$post_type      = isset( $attributes['postType'] ) ? $attributes['postType'] : 'post';
		$filter         = @json_decode( $attributes['filters'] );
		$excluded_posts = [ get_the_ID() ];

		if ( $attributes['excludeSponsored'] ) {
			$excluded_posts = array_merge( $excluded_posts, get_sponsored_posts() );
		}

		if ( empty( $attributes ) || ! isset( $filter ) || ! $filter ) {

			$args = [
				'post__not_in'   => $excluded_posts,
				'posts_per_page' => $amount,
				'no_found_rows'  => ! isset( $attributes['ajaxLoad'] ),
				'offset'         => $offset,
				'post_status'    => [ 'publish', 'updated' ],
				'post_type'      => $post_type,
			];

		} else {

			$new_filter = [];
			$tax_item   = [];
			$tax_query  = [];

			foreach ( $filter as $key => $value ) {
				$new_filter[ $value->tax ][ $key ] = $value;
			}

			if ( empty( $new_filter ) ) {
				return [];
			}

			foreach ( $new_filter as $key => $value ) {
				$terms = array_map(
					function( $value ) {
						return $value->value;
					},
					$value
				);

				$tax_item = [
					'taxonomy' => $key, // phpcs:ignore
					'field'    => 'term_id',
					'terms'    => $terms,
				];

				array_push( $tax_query, $tax_item );
			}

			$amount = isset( $amount ) ? $amount : $attributes['amount'];

			$args = [
				'tax_query'      => $tax_query, // phpcs:ignore
				'post_status'    => [ 'publish', 'updated' ],
				'post__not_in'   => $excluded_posts,
				'posts_per_page' => $amount,
				'offset'         => $offset,
				'no_found_rows'  => ! isset( $attributes['ajaxLoad'] ),
				'post_type'      => $post_type,
			];
		}

		return $args;
	}
}
