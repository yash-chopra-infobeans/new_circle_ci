<?php

namespace IDG\Asset_Manager;

if ( ! class_exists( '\\Rest_Video_Controller' ) ) {
	return;
}

/**
 * Custom endpoint for Video uploads.
 */
class Rest_Video_Controller extends \WP_REST_Attachments_Controller {
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
		parent::__construct( 'attachment' );

		if ( defined( 'JW_PLAYER_API_KEY' ) && defined( 'JW_PLAYER_API_SECRET' ) ) {
			$this->jwplatform_api = new Jw_Player( JW_PLAYER_API_KEY, JW_PLAYER_API_SECRET );
		}

		add_filter(
			'oauth_route_filter',
			function( $excluded_routes ) {
				$excluded_routes[] = 'idg/v1/video/webhooks/video-ready';
				return $excluded_routes;
			}
		);
	}

	/**
	 * Register custom endpoint(s).
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			'idg/v1',
			'/video',
			[
				[
					'methods'             => 'POST',
					'callback'            => [ $this, 'upload_video_callback' ],
					'permission_callback' => [ $this, 'create_item_permissions_check' ],
				],
				'schema' => [ $this, 'get_public_item_schema' ],
			],
		);
		register_rest_route(
			'idg/v1',
			'/video/status/(?P<id>\d+)',
			[
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'get_video_status' ],
					'permission_callback' => [ $this, 'get_items_permissions_check' ],
				],
				'schema' => [ $this, 'get_public_item_schema' ],
			],
		);
		register_rest_route(
			'idg/v1',
			'/video/webhooks/video-ready',
			[
				[
					'methods'             => 'POST',
					'callback'            => [ $this, 'media_available' ],
					'permission_callback' => [ $this, 'webhook_permissions_check' ],
				],
				'schema' => [ $this, 'get_public_item_schema' ],
			],
		);
	}

	/**
	 * Check that the request is coming from JW Player.
	 *
	 * @return bool
	 */
	public function webhook_permissions_check() {
		$webhooks      = new \IDG\Asset_Manager\Webhooks();
		$authenticated = false;

		// Is this JWPlayer Webhook?
		if ( $webhooks->verify() ) {
			$authenticated = true;
		}

		return $authenticated;
	}

	/**
	 * When JW Player media is available upadte the meta status to ready.
	 *
	 * @return void
	 */
	public function media_available() {
		$webhook_payload = json_decode( file_get_contents( 'php://input' ) );

		if ( ! isset( $webhook_payload->media_id ) ) {
			return;
		}

		$attachment = $this->get_attachment_by_media_id( $webhook_payload->media_id );

		if ( ! $attachment ) {
			return;
		}

		$meta = update_post_meta( $attachment->ID, Meta_Fields::META_CUSTOM_STATUS, 'ready' );

		idg_notify_error(
			'MediaAvailableUpdateMeta',
			'media_available - Update attachment meta.',
			[ 'meta' => $meta ],
			'info'
		);
	}

	/**
	 * Get attachment where JW Player mediaid meta value is equal to the one passed.
	 *
	 * @param string $mediaid id of the media id in JW Player that we save as meta, so we have a reference.
	 * @return WP_Post[]|int[]|false
	 */
	public function get_attachment_by_media_id( string $mediaid ) {
		$args = [
			'meta_query'     => [
				[
					'key'   => Meta_Fields::META_JW_PLAYER_MEDIA_ID,
					'value' => $mediaid,
				],
			],
			'post_type'      => 'attachment',
			'posts_per_page' => 1,
			'cache_results'  => false,
		];

		$attachment = get_posts( $args );

		idg_notify_error(
			'GetAttachmentByMediaId',
			'get_attachment_by_media_id - Attachment WP_Post and WP_Query.',
			[
				'attachment' => $attachment,
				'args'       => $args,
			],
			'info'
		);

		if ( ! $attachment ) {
			return false;
		}

		return $attachment[0];
	}

	/**
	 * Uploads video to JW Player and inserts a record into the DB with media id returned from JW Player as
	 * attachment meta.
	 *
	 * @param \WP_REST_Request $request REST request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function upload_video_callback( \WP_REST_Request $request ) {
		$params = $request->get_params();

		$files       = $request->get_file_params();
		$target_file = $files['file']['tmp_name'];

		// phpcs:disable
		$title                    = isset( $params['title'] ) ? $params['title'] : pathinfo( $files['file']['name'], PATHINFO_FILENAME );
		$caption                  = isset( $params['caption'] ) ? $params['caption'] : '';
		$tags                     = isset( $params['asset_tag'] ) ? $params['asset_tag'] : [];
		$credit                   = isset( $params['meta']['credit'] ) ? $params['meta']['credit'] : '';
		$image_rights_notes       = isset( $params['meta']['image_rights_notes'] ) ? $params['meta']['image_rights_notes'] : '';
		$active                   = isset( $params['meta']['active'] ) ? $params['meta']['active'] : true;
		$_wp_attachment_image_alt = isset( $params['meta']['_wp_attachment_image_alt'] ) ? $params['meta']['_wp_attachment_image_alt'] : '';
		// phpcs:enable

		if ( ! function_exists( 'wp_read_video_metadata' ) ) {
			include ABSPATH . 'wp-admin/includes/media.php';
		}

		$metadata = wp_read_video_metadata( $target_file );

		$attachment = $this->prepare_item_for_database( $request );

		$attachment->post_title     = wp_strip_all_tags( $title );
		$attachment->post_excerpt   = wp_filter_post_kses( $caption );
		$attachment->post_mime_type = $metadata['mime_type'];
		$attachment->meta_input     = [
			Meta_Fields::META_IMAGE_RIGHTS_NOTES => $image_rights_notes,
			Meta_Fields::META_CREDIT             => $credit,
			Meta_Fields::META_ACTIVE             => $active,
			Meta_Fields::META_METADATA           => $metadata,
			Meta_Fields::META_ALT                => $_wp_attachment_image_alt,
			Meta_Fields::META_CUSTOM_STATUS      => 'processing',
		];

		// Insert attachment into DB.
		$attachment_id = wp_insert_attachment( wp_slash( (array) $attachment ), $file, 0, true, false );

		if ( is_wp_error( $attachment_id ) ) {
			if ( 'db_update_error' === $attachment_id->get_error_code() ) {
				$attachment_id->add_data( [ 'status' => 500 ] );
			} else {
				$attachment_id->add_data( [ 'status' => 400 ] );
			}

			return $attachment_id;
		}

		$jw_player_tags = [];

		foreach ( $tags as $tag ) {
			$term = get_term( (int) $tag );

			if ( ! $term ) {
				continue;
			}

			$jw_player_tags[] = $term->name;
		}

		$jw_player_params = [];

		if ( ! empty( sanitize_text_field( $title ) ) ) {
			$jw_player_params['title'] = wp_strip_all_tags( $title );
		}

		if ( ! empty( sanitize_text_field( $caption ) ) ) {
			$jw_player_params['description'] = wp_strip_all_tags( $caption );
		}

		if ( ! empty( $jw_player_tags ) ) {
			$jw_player_params['tags'] = implode(
				', ',
				$jw_player_tags
			);
		}

		$create_response = wp_json_encode( $this->jwplatform_api->call( '/videos/create', $jw_player_params ) );
		$decoded         = json_decode( trim( $create_response ), true );

		if ( 'ok' !== $decoded['status'] ) {
			wp_delete_post( $attachment_id, true );
			return new \WP_Error(
				'error_creating_video_object_jw_player',
				$decoded['message'],
				[ 'status' => 400 ]
			);
		}

		$upload_response = $this->jwplatform_api->upload( $target_file, $decoded['link'] );

		if ( 'ok' !== $upload_response['status'] ) {
			wp_delete_post( $attachment_id, true );
			return new \WP_Error(
				'error_creating_video_object_jw_player',
				isset( $upload_response['message'] ) ? $upload_response['message'] : 'Unable to upload video to JW Player.',
				[ 'status' => 400 ]
			);
		}

		// Create relationships between terms and attachment.
		// @TODO Change to handle_terms, see comment for video_handle_terms to why we do this.
		$this->video_handle_terms( $attachment_id, $request );

		// Add JW Player media id as post meta.
		update_post_meta( $attachment_id, Meta_Fields::META_JW_PLAYER_MEDIA_ID, $upload_response['media']['key'] );

		if ( ! defined( 'JW_PLAYER_API_WEBHOOK_SECRET' ) ) {
			update_post_meta( $attachment_id, Meta_Fields::META_CUSTOM_STATUS, 'ready' );
		}

		$attachment = get_post( $attachment_id );

		$response = $this->prepare_item_for_response( $attachment, $request );
		$response = rest_ensure_response( $response );

		return $response;
	}

	/**
	 * Endpoint for checking whether or not an uploaded attachment is ready for example when uploading to JW Player
	 * the video can be uploaded but not ready to use as JW Player is processing it this endpoint can be used to check
	 * if JW Player is processing it.
	 *
	 * @param \WP_REST_Request $request The Request.
	 * @return bool|\WP_Error|\WP_Rest_Response
	 */
	public function get_video_status( \WP_REST_Request $request ) {
		$attachment = get_post( $request['id'] );

		if ( ! $attachment ) {
			return new \WP_Error(
				'error_retreiving_video_status',
				'Attachment not found.',
				[ 'status' => 404 ]
			);
		}

		$media_id = get_post_meta( $request['id'], Meta_Fields::META_JW_PLAYER_MEDIA_ID, true );

		if ( ! $media_id ) {
			return new \WP_Error(
				'error_retreiving_video_status',
				'Attachment does not have a JW Player media id attachmed to it.',
				[ 'status' => 400 ]
			);
		}

		$media_custom_status = get_post_meta( $request['id'], Meta_Fields::META_CUSTOM_STATUS, true );

		idg_notify_error(
			'GetVideoStatus',
			'Get video status media_id.',
			[
				'media_id'            => $media_id,
				'attachment'          => $attachment,
				'media_custom_status' => $media_custom_status,
			],
			'info'
		);

		if ( 'processing' !== $media_custom_status ) {
			$response = $this->prepare_item_for_response( $attachment, $request );
			$response = rest_ensure_response( $response );

			return $response;
		}

		$response = rest_ensure_response( false );

		return $response;
	}


	/**
	 * Updates the post's terms from a REST request - casting values to int as it would otherwise create a term
	 * using the id as the term name ie passing "61" would create a term called "61".
	 *
	 * @param int              $post_id The post ID to update the terms form.
	 * @param \WP_REST_Request $request The request object with post and terms data.
	 * @return null|WP_Error WP_Error on an error assigning any of the terms, otherwise null.
	 */
	protected function video_handle_terms( int $post_id, \WP_REST_Request $request ) {
		// phpcs:ignore
		$taxonomies = wp_list_filter( get_object_taxonomies( $this->post_type, 'objects' ), [ 'show_in_rest' => true ] );

		foreach ( $taxonomies as $taxonomy ) {
			$base = ! empty( $taxonomy->rest_base ) ? $taxonomy->rest_base : $taxonomy->name;

			if ( ! isset( $request[ $base ] ) ) {
				continue;
			}

			$raw_terms = (array) $request[ $base ];
			$terms     = array_map( 'absint', $raw_terms );
			if ( empty( array_filter( $terms ) ) ) {
				$terms = array_map( 'sanitize_text_field', $raw_terms );
			}

			$result = wp_set_object_terms( $post_id, $terms, $taxonomy->name );

			if ( is_wp_error( $result ) ) {
				return $result;
			}
		}
	}
}
