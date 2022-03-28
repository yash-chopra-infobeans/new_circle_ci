<?php

namespace IDG\Migration\Images;

use WP_CLI;
use IDG\Publishing_Flow\Deploy\Article as Deploy_Article;
use IDG\Publishing_Flow\Sites;

/**
 * Base Class with methods that can be used to migrate content.
 */
class Base {
	/**
	 * Create/update attachment with metadata etc.
	 *
	 * @param string  $url image url.
	 * @param integer $existing_id existing attachment id of image if we have one.
	 * @return int
	 */
	public function handle_image( string $url, int $existing_id = 0 ) {
		try {
			$args = idg_download_image( $url );

			if ( 0 < $existing_id ) {
				// Get existing attachment so we can prevent the modified date changing.
				$existing_attachment = get_post( $existing_id );

				$args = array_merge(
					[
						'ID'                => $existing_id,
						'post_date'         => $existing_attachment->post_date_gmt,
						'post_date_gmt'     => $existing_attachment->post_date_gmt,
						'post_modified'     => $existing_attachment->post_modified,
						'post_modified_gmt' => $existing_attachment->post_modified_gmt,
					],
					$args
				);
			}

			$attachment_id = wp_insert_attachment( $args );
			update_post_meta( $attachment_id, 'legacy_image_src', $url );
			$file_path = $args['file_path'];
			// Update attachment file path.
			update_attached_file( $attachment_id, $file_path );
			// Update attachment guid.
			idg_update_post_guid( $attachment_id, $args['guid'] );
			// Generate & Update attachmet metadata.
			$metadata = wp_generate_attachment_metadata( $attachment_id, $file_path );
			wp_update_attachment_metadata( $attachment_id, $metadata );

			return $attachment_id;
		} catch ( \ErrorException $e ) {
			WP_CLI::error( $e->getMessage() );
		};
	}

	/**
	 * Migrate post featured image.
	 *
	 * @param \WP_Post $post post.
	 * @return void
	 */
	public function post_featured_image( $post ) {
		$featured_image_id = get_post_thumbnail_id( $post );

		// If no featured image return early.
		if ( ! $featured_image_id ) {
			WP_CLI::line( 'Skipping featured image, no featured image set.' );
			return;
		}

		$attachment = get_post( $featured_image_id );

		if ( ! idg_is_valid_image_url( $attachment->guid ) || ! idg_can_image_be_migrated( $attachment->guid ) ) {
			WP_CLI::line( "Skipping featured image: {$attachment->guid}." );
			return;
		}

		// Download image and update attachment record.
		$this->handle_image( $attachment->guid, $attachment->ID );
		WP_CLI::line( "Migrated featured image: {$attachment->guid}." );
	}

	/**
	 * Publish post to publication(s).
	 *
	 * @param int $post post id.
	 * @return void
	 */
	public function deploy_post( int $post_id ) {
		$publication = Sites::get_post_publication( $post_id );

		// If no publication assigned skip post.
		if ( ! $publication ) {
			WP_CLI::line( "Skipping post({$post_id}), no publication(s) assigned." );
		}

		$post = get_post( $post_id );

		$deploy = new Deploy_Article( $post, intval( $publication->term_id ) );
		$deploy->add_headers( [ 'X-IDG-PUBFLOW-SKIP-MISSING-IMAGES' => true ] );
		$deploy->add_headers( [ 'X-IDG-PUBFLOW-SKIP-MODIFIED-DATE' => true ] );
		$deploy->create();

		if ( $deploy->failed() ) {
			$error_messages = $deploy->get_data()->errors ?: [ 'No error message provided.' ];
			WP_CLI::error_multi_line(
				array_merge(
					[
						"Post {$post->ID} could not be deployed to {$publication->term_id} publication.",
						'-------',
					],
					json_decode( wp_json_encode( $error_messages ), true ),
				)
			);
		} else {
			WP_CLI::success( "Post {$post->ID} deployed to {$publication->term_id} publication." );
		}
	}
}
