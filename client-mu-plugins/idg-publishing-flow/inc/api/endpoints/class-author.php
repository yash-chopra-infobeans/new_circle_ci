<?php

namespace IDG\Publishing_Flow\API\Endpoints;

use IDG\Publishing_Flow\API\Request;
use IDG\Publishing_Flow\API\Data\Users;

/**
 * Handles and manages any Post requests.
 *
 * Each method should correspond with the defined
 * endpoint routes.
 */
class Author extends Request {
	const HOOK_AFTER_DEPLOY_AUTHOR = 'idg_publishing_flow_after_deploy_author';

	/**
	 * A list of endpoints to register and use.
	 *
	 * Keys should coincide with the method to be used
	 * for the endpoint called. In the below case,
	 * /post/create will call the create() method due
	 * to the key definition.
	 */
	const REST_ROUTES = [
		'update' => '/author/update',
	];

	/**
	 * The validation requirements that are used
	 * to check against the request data.
	 *
	 * @var array The validation options.
	 */
	public $validate = [];

	/**
	 * Update the post from the request.
	 *
	 * @param \WP_Request $request The request being made.
	 * @return object
	 */
	public function update( $request ) {
		if ( ! $this->check_entry_origin( $request ) ) {
			return $this->create_response();
		}

		if ( ! $this->validate_payload( $request ) ) {
			return $this->create_response();
		}

		$author    = $request->get_body_params();
		$author_id = Users::instance()->create( [ $author ] );

		return $this->create_response(
			[ 'author_id' => $author_id ]
		);
	}
}
