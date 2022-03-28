<?php
if ( ! function_exists( 'idg_featured_image' ) ) {
	/**
	 * Return the image url of a posts featured image at the desired size.
	 *
	 * @since 1.0.0
	 * @param int|bool $post_id The desired post's ID.
	 * @param string   $size    The desired image size.
	 * @return bool|string
	 */
	function idg_featured_image( $post_id = false, $size = 'full' ) {
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}

		$img_url = get_the_post_thumbnail_url( $post_id, $size );

		$post_thumbnail_id = get_post_thumbnail_id( $post_id );

		if ( ! $img_url ) {
			/**
			 * Sets the feature image to a default if non set
			 *
			 * @since 1.0.0
			 *
			 * @param string $image_path String path to default image.
			 */
			return false;
			// TODO: Need to add default image.
		}

		return $img_url;
	}
}

if ( ! function_exists( 'idg_can_display_floating_video' ) ) {
	/**
	 * Returns whether or not the current page/post can display the floating video player.
	 *
	 * @param integer $post_ID The desired post's ID.
	 * @return boolean
	 */
	function idg_can_display_floating_video( int $post_ID ) : bool {
		$supress_video = get_post_meta( $post_ID, 'supress_floating_video', true );

		/**
		 * Video should not be displayed if user has explicity set the supress video option.
		 */
		if ( $supress_video ) {
			return false;
		}

		$podcast = get_the_terms( $post_ID, 'podcast_series' );

		/**
		 * Video should not be displayed if content is part of a podcast series.
		 */
		if ( $podcast ) {
			return false;
		}

		$sponsorship = idg_base_theme_get_sponsorship( $post_ID );

		/**
		 * Video should not be displayed if content is sponsored
		 */
		if ( $sponsorship ) {
			return false;
		}

		/**
		 * If the featured video is set the floating player cannot be displayed as the featured video
		 * will float bottom right on scroll.
		 */
		$featured_video = get_post_meta( $post_ID, 'featured_video_id', true );

		if ( $featured_video ) {
			return false;
		}

		return true;
	}
}
