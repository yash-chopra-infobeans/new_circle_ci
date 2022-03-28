<?php

namespace IDG\Configuration\Plugins\Publishing_Flow;

use IDG\Publishing_Flow\Data\Images as PubFlow_Images;

/**
 * Handles images for publishing flow hooks.
 */
class Images {
	public function __construct() {
		add_filter( 'idg_publishing_flow_author_payload', [ $this, 'author_profile_photo_payload'], 10, 2 );
		add_action( 'idg_publishing_flow_after_author_create', [ $this, 'handle_profile_photo' ], 10, 2 );
	}

	/**
	 * Attaches the author profile photo to the author payload.
	 *
	 * @param array   $author The current author payload data.
	 * @param integer $author_id The author id.
	 * @return array
	 */
	public function author_profile_photo_payload( array $author, int $author_id ) : array {
		$author_image_meta = get_the_author_meta( 'profile-photo', $author_id );
		$author['image']   = isset( $author_image_meta['media_id'] ) ? PubFlow_Images::instance()->format( $author_image_meta['media_id'] ) : [];

		return $author;
	}

	/**
	 * Handle the profile photo when it comes in to the delivery site.
	 *
	 * @param integer $author_id The author id.
	 * @param array   $author The current author payload data.
	 * @return void
	 */
	public function handle_profile_photo( int $author_id, array $author ) : void {
		if( ! isset( $author['image'] ) ) {
			return;
		}

		$image_id  = PubFlow_Images::instance()->store_from_url( $author['image'] );
		$image_url = wp_get_attachment_url( $image_id );

		update_user_meta( $author_id, 'profile-photo', [
			'media_id' => $image_id,
			'full' => $image_url,
		] );
	}
}
