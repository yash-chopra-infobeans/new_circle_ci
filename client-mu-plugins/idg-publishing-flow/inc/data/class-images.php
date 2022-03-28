<?php

namespace IDG\Publishing_Flow\Data;

// @TODO: We need a process for handling legacy images
// and not downloading them as these will be handled
// post-launch and likely in a different manner prior
// to article deployment.

/**
 * Image data handling class.
 */
class Images extends Data {
	/**
	 * Get an instance of the class.
	 */
	public static function instance() {
		return new self();
	}

	/**
	 * Get an image by it's id.
	 *
	 * @param int|string $image_id The ID of the image.
	 * @return string|bool
	 */
	public function get_image_by_id( $image_id ) {
		$image = get_posts(
			[
				'ID'               => $image_id,
				'post_type'        => 'attachment',
				'suppress_filters' => false,
			]
		);

		if ( $image ) {
			return intval( $image[0]->ID );
		}

		return false;
	}

	/**
	 * Get an image by it's content hub id.
	 *
	 * @param int|string $ch_id The ID of the image on the Content Hub.
	 * @return string|bool
	 */
	public function get_image_by_content_hub_id( $ch_id ) {
		$image = get_posts(
			[
				'post_type'        => 'attachment',
				'meta_key'         => 'content_hub_id',
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Need to match by reference ID.
				'meta_value'       => $ch_id,
				'suppress_filters' => false,
			]
		);

		if ( $image ) {
			return intval( $image[0]->ID );
		}

		return false;
	}

	/**
	 * Formats the image data object to be compatible with
	 * what is expected for article deployment.
	 *
	 * @param int|string $image_id The image to retrieve and format.
	 * @param array      $preserve The fields to preserve in the resulting array.
	 * @return array
	 */
	public function format( $image_id, $preserve = [] ) : array {
		$attachment_entry = get_post( $image_id, ARRAY_A );

		if ( ! $attachment_entry || 'attachment' !== $attachment_entry['post_type'] ) {
			return [];
		}

		$preserve = array_merge(
			[
				'ID',
				'post_date',
				'post_content',
				'post_title',
				'post_excerpt',
				'guid',
			],
			$preserve
		);

		$attachment_entry = $this->preserve_keys( $attachment_entry, $preserve );

		$image     = wp_get_attachment_image_src( $image_id, 'original' );
		$image_url = is_array( $image ) ? $image[0] : $attachment_entry['guid'];

		$meta  = get_post_meta( $image_id, '' );
		$strip = [
			'_wp_attached_file',
			'_wp_attachment_metadata',
		];

		$attachment_entry['guid'] = strtok( $image_url, '?' ); // Remove all query parameters.
		$attachment_entry['meta'] = $this->strip_keys( $meta, $strip );

		return $attachment_entry;
	}

	/**
	 * Store an image from a given url, adding the required
	 * data along with storing the image bitdata.
	 *
	 * @throws \Error Throw error when response code is not 200.
	 * @param array $data The image information to use in the post record.
	 * @return int The id of the newly inserted image.
	 */
	public function store_from_url( array $data ) : int {
		idg_notify_error(
			'DeliverySite',
			'Attempt storage of image.',
			[
				'data' => $data,
			]
		);

		$url = $data['guid'];

		$existing_image = $this->get_image_by_content_hub_id( $data['ID'] );

		$post_info = [
			'post_title'   => isset( $data['post_title'] ) ? $data['post_title'] : '',
			'post_content' => isset( $data['post_content'] ) ? $data['post_content'] : '',
			'post_excerpt' => isset( $data['post_excerpt'] ) ? $data['post_excerpt'] : '',
		];

		if ( $existing_image ) {
			idg_set_error_report_meta( [ 'existing' => $existing_image ] );
			$post_info['ID'] = $existing_image;
		}

		if ( ! class_exists( 'WP_Http' ) ) {
			include_once ABSPATH . WPINC . '/class-http.php';
		}

		$url = strtok( $url, '?' );  // Remove all query parameters.

		$http     = new \WP_Http;
		$response = $http->request( $url );

		$headers = array_change_key_case( getallheaders(), CASE_UPPER );
		$callee  = end( explode( '\\', get_called_class() ) );

		if (
			( is_wp_error( $response ) || 200 !== $response['response']['code'] )
			&& ! isset( $headers['X-IDG-PUBFLOW-SKIP-MISSING-IMAGES'] )
		) {
			idg_notify_error(
				'DeliverySite',
				'Could not request image.',
				[
					'image_request' => [
						'url'      => $url,
						'response' => $response,
						'data'     => $data,
					],
				]
			);
			throw new \ErrorException( "Could not request image. ($callee)" );
		}

		$upload = wp_upload_bits( basename( $url ), null, $response['body'] );

		idg_set_error_report_meta(
			[
				'image_upload' => [
					'url'      => $url,
					'response' => $response,
					'upload'   => $upload,
				],
			]
		);

		if ( ! empty( $upload['error'] ) && ! isset( $headers['X-IDG-PUBFLOW-SKIP-MISSING-IMAGES'] ) ) {
			throw new \ErrorException( "Could not upload image. ($callee)" );
		}

		$file_path        = $upload['file'];
		$file_name        = basename( $file_path );
		$file_type        = wp_check_filetype( $file_name, null );
		$attachment_title = sanitize_file_name( pathinfo( $file_name, PATHINFO_FILENAME ) );
		$wp_upload_dir    = wp_upload_dir();

		$post_info = array_merge(
			[
				'guid'           => $wp_upload_dir['url'] . '/' . $file_name,
				'post_mime_type' => $file_type['type'],
				'post_title'     => $attachment_title,
				'post_status'    => 'inherit',
			],
			$post_info
		);

		$image_id = wp_insert_attachment( $post_info, $file_path );

		require_once ABSPATH . 'wp-admin/includes/image.php';

		// Define attachment metadata.
		$attach_data = wp_generate_attachment_metadata( $image_id, $file_path );
		// Assign metadata to attachment.
		wp_update_attachment_metadata( $image_id, $attach_data );

		update_post_meta( $image_id, 'content_hub_id', $data['ID'] );

		if ( isset( $data['meta'] ) && \is_array( $data['meta'] ) ) {
			$this->update_meta( $image_id, $data['meta'] );
		}

		return $image_id;
	}

	/**
	 * Update all the meta data for the image.
	 *
	 * @param string|int $image_id The image id to add the meta.
	 * @param array      $data The meta data that should be attached.
	 * @return void
	 */
	public function update_meta( $image_id, array $data = [] ) : void {
		foreach ( $data as $key => $value ) {
			update_post_meta( $image_id, $key, $value[0] );
		}
	}
}
