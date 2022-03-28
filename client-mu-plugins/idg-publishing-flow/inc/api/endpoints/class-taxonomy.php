<?php

namespace IDG\Publishing_Flow\API\Endpoints;

use IDG\Publishing_Flow\API\Request;
use IDG\Publishing_Flow\Data\Taxonomies;

/**
 * Handles and manages any Post requests.
 *
 * Each method should correspond with the defined
 * endpoint routes.
 */
class Taxonomy extends Request {
	const HOOK_AFTER_DEPLOY_TERMS = 'idg_publishing_flow_after_deploy_terms';

	/**
	 * A list of endpoints to register and use.
	 *
	 * Keys should coincide with the method to be used
	 * for the endpoint called. In the below case,
	 * /post/create will call the create() method due
	 * to the key definition.
	 */
	const REST_ROUTES = [
		'create' => '/taxonomy/create',
		'update' => '/taxonomy/update',
		'delete' => '/taxonomy/unpublish',
	];

	/**
	 * The validation requirements that are used
	 * to check against the request data.
	 *
	 * @var array The validation options.
	 */
	public $validate = [
		'*.term_id'  => 'required|numeric',
		'*.name'     => 'required',
		'*.slug'     => 'required',
		'*.taxonomy' => 'required',
		'*.parent'   => 'numeric',
		'*.meta'     => 'array',
	];

	/**
	 * Create the post from the request.
	 *
	 * @param \WP_Request $request The request being made.
	 * @return object
	 */
	public function create( $request ) {
		if ( ! $this->check_entry_origin( $request ) ) {
			return $this->create_response();
		}

		if ( ! $this->validate_payload( $request ) ) {
			return $this->create_response();
		}

		$entry_terms = $request->get_body_params();
		$inserted    = Taxonomies::instance()->insert_terms( $entry_terms );

		do_action( self::HOOK_AFTER_DEPLOY_TERMS, $inserted, $entry_terms );

		idg_set_error_report_meta(
			[
				'terms'      => $inserted,
				'entry_term' => $entry_terms,
			]
		);

		return $this->create_response(
			[
				'term_ids' => $inserted,
			]
		);
	}

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

		$entry_terms = $request->get_body_params();
		$updated     = Taxonomies::instance()->insert_terms( $entry_terms );

		do_action( self::HOOK_AFTER_DEPLOY_TERMS, $updated, $entry_terms );

		if ( is_wp_error( $updated ) ) {
			$this->errors = $updated->errors;
		}

		return $this->create_response(
			[
				'term_ids' => $updated,
			]
		);
	}

	/**
	 * Delete the post from the request.
	 *
	 * Does not completely delete the post from
	 * the databse, but instead marks as trash incase
	 * recovery is required.
	 *
	 * @param \WP_Request $request The request being made.
	 * @return object
	 */
	public function delete( $request ) {
		$this->validate = [
			'id' => 'required|numeric|idExists',
		];

		if ( ! $this->validate_payload( $request ) ) {
			return $this->create_response();
		}

		// $body = $request->get_body_params();

		// Check term exists.
		// delete.

		return $this->create_response(
			[
				'message' => 'Term removed.',
			]
		);
	}
}
