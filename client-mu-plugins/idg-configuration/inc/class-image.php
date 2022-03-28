<?php

namespace IDG\Configuration;

/**
 * Image class for ensuring migrated images work.
 */
class Image {
	/**
	 * Add the required hooks.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'set_image_quality' ] );

		add_filter( 'image_downsize', [ $this, 'image_migration_downsize' ], 10, 3 );
		add_filter( 'wp_get_attachment_image_src', [ $this, 'image_migration_src' ], 10, 4 );
		add_filter( 'rest_prepare_attachment', [ $this, 'get_migrated_image_metadata' ], 10, 3 );
		add_filter( 'wp_calculate_image_srcset', [ $this, 'add_photon_params_to_srcset_urls' ], 10, 5 );
	}

	/**
	 * Add qaulity and strip parameter to srcset URL's to ensure that URL's will serve webp's where possible.
	 *
	 * @param array $sources One or more arrays of source data to include in the 'srcset'.
	 * @return array
	 */
	public function add_photon_params_to_srcset_urls( $sources ) {
		foreach ( $sources as $w => $source ) {
			$sources[ $w ]['url'] = add_query_arg(
				[
					'quality' => '50',
					'strip'   => 'all',
				],
				$sources[ $w ]['url']
			);
		}

		return $sources;
	}

	/**
	 * Check whether the image is external by checking if the current domain is found within the guid.
	 *
	 * @param integer $attachment_id  Attachment ID for image.
	 * @return boolean Returns false if guid domain is not equal to the current domain and true if it does.
	 */
	public function is_migrated_image( int $attachment_id ) {
		$attachment = get_post( $attachment_id );

		$domain = wp_parse_url( get_site_url(), PHP_URL_HOST );
		$guid   = wp_parse_url( $attachment->guid, PHP_URL_HOST );

		return $domain !== $guid;
	}

	/**
	 * Set the quality of jpeg images served from files.wordpress.com
	 *
	 * @return void
	 */
	public function set_image_quality() : void {
		if ( 'local' === VIP_GO_APP_ENVIRONMENT ) {
			return;
		}

		$set_quality = false;

		if ( function_exists( 'wpcom_vip_set_image_quality' ) ) {
			wpcom_vip_set_image_quality( 50, 'all' );

			$set_quality = true;
		}
	}

	/**
	 * If image has not been migrated, bypass image downsize.
	 *
	 * @param bool|array $short_circuit Whether to short-circuit the image downsize.
	 * @param int        $attachment_id Attachment ID for image.
	 * @return bool|array
	 */
	public function image_migration_downsize( $short_circuit, $attachment_id ) {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			return $short_circuit;
		}

		if ( $this->is_migrated_image( (int) $attachment_id ) ) {
			$short_circuit = true;
		}

		return $short_circuit;
	}

	/**
	 * If image has not been migrated, return original image array with guid as the file url.
	 *
	 * @param array|false  $image Array of image data, or boolean false if no image is available.
	 * @param int          $attachment_id Image attachment ID.
	 * @param string|int[] $size Requested image size. Can be any registered image size name, or an array of width and height values in pixels (in that order).
	 * @param bool         $icon Whether the image should be treated as an icon.
	 * @return array|false
	 */
	public function image_migration_src( $image, $attachment_id, $size, $icon ) {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			return $image;
		}

		$attachment = get_post( $attachment_id );

		if ( $this->is_migrated_image( (int) $attachment_id ) ) {
			$width  = get_post_meta( $attachment_id, 'width', true ) ?: 0;
			$height = get_post_meta( $attachment_id, 'height', true ) ?: 0;
			$image  = [
				$attachment->guid,
				(int) $width,
				(int) $height,
				false,
			];
		}

		return $image;
	}

	/**
	 * Add the minimun required meta data to the media response for migrated images.
	 *
	 * This is required for the components such as featured image for example as the react component uses the
	 * width/size, without we have overlapping issues in the UI and possible other undiscovered bugs.
	 *
	 * Reference to related editor component: https://github.com/WordPress/gutenberg/blob/004a32ec09d5b789be694d85725fe49cca385f08/packages/editor/src/components/post-featured-image/index.js
	 *
	 * @param \WP_REST_Response $response The response object.
	 * @param \WP_Post          $post The original attachment post.
	 * @return \WP_REST_Response
	 */
	public function get_migrated_image_metadata( $response, $post ) {
		$attachment = get_post( $post->ID );

		if ( ! $attachment ) {
			return $response;
		}

		if ( ! $this->is_migrated_image( (int) $post->ID ) ) {
			return $response;
		}

		$width  = get_post_meta( $post->ID, 'width', true ) ?: 0;
		$height = get_post_meta( $post->ID, 'height', true ) ?: 0;

		if ( empty( $width ) || empty( $height ) ) {
			return $response;
		}

		$response->data['media_details'] = [
			'width'  => (int) $width,
			'height' => (int) $height,
			'file'   => $attachment->guid,
		];

		return $response;
	}
}
