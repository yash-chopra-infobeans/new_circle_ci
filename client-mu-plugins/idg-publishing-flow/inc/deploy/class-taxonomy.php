<?php
/**
 * Handles the Taxonomy deployment methods.
 *
 * @package IDG-Publishing-Flow
 */

namespace IDG\Publishing_Flow\Deploy;

use IDG\Publishing_Flow\Deploy;
use IDG\Publishing_Flow\API\Endpoints\Taxonomy as Taxonomy_Endpoint;

/**
 * Handles the Taxonomy deployment methods.
 */
class Taxonomy extends Deploy {
	/**
	 * Filter Hook for when prior to when payload is deployed.
	 */
	const FILTER_PREPARE_PAYLOAD = 'idg_publishing_flow_prepare_term_payload';

	/**
	 * Construct the class by assigning the term and the destination.
	 * Also starts the process for preparing the payload and
	 * adds any required headers.
	 *
	 * @param \WP_Term $term           The term to be deployed.
	 * @param int      $publication_id The destination site.
	 */
	public function __construct( $term, int $publication_id ) {
		$this->term           = $term;
		$this->publication_id = $publication_id;
		$this->endpoints      = Taxonomy_Endpoint::REST_ROUTES;

		$this->prepare_payload();
		$this->add_headers();
	}

	/**
	 * Prepare the payload for when it is to be deployed.
	 *
	 * @return object
	 */
	private function prepare_payload() : object {
		$items = [
			$this->term,
		];

		$terms = $this->parents( $this->term->parent, $items );

		/**
		 * Allows for the alteration of the payload during
		 * preperation.
		 *
		 * @param array $payload The payload to be set.
		 * @param \WP_Post $post The post as provided during instantiation.
		 */
		$this->payload = apply_filters(
			self::FILTER_PREPARE_PAYLOAD,
			$terms,
			$this->term
		);

		return $this;
	}

	/**
	 * Get the parents, grandparents, and so on of
	 * the inserted term.
	 *
	 * @param string|int $parent_id The id of the parent to start seeknig from.
	 * @param array      $values The values of the gathered parents.
	 * @return array
	 */
	public function parents( $parent_id, $values = [] ) {
		if ( ! $parent_id || 0 === $parent_id ) {
			return $values;
		}

		$term = get_term( $parent_id, $this->term->taxonomy );

		$meta = get_term_meta( $term->term_id );

		foreach ( $meta as $key => $value ) {
			foreach ( $value as $mkey => $meta_value ) {
				$meta[ $key ] = maybe_unserialize( $meta_value );
			}
		}

		$disallowed_meta = apply_filters( self::FILTER_DISALLOWED_META, [] );

		foreach ( $disallowed_meta as $pattern ) {
			$found_keys = array_flip( preg_grep( "/^$pattern$/", array_keys( $meta ) ) );
			$meta       = array_diff_key( $meta, $found_keys );
		}

		$term->meta = $meta;

		$values[] = $term;

		if ( $term->parent ) {
			$values = $this->parents( $term->parent, $values );
		}

		return $values;
	}

	/**
	 * Deploy the payload to the given target url and
	 * handle the response values.
	 *
	 * @param string $target_url The url of the endpoint to make the request.
	 * @return object
	 */
	public function deploy( string $target_url ) {
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

		if ( is_wp_error( $this->response ) ) {
			$this->response_error = true;

			return $this;
		}

		$this->response_http = $this->response['http_response'];

		return $this;
	}

	/**
	 * Sets a create request to the target url.
	 *
	 * @return object
	 */
	public function create() {
		$target_url = $this->get_target_url( 'create' );

		return $this->deploy( $target_url );
	}

	/**
	 * Sets an update request to the target url.
	 *
	 * @return object
	 */
	public function update() {
		$target_url = $this->get_target_url( 'update' );

		return $this->deploy( $target_url );
	}
}
