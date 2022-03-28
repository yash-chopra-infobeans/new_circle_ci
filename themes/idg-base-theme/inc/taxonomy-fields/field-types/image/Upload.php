<?php

namespace IDG\Base_Theme\Taxonomy;

class Tax_Image_Upload {
	public function __construct( $data, $meta_name, $term_id ) {
		$this->data      = $data;
		$this->meta_name = $meta_name;
		$this->term_id   = $term_id;
	}

	public function upload() {
		$supported_types = [ 'image/gif', 'image/jpeg', 'image/png', 'image/svg+xml' ];
		$file_type       = $this->data['type'];

		if ( in_array( $file_type, $supported_types, true ) ) {
			$upload_status = wp_handle_upload( $this->data, [ 'test_form' => false ] );

			if ( isset( $upload_status['file'] ) ) {
				require_once ABSPATH . 'wp-admin' . '/includes/image.php';

				$image_id = wp_insert_attachment(
					[
						'post_mime_type' => $upload_status['type'],
						'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $upload_status['file'] ) ),
						'post_content'   => '',
						'post_status'    => 'publish',
						'meta_input'     => [
							'media_type' => 'taxonomy-image',
						],
					],
					$upload_status['file']
				);

				$attachment_data = wp_generate_attachment_metadata( $image_id, $upload_status['file'] );
				wp_update_attachment_metadata( $image_id, $attachment_data );

				$existing_image = get_term_meta( $this->term_id, $this->meta_name, true );
				if ( ! empty( $existing_image ) && is_numeric( $existing_image ) ) {
					wp_delete_attachment( $existing_image );
				}

				update_term_meta( $this->term_id, $this->meta_name, $image_id );
				delete_term_meta( $this->term_id, $this->meta_name . '_feedback' );
			} else {
				$upload_feedback = 'There has been a problem uploading your file.';
			}
		} else {
			$upload_feedback = 'Image Files only: JPEG/JPG, GIF, PNG, SVG';
		}

		if ( isset( $upload_feedback ) ) {
			update_term_meta( $this->term_id, $this->meta_name . '_feedback', $upload_feedback );
		}
	}
}
