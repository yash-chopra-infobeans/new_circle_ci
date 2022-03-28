<?php

namespace IDG\Publishing_Flow\API;

use Rakit\Validation\Validator;
use IDG\Publishing_Flow\API\Validator\Post_ID_Exists;

/**
 * Manages any incoming API requests
 */
class Request {
	/**
	 * The default response code.
	 *
	 * @var integer
	 */
	public $response_code = 200;

	/**
	 * The built response for the API call.
	 *
	 * @var array
	 */
	private $response = null;

	/**
	 * Validation requirements.
	 *
	 * @var array
	 */
	public $validate = [];

	/**
	 * The validator that is being used.
	 *
	 * @var mixed
	 */
	public $validator = null;

	/**
	 * Any errors that may have occurred.
	 *
	 * @var array
	 */
	public $errors = [];

	/**
	 * Check if the request is a REST request.
	 *
	 * @return boolean
	 */
	public static function is_rest() {
		return ( defined( 'REST_REQUEST' ) && REST_REQUEST );
	}

	/**
	 * Validate the payload against the provided validate
	 * array of requirements.
	 *
	 * @param \WP_REST_Request $request The request being sent through WordPress.
	 * @return boolean
	 */
	protected function validate_payload( \WP_REST_Request $request ) : bool {
		$body = $request->get_body_params();

		$defaults = [];

		$this->validate = array_merge( $defaults, $this->validate );

		$validator = new Validator();
		$validator->addValidator( 'idExists', new Post_ID_Exists() );
		$this->validator = $validator->validate( $body, $this->validate );

		if ( $this->validator->fails() ) {
			$this->response_code = 400;
			$this->errors        = $this->validator->errors()->toArray();
			return false;
		}

		return true;
	}

	/**
	 * Checks that the origin of the request is as expected.
	 *
	 * @param \WP_REST_Request $request The request being sent through WordPress.
	 * @return boolean
	 */
	protected function check_entry_origin( \WP_REST_Request $request ) : bool {
		$entry_origin_header = $request->get_header( PUBLISHING_FLOW_ENTRY_ORIGIN_HEADER );

		if (
			! defined( 'PUBLISHING_FLOW_EXPECTED_SOURCE_URL' )
			|| PUBLISHING_FLOW_EXPECTED_SOURCE_URL !== $entry_origin_header
		) {
			$this->response_code = 400;
			$this->errors        = [ 'Your request was incorrect.' ];

			return false;
		}

		return true;
	}

	/**
	 * Create and handle the response for the REST request
	 * source to read from.
	 *
	 * @param array $data Data being used in the response if it is valid.
	 * @return object
	 */
	protected function create_response( $data = [] ) {
		if ( count( $this->errors ) > 0 ) {
			$this->response = [
				'status' => $this->response_code,
				'errors' => $this->errors,
			];
		} else {
			$this->response = [
				'status' => $this->response_code,
				'data'   => $data,
			];
		}

		$response = new \WP_REST_Response( $this->response );
		$response->set_status( $this->response_code );

		idg_notify_error(
			'DeliverySite',
			'Responding',
			[
				'response' => $response,
			]
		);

		return $response;
	}
}
