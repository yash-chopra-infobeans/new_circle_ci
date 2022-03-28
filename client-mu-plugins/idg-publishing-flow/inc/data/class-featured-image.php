<?php

namespace IDG\Publishing_Flow\Data;

/**
 * Handling of featured image data.
 */
class Featured_Image extends Images {
	/**
	 * Get an instance of the class.
	 */
	public static function instance() {
		return new self();
	}

	/**
	 * Get the featured image from the post, and clean
	 * unrequired data for sending in a REST request.
	 *
	 * @param integer $post_id Reqested id for the image.
	 * @return array
	 */
	public function get( int $post_id ) {
		$image_id = get_post_thumbnail_id( $post_id );

		if ( 0 === $image_id ) {
			return [];
		}

		return self::format( $image_id );
	}
}
