<?php

namespace IDG\Migration\Images;

use WP_CLI;
use IDG\Migration\Images\CLI;

// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
class DB extends Base {
	private $cli = null;

	public function __construct( CLI $cli ) {
		$this->cli     = $cli;
		$this->amount  = $cli->amount;
		$this->offset  = $cli->offset;
		$this->include = $cli->include;
	}

	public static function instance( CLI $cli ) {
		return new self( $cli );
	}

	/**
	 * Start the migration run by looping through the
	 * the amount and offset counters. Each entry will
	 * be processed and handled on it's own.
	 *
	 * @return void
	 */
	public function migrate() {
		$end_num = $this->offset + ( $this->amount - 1 );

		for ( $i = $this->offset; $i <= $end_num; $i++ ) {
			// phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.get_posts_get_posts -- We do not want to use cached results for this process and require current data at time of request.
			$posts = get_posts(
				[
					'numberposts' => 1,
					'offset'      => $i,
					'include'     => $this->include,
				]
			);

			$post = isset( $posts[0] ) ? $posts[0] : false;

			if ( ! $post ) {
				WP_CLI::line( WP_CLI::colorize( '%yNo Post matching get_posts query.%n' ) );
				continue;
			}

			$this->process_post( $post );
		}
	}

	/**
	 * Migrate image.
	 *
	 * @param string $url image url to migrate.
	 * @param string $image_id old one cms image id.
	 * @return int
	 */
	public function migrate_image( string $url, string $image_id ) {
		$replacements = [
			'-large.'  => '-orig.',
			'-medium.' => '-orig.',
			'-small'   => '-orig.',
		];

		$url = str_replace( array_keys( $replacements ), array_values( $replacements ), $url );

		// idg_get_post_by_guid will check if an attachment exists with the guid or meta value of $url.
		$attachment_id  = (int) idg_get_post_by_guid( $url );
		$attachment_url = wp_get_attachment_url( $attachment_id );

		/**
		 * If we do not have an attachment id then we need to migrate the image.
		 *
		 * If we have an attachment id then we need to check that the $attachment_url needs to be migrated. If
		 * we do need to migrate the image then go through the $this->handle_images function otherwise return the id
		 * we heve.
		 */
		if ( ! $attachment_id || ( $attachment_url && idg_can_image_be_migrated( $attachment_url ) ) ) {
			$attachment_id = $this->handle_images( $url, intval( $image_id ), $attachment_id );
		}

		return $attachment_id;
	}

	/**
	 * Create/update attachment with metadata etc.
	 *
	 * @param string  $url image url.
	 * @param integer $image_id old one cms image id.
	 * @param integer $existing_id existing attachment id of image if we have one.
	 * @return int
	 */
	public function handle_images( string $url, int $image_id, int $existing_id = 0 ) {
		try {
			$args = idg_download_image( $url );

			if ( 0 < $existing_id ) {
				$args = array_merge(
					[
						'ID' => $existing_id,
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
	 * Migrate any images found within post_content.
	 *
	 * @param \WP_Post $post post object.
	 * @return void
	 */
	public function post_content_images( $post ) {
		$document = new \DOMDocument();
		$html     = "<div>$post->post_content</div>";
		libxml_use_internal_errors( true );
		$document->loadHTML(
			mb_convert_encoding( $html, 'HTML-ENTITIES', 'UTF-8' ),
			LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
		);

		$images = $document->getElementsByTagName( 'img' ) ?: [];

		if ( 0 === count( $images ) ) {
			WP_CLI::line( WP_CLI::colorize( "%yPost {$post->ID} does not have content images.%n" ) );
			return;
		}

		WP_CLI::line( "Post {$post->ID} content importing..." );

		foreach ( $images as $image ) {
			$src      = $image->getAttribute( 'src' ) ?: '';
			$image_id = $image->getAttribute( 'data-imageid' ) ?: '';

			// Check the image needs to be migrated.
			if ( ! idg_can_image_be_migrated( $src ) ) {
				WP_CLI::line( "Skipping Image: {$src}" );
				continue;
			}

			$attachment_id     = false;
			$attachment_url    = ''; // placeholder image URL.
			$attachment_srcset = '';

			if ( idg_is_valid_image_url( $src ) ) {
				$attachment_id     = $this->migrate_image( $src, $image_id );
				$attachment_url    = wp_get_attachment_url( $attachment_id );
				$attachment_srcset = wp_get_attachment_image_srcset( $attachment_id );
			} else {
				// idg_is_valid_image_url will return false if image 404s in which case we want to remove the image.
				$image = $this->remove_image_parent( $image );
				continue;
			}

			// Set image attributes(update src/srcset).
			$image->setAttribute( 'src', $attachment_url );
			$image->setAttribute( 'srcset', $attachment_srcset );

			// Handle light box anchor element.
			if ( $image->parentNode && strpos( $image->parentNode->getAttribute( 'class' ), 'zoom' ) !== false ) {
				$image->parentNode->setAttribute( 'href', $attachment_url );
			}

			// Handle slide.
			$has_thumbnail = ! empty( $image->getAttribute( 'data-thumb-src' ) ?: '' );

			if ( $has_thumbnail ) {
				$attachment_image = wp_get_attachment_image_src( $attachment_id, 'full' );

				$size = $attachment_image[1] > $attachment_image[2] ? $attachment_image[2] : $attachment_image[1];

				$thumbnail_url = add_query_arg(
					[
						'resize' => "{$size},{$size}",
					],
					$attachment_image[0]
				);

				$image->setAttribute( 'data-thumb-src', $thumbnail_url );
			}

			WP_CLI::line( "Image: {$src}" );
			WP_CLI::line( "Replacement image: {$attachment_url}" );
			WP_CLI::line( '---' );
		}

		$post_content = $document->saveHTML();

		// If there's an issue saving the HTML just return as there's nothing we can do.
		if ( ! $post_content ) {
			WP_CLI::error( "Post {$post->ID}: Unable to save DOM document." );
			return;
		}

		// Strip the root node we added for \DOMDocument.
		$post_content = preg_replace( '/(^<div[^>]*>|<\/div>$)/i', '', $post_content );

		// Update post.
		wp_update_post(
			[
				'ID'           => $post->ID,
				'post_content' => $post_content,
			]
		);

		WP_CLI::success( "Post {$post->ID} content imported." );
	}

	/**
	 * Removes the image based on the parent container.
	 *
	 * @param \DOMElement $image The found image to remove the parent of.
	 * @return \DOMElement
	 */
	private function remove_image_parent( \DOMElement $image ) {
		$parent = $image;

		while ( $parent instanceof \DOMElement ) {
			if ( 'figure' === $parent->tagName ) {
				$parent->parentNode->removeChild( $parent );

				return $image;
			}

			$parent = $parent->parentNode;
		}

		return $image;
	}

	/**
	 * Post/entry processing here.
	 *
	 * @param \WP_Post $post The post object to be processed.
	 * @return void
	 */
	private function process_post( $post ) {
		// Migrate post content image(s).
		$this->post_content_images( $post );
	}
}
