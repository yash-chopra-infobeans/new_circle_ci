<?php

namespace IDG\Configuration\Plugins\Publishing_Flow;

use IDG\Publishing_Flow\Deploy\Article as Deploy_Article;
use IDG\Publishing_Flow\Data\Images;
use IDG\Publishing_Flow\API\Endpoints\Post;

class Featured_Video {
	public function __construct() {
		add_filter( Deploy_Article::FILTER_PREPARE_PAYLOAD, [ $this, 'attach_to_payload' ], 10, 1 );
		add_action( Post::HOOK_AFTER_DEPLOY_ARTICLE, [ $this, 'create_featured_video' ], 10, 2 );
	}

	/**
	 * Attach featured video information to the deployment
	 * payload to be used on delivery site insertion.
	 *
	 * @param array $payload The payload to be sent to the Delivery Site.
	 * @return array
	 */
	public function attach_to_payload( array $payload ) {
		$meta = $payload['meta'];

		if ( ! isset( $meta['featured_video_id'] ) ) {
			return $payload;
		}

		$featured_video_id = (array) $meta['featured_video_id'];

		$preserve = ['post_mime_type'];

		$payload['featured_video'] = Images::instance()->format( $featured_video_id[0], $preserve );

		return $payload;
	}

	/**
	 * Create the featured video record from data provided by
	 * the Content Hub to a Delivery Site.
	 *
	 * @param int|string $post_id ID of the new post that will be attached to.
	 * @param array $body The current request body.
	 * @return void
	 */
	public function create_featured_video( $post_id, $body = [] ) : void {
		if ( ! isset( $body['featured_video'] ) ) {
			delete_post_meta( $post_id, 'featured_video_id' );
			return;
		}

		$content_hub_id  = $body['featured_video']['ID'];
		$attachment_meta = $body['featured_video']['meta'];

		unset( $body['featured_video']['ID'] );
		unset( $body['featured_video']['meta'] );
		unset( $body['featured_video']['guid'] );

		$attachment = Images::instance()->get_image_by_content_hub_id( $content_hub_id );

		if ( $attachment ) {
			$body['featured_video']['ID'] = $attachment;
		}

		$attachment_id = wp_insert_attachment( $body['featured_video'] );

		update_post_meta( $attachment_id, 'content_hub_id', $content_hub_id );
		update_post_meta( $post_id, 'featured_video_id', $attachment_id );

		foreach( $attachment_meta as $key => $value ) {
			update_post_meta( $attachment_id, $key, $value[0] );
		}
	}
}
