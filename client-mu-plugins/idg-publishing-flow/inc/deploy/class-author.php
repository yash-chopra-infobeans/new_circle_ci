<?php

namespace IDG\Publishing_Flow\Deploy;

use IDG\Publishing_Flow\Deploy;
use IDG\Publishing_Flow\API\Endpoints\Author as Author_Endpoint;

/**
 * Handles the Author deployment methods.
 */
class Author extends Deploy {
	/**
	 * Filter Hook for when prior to when payload is deployed.
	 */
	const FILTER_PREPARE_PAYLOAD = 'idg_publishing_flow_prepare_author_payload';

	/**
	 * Construct the class by assigning the author and the destination.
	 * Also starts the process for preparing the payload and
	 * adds any required headers.
	 *
	 * @param array $author The author to be deployed.
	 * @param int   $publication_id The destination site.
	 */
	public function __construct( array $author, int $publication_id ) {
		$this->author         = $author;
		$this->publication_id = $publication_id;
		$this->endpoints      = Author_Endpoint::REST_ROUTES;

		$this->prepare_payload();
		$this->add_headers();
	}

	/**
	 * Prepare the payload for when it is to be deployed.
	 *
	 * @return object
	 */
	private function prepare_payload() : object {
		/**
		 * Allows for the alteration of the payload during
		 * preperation.
		 *
		 * @param array $payload The payload to be set.
		 */
		$this->payload = apply_filters(
			self::FILTER_PREPARE_PAYLOAD,
			$this->author
		);

		return $this;
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
	public function create_or_update() {
		$target_url = $this->get_target_url( 'update' );

		return $this->deploy( $target_url );
	}
}
