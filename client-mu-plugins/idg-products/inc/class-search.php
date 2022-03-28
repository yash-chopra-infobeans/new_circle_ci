<?php

namespace IDG\Products;

use WP_REST_Request;

/**
 * Management of product search querys.
 */
class Search {
	/**
	 * Add filters.
	 */
	public function __construct() {
		add_filter( 'rest_product_query', [ $this, 'custom_seach_query' ], 10, 2 );
	}

	/**
	 * Modify GET(search) request WP_Query parameters.
	 *
	 * @param array           $query_args - The unmodified query args.
	 * @param WP_REST_Request $request - The rest request.
	 * @return array
	 */
	public function custom_seach_query( array $query_args, WP_REST_Request $request ) {
		$params = $request->get_params();

		if ( isset( $params['manufacturer'] ) ) {
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			$query_args['tax_query'][] = [
				'taxonomy' => 'manufacturer',
				'field'    => 'term_id',
				'terms'    => $params['manufacturers'],
				'operator' => 'AND',
			];
		}

		if ( isset( $params['category'] ) ) {
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			$query_args['tax_query'][] = [
				'taxonomy' => 'category',
				'field'    => 'term_id',
				'terms'    => $params['category'],
				'operator' => 'AND',
			];
		}

		if ( isset( $params['origin'] ) ) {
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			$query_args['tax_query'][] = [
				'taxonomy' => 'origin',
				'field'    => 'term_id',
				'terms'    => $params['origin'],
				'operator' => 'AND',
			];
		}

		return $query_args;
	}
}
