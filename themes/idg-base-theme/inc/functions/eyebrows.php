<?php
/**
 * Contain a function which have information of eyebrow.
 *
 * @package idg-base-theme
 */

if ( ! function_exists( 'idg_base_theme_get_eyebrow' ) ) {
	/**
	 * Gets eyebrow text and style using the post_ID
	 *
	 * @param int|bool $post_ID The desired post's ID.
	 */
	function idg_base_theme_get_eyebrow( $post_ID ) {
		$cached = wpcom_vip_cache_get( sprintf( 'get_eyebrow_%s', $post_ID ), 'idg_base_theme_non_pers' );

		if ( $cached ) {
			return $cached;
		}

		$story_type_name = '';
		$story_type      = get_the_terms( $post_ID, 'story_types' );

		if ( $story_type ) {
			$story_type_name = $story_type[0]->name;
		}

		$sponsored_post = get_the_terms( $post_ID, 'sponsorships' );
		if ( $sponsored_post ) {
			$eyebrow_sponsorship = get_term_meta( $sponsored_post[0]->term_id, 'series_name', true );
		}

		$podcast_series = get_the_terms( $post_ID, 'podcast_series' );
		$blog_series    = get_the_terms( $post_ID, 'blogs' );
		$eyebrow        = '';
		$eyebrow_style  = 'default';
		$is_updated     = get_post_meta( $post_ID, '_idg_updated_flag', true );

		if ( 'DealPost' === $story_type_name ) {
			$eyebrow            = $story_type_name;
			$eyebrow_style      = 'default';
			$eyebrow_feed_title = $story_type_name;
			$eyebrow_feed_style = 'default';
		} elseif ( 'BrandPost' === $story_type_name ) {
			$eyebrow             = 'Sponsored';
			$eyebrow_style       = 'default';
			$eyebrow_sponsorship = get_term_meta( $sponsored_post[0]->term_id, 'display_name', true );
			$eyebrow_feed_title  = $story_type_name;
			$eyebrow_feed_style  = 'updated-lower';
		} elseif ( $podcast_series && $sponsored_post ) {
			$eyebrow            = 'Sponsored';
			$eyebrow_style      = 'default';
			$eyebrow_feed_title = 'Sponsor Podcast';
			$eyebrow_feed_style = 'updated-lower';
		} elseif ( $podcast_series ) {
			$eyebrow            = 'Podcast';
			$eyebrow_style      = 'default';
			$eyebrow_feed_title = $podcast_series[0]->name;
			$eyebrow_feed_style = 'default';
		} elseif ( $blog_series ) {
			$eyebrow_feed_title = $blog_series[0]->name;
			$eyebrow_feed_style = 'default';
		} elseif ( $sponsored_post ) {
			$eyebrow            = 'Sponsored';
			$eyebrow_style      = 'default';
			$eyebrow_feed_title = 'Sponsored';
			$eyebrow_feed_style = 'default';
		} elseif ( $is_updated ) {
			$eyebrow            = 'Updated';
			$eyebrow_style      = 'updated';
			$eyebrow_feed_title = 'Updated';
			$eyebrow_feed_style = 'updated';
		} elseif ( $story_type_name ) {
			$eyebrow            = $story_type_name;
			$eyebrow_style      = 'default';
			$eyebrow_feed_title = $story_type_name;
			$eyebrow_feed_style = 'default';
		}

		if ( $is_updated && ! $sponsored_post ) {
			// Main eyebrow should be Updated regardless unless post sponsored.
			$eyebrow       = 'Updated';
			$eyebrow_style = 'updated';
		}

		$data = [
			'eyebrow'             => $eyebrow,
			'eyebrow_style'       => $eyebrow_style,
			'eyebrow_sponsorship' => $eyebrow_sponsorship ?? '',
			'eyebrow_feed_title'  => $eyebrow_feed_title ?? '',
			'eyebrow_feed_style'  => $eyebrow_feed_style ?? '',
		];

		wpcom_vip_cache_set( sprintf( 'get_eyebrow_%s', $post_ID ), $data, 'idg_base_theme_non_pers' );

		return $data;
	}
}
