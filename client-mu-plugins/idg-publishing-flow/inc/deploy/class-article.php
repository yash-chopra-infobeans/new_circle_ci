<?php
/**
 * Handles the deployment of articles to a given site.
 *
 * @package idg-publishing-flow
 */

namespace IDG\Publishing_Flow\Deploy;

use IDG\Publishing_Flow\Deploy;

use IDG\Publishing_Flow\Cache;
use IDG\Publishing_Flow\Loader;
use IDG\Publishing_Flow\API\Endpoints\Post as Post_Endpoint;
use IDG\Publishing_Flow\Data\Featured_Image;
use IDG\Publishing_Flow\Data\Taxonomies;
use IDG\Publishing_Flow\Data\Authors;
use IDG\Publishing_Flow\Data\Content;

/**
 * Handles the deployment of articles to a given site.
 */
class Article extends Deploy {
	/**
	 * Filter Hook for when prior to when payload is deployed.
	 */
	const FILTER_PREPARE_PAYLOAD = 'idg_publishing_flow_prepare_payload';

	/**
	 * Construct the class by assigning the post and the destination.
	 * Also starts the process for preparing the payload and
	 * adds any required headers.
	 *
	 * @param \WP_Post $post           The post to be deployed.
	 * @param int      $publication_id The destination site.
	 */
	public function __construct( \WP_Post $post, int $publication_id ) {
		$this->post           = $post;
		$this->publication_id = $publication_id;
		$this->endpoints      = Post_Endpoint::REST_ROUTES;

		$this->prepare_payload();
		$this->add_headers();
	}

	/**
	 * Check whether the site has already been published to
	 * or deployed to.
	 *
	 * @param array $published_ids List of possible publish ids.
	 * @return boolean
	 */
	public function has_deployed_before( $published_ids ) : bool {
		if ( ! $published_ids ) {
			return false;
		}

		if ( is_in_array( $this->publication_id, $published_ids, 'source' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Prepare the payload for when it is to be deployed.
	 *
	 * @return object
	 */
	private function prepare_payload() : object {
		$post = $this->post;
		$meta = Cache::get_all_meta( $post->ID );

		foreach ( $meta as $key => $value ) {
			foreach ( $value as $mkey => $meta_value ) {
				$meta[ $key ][ $mkey ] = maybe_unserialize( $meta_value );
			}
		}

		$disallowed_meta = apply_filters(
			self::FILTER_DISALLOWED_META,
			[
				'_edit_lock',
				'_thumbnail_id',
				'_oembed_.*',
				'_edit_last',
				Loader::META_POST_EMBARGO_DATE,
				Loader::META_POST_PUBLISHED_IDS,
				Loader::META_POST_PUBLISHED_STATUS,
			]
		);

		foreach ( $disallowed_meta as $pattern ) {
			$found_keys = array_flip( preg_grep( "/^$pattern$/", array_keys( $meta ) ) );
			$meta       = array_diff_key( $meta, $found_keys );
		}

		
		$post_date     = $this->post->post_date_gmt ?: $this->post->post_date;
		$post_modified = $this->post->post_modified_gmt ?: $this->post->post_modified;
		
		/**
		 * Allows for the alteration of the payload during
		 * preperation.
		 *
		 * @param array $payload The payload to be set.
		 * @param \WP_Post $post The post as provided during instantiation.
		 */
		$this->payload = apply_filters(
			self::FILTER_PREPARE_PAYLOAD,
			[
				'id'             => $this->post->ID,
				'source'         => $this->get_source(),
				'authors'        => Authors::instance()->format( $this->post->post_author ),
				'title'          => $this->post->post_title,
				'post_name'      => $this->post->post_name,
				'content'        => Content::instance()->format( $this->post->post_content ),
				'post_date'      => $post_date,
				'post_modified'  => $post_modified,
				'meta'           => $meta,
				'featured_image' => Featured_Image::instance()->get( $this->post->ID ),
				'terms'          => Taxonomies::instance()->get_post_terms( $this->post->ID ),
			],
			$this->post,
			$this
		);

		return $this;
	}

	/**
	 * Sets a create request to the target url.
	 *
	 * @return object
	 */
	public function create() {
		$target_url = $this->get_target_url( 'create' );

		$request = [
			'headers' => $this->headers,
			'body'    => $this->get_payload(),
		];

		$request = apply_filters(
			self::FILTER_REQUEST_ARGS,
			$request
		);

		$this->response = wp_remote_post( $target_url, $request );

		idg_set_error_report_meta(
			[
				'api_response' => $this->response,
			]
		);


		idg_notify_error(
			'ContentHub',
			'Publishing response',
			[
				'response' => $this->response,
			]
		);

		if ( is_wp_error( $this->response ) ) {
			$this->response_error = true;

			return $this;
		}

		$this->response_http = $this->response['http_response'];

		return $this;
	}

	/**
	 * Sends and update request to the target url.
	 *
	 * @param int $target_id The id of the post to update.
	 * @return object
	 */
	public function update( $target_id ) {
		$target_url = $this->get_target_url( 'update' );

		$request = [
			'headers' => $this->headers,
			'body'    => $this->get_payload(),
		];

		$request['body']['id'] = $target_id;

		$request = apply_filters( self::FILTER_REQUEST_ARGS, $request );

		$this->response = wp_remote_post( $target_url, $request );

		if ( is_wp_error( $this->response ) ) {
			$this->response_error = true;

			return $this;
		}

		$this->response_http = $this->response['http_response'];

		return $this;
	}

	/**
	 * Sends a removal request from the target site.
	 *
	 * @param int $target_id Id of the post on the target site to remove.
	 * @return object
	 */
	public function delete( $target_id ) {
		$target_url = $this->get_target_url( 'delete' );

		$request = [
			'headers' => $this->headers,
			'body'    => [
				'source' => $this->get_source(),
				'id'     => $target_id,
			],
		];

		$request = apply_filters(
			self::FILTER_REQUEST_ARGS,
			$request
		);

		$response = wp_remote_post( $target_url, $request );

		$this->response_http = $response['http_response'];

		return $this;
	}
}
