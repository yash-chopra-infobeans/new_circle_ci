<?php

if ( class_exists( 'IDG_Tag_Rest' ) ) {
	return new IDG_Tag_Rest();
}

/**
 * Class to register custom rest route for `post_tag`
 * taxonomy with better child/parent heirachy.
 */
class IDG_Tag_Rest {

	/**
	 * The results returned back to the rest route.
	 *
	 * @var array Array of sorted results.
	 */
	protected $results = [];

	/**
	 * Adds custom rest route
	 */
	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register' ] );
	}

	/**
	 * Registers custom rest route
	 */
	public function register() {
		register_rest_route(
			'idg/v1',
			'post_tag',
			[
				'methods'             => [ 'GET' ],
				'callback'            => [ $this, 'get' ],
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			]
		);
	}

	/**
	 * Gets all tags: `post_tag`
	 *
	 * @param object $request The incoming rest request.
	 */
	public function get( WP_REST_Request $request = null ) { // phpcs:ignore Squiz.Commenting.FunctionComment.InvalidTypeHint
		$this->results = get_terms(
			[
				'taxonomy'   => 'post_tag',
				'hide_empty' => false,
				'number'     => 0,
			]
		);

		return $this->sort();
	}

	/**
	 * Sorts parent tags and their children into an array
	 *
	 * @param int $parent parent term id.
	 */
	protected function sort( $parent = 0 ) {
		$kids = array_values(
			array_filter(
				$this->results,
				function( $item ) use ( $parent ) {
					return $item->parent === $parent;
				}
			)
		);

		$sorted = [];

		foreach ( $kids as $kid ) {
			$sorted[] = $kid;
			$sorted   = array_merge( $sorted, $this->sort( $kid->term_id ) );
		}

		return $sorted;
	}

}
