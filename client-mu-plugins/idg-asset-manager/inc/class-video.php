<?php

namespace IDG\Asset_Manager;

/**
 * Add filters and actions to handling upadting JW Player video data when
 * a JW Player attachment is updated within the DB.
 *
 * It also handled deleting the video from JW Player when the attachment is
 * deleted from WordPress.
 */
class Video {
	/**
	 * JW Player api.
	 *
	 * @var class
	 */
	protected $jwplatform_api;

	/**
	 * Setup.
	 */
	public function __construct() {
		if ( defined( 'JW_PLAYER_API_KEY' ) && defined( 'JW_PLAYER_API_SECRET' ) ) {
			$this->jwplatform_api = new Jw_Player( JW_PLAYER_API_KEY, JW_PLAYER_API_SECRET );
		}

		add_action( 'rest_insert_attachment', [ $this, 'rest_update_attachment' ], 10, 3 );
		add_action( 'delete_attachment', [ $this, 'delete_video' ], 10, 3 );
		add_action( 'attachment_updated', [ $this, 'update_video' ], 10, 3 );
	}

	/**
	 * Update JW Player video data when attachment is updated via REST api.
	 *
	 * @param \WP_Post         $attachment Inserted or updated attachment object.
	 * @param \WP_REST_Request $request The request sent to the API.
	 * @param boolean          $creating True when creating an attachment, false when updating.
	 * @return void
	 */
	public function rest_update_attachment( \WP_Post $attachment, \WP_REST_Request $request, bool $creating ) {
		/**
		 * We don't call it when creating as for video's we use our custom endpoint as were not uploading the file
		 * to the local file system. Example when were uploading the video to JW Player which is hadnled by the
		 * custom endpoint.
		 */
		if ( $creating ) {
			return;
		}

		$this->update_video( $attachment->ID );
	}

	/**
	 * Update JW Player video data.
	 *
	 * @param integer $post_id Post ID.
	 * @return void
	 */
	public function update_video( int $post_id ) : void {
		if ( ! $post_id || ! $this->jwplatform_api ) {
			return;
		}

		$post = get_post( $post_id );

		$is_jw_player_video = get_post_meta( $post_id, Meta_Fields::META_JW_PLAYER_MEDIA_ID, true );

		if ( ! $is_jw_player_video ) {
			return;
		}

		$tags        = get_the_terms( $post_id, 'asset_tag' );
		$tags_string = $tags ? join( ', ', wp_list_pluck( $tags, 'name' ) ) : '';

		$params = [
			'video_key'   => $is_jw_player_video,
			'title'       => sanitize_text_field( $post->post_title ),
			'description' => sanitize_text_field( $post->post_excerpt ),
			'tags'        => $tags_string,
		];

		$this->jwplatform_api->call( '/videos/update', $params );
	}

	/**
	 * Deletes video from JW Player
	 *
	 * @param integer  $post_id Attachment ID.
	 * @param \WP_Post $post Post object.
	 * @return void
	 */
	public function delete_video( int $post_id, \WP_Post $post ) : void {
		if ( ! $post || ! $this->jwplatform_api ) {
			return;
		}

		$is_jw_player_video = get_post_meta( $post_id, Meta_Fields::META_JW_PLAYER_MEDIA_ID, true );

		if ( ! $is_jw_player_video ) {
			return;
		}

		$params = [
			'video_key' => $is_jw_player_video,
		];

		$this->jwplatform_api->call( '/videos/delete', $params );
	}
}
