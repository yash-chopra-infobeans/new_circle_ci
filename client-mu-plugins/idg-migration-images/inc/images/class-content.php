<?php

namespace IDG\Migration\Images;

use WP_CLI;
use IDG\Migration\Images\CLI;
use IDG\Publishing_Flow\Deploy\Article as Deploy_Article;

// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
class Content extends Base {
	private $cli = null;

	public function __construct( CLI $cli ) {
		$this->cli     = $cli;
		$this->amount  = $cli->amount;
		$this->offset  = $cli->offset;
		$this->include = $cli->include;
		$this->publish = $cli->publish;
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

		add_filter( Deploy_Article::FILTER_PREPARE_PAYLOAD, [ $this, 'attach_to_payload' ], 10, 2 );

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

			if ( $this->publish ) {
				$this->deploy_post( $post->ID );
			}
		}

		remove_filter( Deploy_Article::FILTER_PREPARE_PAYLOAD, [ $this, 'attach_to_payload' ], 10, 1 );
	}

	/**
	 * Add some some additional data to payload.
	 *
	 * @param array $payload The payload to be sent to the Delivery Site.
	 * @param array $post post object.
	 * @return array
	 */
	public function attach_to_payload( $payload, $post ) {
		if ( ! $payload['id'] || ! $post ) {
			return $payload;
		}

		$payload['post_modified']     = $post->post_modified;
		$payload['post_modified_gmt'] = $post->post_modified_gmt;

		return $payload;
	}

	/**
	 * Migrate image.
	 *
	 * @param string $url image url to migrate.
	 * @param string $image_id old one cms image id.
	 * @return int
	 */
	public function migrate_image( string $url, string $image_id ) {
		// idg_get_post_by_guid will check if an attachment exists with the guid or meta value of $url.
		$attachment_id  = (int) idg_get_post_by_guid( $url );
		$attachment_url = wp_get_attachment_url( $attachment_id );

		/**
		 * If we do not have an attachment id then we need to migrate the image.
		 *
		 * If we have an attachment id then we need to check that the $attachment_url needs to be migrated. If
		 * we do need to migrate the image then go through the $this->handle_image function otherwise return the id
		 * we heve.
		 */
		if ( ! $attachment_id || ( $attachment_url && idg_can_image_be_migrated( $attachment_url ) ) ) {
			$attachment_id = $this->handle_image( $url, $attachment_id );
		}

		return $attachment_id;
	}

	/**
	 * Get WordPress image size based on URL.
	 *
	 * @param string $src url of image.
	 * @return string
	 */
	public function get_size( string $src ) : string {
		// Defaults to full - which will be the original image.
		$size = 'full';

		if ( strpos( $src, '-large.' ) !== false ) {
			$size = 'large';
		}

		if ( strpos( $src, '-medium.' ) !== false ) {
			$size = 'medium';
		}

		if ( strpos( $src, '-small.' ) !== false ) {
			$size = 'thumbnail';
		}

		return $size;
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

		$images_to_remove = [];

		foreach ( $images as $image ) {
			$src      = $image->getAttribute( 'src' ) ?: '';
			$image_id = $image->getAttribute( 'data-imageid' ) ?: '';

			// Check the image needs to be migrated.
			if ( ! idg_can_image_be_migrated( $src ) ) {
				WP_CLI::line( "Skipping Image: {$src}" );
				continue;
			}

			$attachment_id = false;

			$largest_src = $this->get_largest_size( $src );

			if ( $largest_src ) {
				$attachment_id = $this->migrate_image( $largest_src, $image_id );
			} else {
				// idg_is_valid_image_url will return false if image 404s in which case we want to remove the image.
				$images_to_remove[] = $image;
				continue;
			}

			// Get the WordPress image size based on the image url we migrated, returns full if one cannot be found.
			$size = $this->get_size( $src );

			/**
			 * wp_get_attachment_image_src return an array:
			 * [0] - Image source URL.
			 * [1] - Image width in pixels.
			 * [2] - image height in pixels.
			 */
			$attachment =  wp_get_attachment_image_src( $attachment_id, $size );

			// If for whatever reason we cannot get the size then fallback to the full/original image.
			if ( ! $attachment ) {
				$attachment =  wp_get_attachment_image_src( $attachment_id, 'full' );
				$size       = 'full';
			}

			// Set image attributes(update src/srcset).
			$image->setAttribute( 'src', $attachment[0] );
			$image->setAttribute( 'srcset', wp_get_attachment_image_srcset( $attachment_id, $size ) );
			$image->setAttribute( 'width', $attachment[1] );
			$image->setAttribute( 'height', $attachment[2] );
			$image->setAttribute( 'loading', 'lazy' );

			$attachment_url = wp_get_attachment_url( $attachment_id );

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

		}

		foreach ($images_to_remove as $image) {
			$this->remove_image_parent( $image );
		}

		$post_content = $document->saveHTML();

		// If there's an issue saving the HTML just return as there's nothing we can do.
		if ( ! $post_content ) {
			WP_CLI::error( "Post {$post->ID}: Unable to save DOM document." );
			return;
		}

		// Strip the root node we added for \DOMDocument.
		$post_content = preg_replace( '/(^<div[^>]*>|<\/div>$)/i', '', $post_content );

		add_filter( 'wp_insert_post_data', 'idg_alter_post_modification_time', 99, 2 );
		// Update post.
		wp_update_post(
			[
				'ID'                => $post->ID,
				'post_content'      => $post_content,
				'post_modified'     => $post->post_modified,
				'post_modified_gmt' => $post->post_modified_gmt,
			]
		);
		remove_filter( 'wp_insert_post_data', 'idg_alter_post_modification_time', 99, 2 );
	}

	private function get_largest_size( string $url ) {
		$sizes = [
			'-orig.',
			'-large.',
			'-medium.',
			'-small',
		];

		foreach ( $sizes as $size ) {
			$replacements = array_combine( $sizes, array_fill( 0, count( $sizes ), $size ) );

			$url = str_replace( array_keys( $replacements ), array_values( $replacements ), $url );

			if ( idg_is_valid_image_url( $url ) ) {
				return $url;
			}
		}

		return null;
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

		$image->parentNode->removeChild( $image );
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

		// Migrate post featured image(s).
		$this->post_featured_image( $post );

		WP_CLI::success( "Post {$post->ID} content imported." );
		WP_CLI::line( '---' );
	}
}
