<?php

if ( class_exists( 'IDG_Article_Subtag_Lookup_Rest' ) ) {
	return new IDG_Article_Subtag_Lookup_Rest();
}

/**
 * Class to register custom rest route for subtag lookup.
 */
class IDG_Article_Subtag_Lookup_Rest {

	const SEPERATOR = '-';

	const PARTS = [ 'site_id', 'medium_code', 'article_id', 'position', 'product_id', 'manufacturer_id' ];

	/**
	 * The result returned back to the rest route.
	 *
	 * @var array Array result.
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
			'/article_subtag_lookup/(?P<subtag>[a-zA-Z0-9-]+)',
			[
				'methods'             => [ 'GET' ],
				'callback'            => [ $this, 'get' ],
				'permission_callback' => [ $this, 'authenticate' ],
			]
		);
	}

	/**
	 * Checks the authentication headers for oAuth2 tokens.
	 *
	 * @return boolean
	 */
	public function authenticate(): bool {

		$token = \WP\OAuth2\Authentication\attempt_authentication();

		if ( $token ) {
			return true;
		}

		return false;
	}

	/**
	 * Gets result of requested subtag.
	 *
	 * @param WP_REST_Request $request The incoming rest request.
	 *
	 * @return array
	 */
	public function get( WP_REST_Request $request = null ) {

		if ( empty( $request ) ) {
			return [];
		}

		$subtag = $request->get_param( 'subtag' );

		if ( empty( $subtag ) ) {
			return [];
		}

		$subtag = explode( self::SEPERATOR, $subtag );

		$subtag_parts = [];
		if ( count( self::PARTS ) === count( $subtag ) ) {
			$subtag_parts = array_combine( self::PARTS, $subtag );
		}

		return $this->get_subtag_information( $subtag_parts );
	}

	/**
	 * Gets subtag related information.
	 *
	 * @param array $subtag_parts The subtag data.
	 *
	 * @return string[]
	 */
	protected function get_subtag_information( $subtag_parts = [] ) {

		$this->results = [
			'headline'          => '',
			'contentType'       => '',
			'categories'        => '',
			'tags'              => '',
			'product'           => '',
			'manufacturer'      => '',
			'productCategories' => '',
		];

		if ( empty( $subtag_parts ) ) {
			wp_send_json_error( [ 'error' => 'subtag is incorrect or empty' ], 400 );
		}

		// TODO - Need clarification what to do with `site_id`?
		if ( ! empty( $subtag_parts['site_id'] ) ) {
		}

		if ( ! empty( $subtag_parts['article_id'] ) && is_numeric( $subtag_parts['article_id'] ) ) {
			$post = get_post( $subtag_parts['article_id'] );

			$categories_list = wp_get_post_categories( $post->ID, [ 'fields' => 'names' ] );
			$tags_list       = wp_get_post_tags( $post->ID, [ 'fields' => 'names' ] );
			$story_type_list = wp_get_post_terms( $post->ID, 'story_types', [ 'fields' => 'names' ] );

			$this->results['headline'] = $post->post_title;

			if ( ! is_wp_error( $categories_list ) ) {
				$this->results['categories'] = join( ',', $categories_list );
			}

			if ( ! is_wp_error( $tags_list ) ) {
				$this->results['tags'] = join( ',', $tags_list );
			}

			if ( ! is_wp_error( $story_type_list ) ) {
				$this->results['contentType'] = join( ',', $story_type_list );
			}
		}

		if ( ! empty( $subtag_parts['product_id'] ) && is_numeric( $subtag_parts['product_id'] ) ) {
			$product = get_post( $subtag_parts['product_id'] );

			if ( ! empty( $product ) && is_a( $product, 'WP_Post' ) && ! empty( $product->post_title ) ) {
				$this->results['product'] = $product->post_title;

				$taxonomies = [
					'manufacturer'      => 'manufacturer',
					'productCategories' => 'category',
				];

				foreach ( $taxonomies as $taxonomie => $value ) {
					$taxonomie_list = wp_get_post_terms( $product->ID, $value, [ 'fields' => 'names' ] );

					if ( ! is_wp_error( $taxonomie_list ) ) {
						$this->results[ $taxonomie ] = join( ',', $taxonomie_list );
					}
				}
			}
		}

		return $this->results;
	}

}
