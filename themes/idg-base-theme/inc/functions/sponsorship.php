<?php
/**
 * Contain a function which have information about sponsorship taxonomy
 *
 * @package idg-base-theme
 */

if ( ! function_exists( 'idg_base_theme_get_sponsorship' ) ) {
	/**
	 * Gets sponsorship information using the post_ID.
	 * Returns false if no sponsorship.
	 *
	 * @param int $post_ID ID of post.
	 */
	function idg_base_theme_get_sponsorship( $post_ID ) {
		$cached = wpcom_vip_cache_get( sprintf( 'get_sponsorship_%s', $post_ID ), 'idg_base_theme_non_pers' );

		if ( $cached ) {
			return $cached;
		}

		$direct_sponsorship = get_the_terms( $post_ID, 'sponsorships' );
		$blog               = get_the_terms( $post_ID, 'blogs' );
		$podcast_series     = get_the_terms( $post_ID, 'podcast_series' );
		$story_type         = get_the_terms( $post_ID, 'story_types' );
		$tooltip_content    = cf_get_value( 'global_settings', 'sponsored_content', 'sponsorship_descriptions' );

		// Check there is a sponsorship.
		if ( ! $direct_sponsorship ) {
			return;
		} else {
			$sponsorship = $direct_sponsorship[0]->term_id;
		}

		// Check if blog.
		if ( $blog ) {
			$sponsorship_type    = 'sponsored-blog';
			$sponsorship_tooltip = $tooltip_content['sponsored_post_description'];

			// Check if podcast.
		} elseif ( $podcast_series ) {
			$sponsorship_type    = 'sponsored-podcast';
			$sponsorship_tooltip = $tooltip_content['sponsored_podcast_description'];

			// Check if dealpost.
		} elseif ( is_object( $story_type[0] ) && 'dealpost' === $story_type[0]->slug ) {
			$sponsorship_type    = 'dealpost';
			$sponsorship_tooltip = $tooltip_content['deal_post_description'];

			// Check if brandpost.
		} elseif ( is_object( $story_type[0] ) && 'brandpost' === $story_type[0]->slug ) {
			$sponsorship_type    = 'brandpost';
			$sponsorship_tooltip = $tooltip_content['brand_post_description'];

			// Fallback to sponsored-post.
		} else {
			$sponsorship_type    = 'sponsored-post';
			$sponsorship_tooltip = $tooltip_content['sponsored_post_description'];
		}

		$sponsorship_id = $sponsorship;
		$series_name    = get_term_meta( $sponsorship_id, 'series_name', true );
		$intro_text     = get_term_meta( $sponsorship_id, 'introductory_text', true );
		$brand_color    = get_term_meta( $sponsorship_id, 'brand_color', true );
		$brand_url      = get_term_meta( $sponsorship_id, 'brand_url', true );
		$brand_logo_id  = get_term_meta( $sponsorship_id, 'logo', true );
		$disable_ads    = get_term_meta( $sponsorship_id, 'disable_ads', true );
		$brand_logo_arr = wp_get_attachment_image_src( $brand_logo_id, '150s-r1:1' );
		$brand_logo_url = $brand_logo_arr[0];

		$data = [
			'id'             => $sponsorship_id,
			'type'           => $sponsorship_type,
			'tooltip'        => $sponsorship_tooltip,
			'series_name'    => $series_name,
			'intro_text'     => $intro_text,
			'brand_color'    => $brand_color,
			'brand_url'      => $brand_url,
			'brand_logo_id'  => $brand_logo_id,
			'brand_logo_arr' => $brand_logo_arr,
			'brand_logo_url' => $brand_logo_url,
			'disable_ads'    => $disable_ads,
		];

		wpcom_vip_cache_set( sprintf( 'get_sponsorship_%s', $post_ID ), $data, 'idg_base_theme_non_pers' );

		return $data;
	}
}
