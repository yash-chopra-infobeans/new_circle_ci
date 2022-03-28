<?php

namespace IDG\Asset_Manager;

/**
 * Search config class.
 */
class Search {
	/**
	 * Add hooks and filters when class is initialized.
	 */
	public function __construct() {
		add_filter( 'vip_search_post_meta_allow_list', [ $this, 'allowed_meta' ], 10, 2 );
		add_filter( 'vip_search_post_taxonomies_allow_list', [ $this, 'allowed_taxonomies' ], 10, 2 );
		add_filter( 'rest_attachment_query', [ $this, 'custom_seach_query' ], 10, 2 );
		add_filter( 'ep_indexable_post_types', [ $this, 'indexable_post_types' ], 10, 1 );
		add_filter( 'ep_searchable_post_types', [ $this, 'searchable_post_types' ], 10, 1 );
		add_filter( 'ep_indexable_post_status', [ $this, 'indexable_post_status' ], 10, 1 );
		add_filter( 'ep_post_formatted_args', [ $this, 'custom_ep_add_author__in' ], 10, 2 );
		add_filter( 'ep_ajax_wp_query_integration', '__return_true' );
		add_filter( 'ep_admin_wp_query_integration', '__return_true' );
		add_filter( 'ep_skip_query_integration', [ $this, 'idg_skip_query_intergration' ], 10, 2 );
		add_filter( 'ep_elasticpress_enabled', [ $this, 'idg_elasticpress_enabled' ], 10, 2 );
	}

	/**
	 * Suggested here: https://github.com/10up/ElasticPress/issues/1758.
	 *
	 * @param boolean   $skip True to skip.
	 * @param \WP_Query $query WP Query to evaluate.
	 * @return boolean
	 */
	public function idg_skip_query_intergration( bool $skip, \WP_Query $query ) : bool {
		if ( 'attachment' === $query->query_vars['post_type'] ) {
			$skip = false;
		}

		return $skip;
	}

	/**
	 * Suggested here: https://github.com/10up/ElasticPress/issues/1758.
	 *
	 * @param boolean   $enabled Whether to integrate with Elasticsearch or not.
	 * @param \WP_Query $query WP_Query to evaluate.
	 * @return boolean
	 */
	public function idg_elasticpress_enabled( bool $enabled, \WP_Query $query ) : bool {
		if ( 'attachment' === $query->query_vars['post_type'] ) {
			$enabled = true;
		}

		return $enabled;
	}

	/**
	 * Attachment's usually have a post status of inherit, so we need to add it as an indexable status.
	 *
	 * @param array $statuses indexable post statuses.
	 * @return array
	 */
	public function indexable_post_status( array $statuses ) {
		if ( ! array_search( 'inherit', $statuses, true ) ) {
			$statuses[] = 'inherit';
		}

		return $statuses;
	}

	/**
	 * By default VIP Search/Elasticpress doesn't search attachments so we need to add it.
	 *
	 * @param array $post_types post types that should be searched.
	 * @return array
	 */
	public function searchable_post_types( array $post_types ) : array {
		$post_types['attachment'] = 'attachment';

		return $post_types;
	}

	/**
	 * By default VIP Search/Elasticpress doesn't index attachments so we need to add it.
	 *
	 * @param array $post_types post types that should be indexed.
	 * @return array
	 */
	public function indexable_post_types( array $post_types ) : array {
		$post_types['attachment'] = 'attachment';

		return $post_types;
	}

	/**
	 * Meta that VIP search should index
	 *
	 * @param array $meta meta fields that should be indexed.
	 * @return array
	 */
	public function allowed_meta( $meta ) : array {
		$meta[] = '_wp_attachment_image_alt';
		$meta[] = 'source';
		$meta[] = 'jw_player_media_id';
		$meta[] = 'status';

		return $meta;
	}

	/**
	 * Taxonomies that VIP search should index
	 *
	 * @param array $taxonomies taxonomies that should be indexed.
	 * @return array
	 */
	public function allowed_taxonomies( $taxonomies ) : array {
		$taxonomies[] = 'publication';
		$taxonomies[] = 'asset_tag';
		$taxonomies[] = 'asset_image_rights';

		return $taxonomies;
	}

	/**
	 * Add support to the `author__in` WP_Query parameter.
	 *
	 * @param array $formatted_args Formatted Args for the ES query.
	 * @param array $args           WP_Query args.
	 * @return array
	 */
	public function custom_ep_add_author__in( $formatted_args, $args ) {
		if ( ! empty( $args['author__in'] ) ) {
			$formatted_args['post_filter']['bool']['must'][]['bool']['must'] = [
				'terms' => [
					'post_author.id' => array_values( (array) $args['author__in'] ),
				],
			];
		}   return $formatted_args;
	}

	/**
	 * Modify attachment GET(search) request WP_Query arguments.
	 *
	 * @param array            $query_args WP_Query arguments, additional arguments are added by Elasticpress.
	 * @param \WP_REST_Request $request REST request.
	 * @return array
	 */
	public function custom_seach_query( array $query_args, \WP_REST_Request $request ) : array {
		// Run queries through Elasticsearch instead of MySQL.
		$query_args['ep_integrate'] = true;

		$query_args['search_fields'] = [
			'ID',
			'post_title',
			'post_content',
			'post_excerpt',
			'taxonomies' => [ 'publication', 'asset_tag', 'asset_image_rights' ],
			'meta'       => [ '_wp_attachment_image_alt' ],
		];

		if ( ! isset( $params['meta_query'] ) ) {
			$query_args['meta_query'] = [];
		}

		$query_args['meta_query']['relation'] = 'OR';

		$query_args['meta_query'][] = [
			'key'     => Meta_Fields::META_TYPE,
			'compare' => 'NOT EXISTS',
		];

		$query_args['meta_query'][] = [
			'key'     => Meta_Fields::META_TYPE,
			'compare' => '=',
			'value'   => '',
		];

		return $query_args;
	}
}
