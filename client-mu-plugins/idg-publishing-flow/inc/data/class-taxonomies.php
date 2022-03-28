<?php

namespace IDG\Publishing_Flow\Data;

use IDG\Publishing_Flow\Cache;
use IDG\Publishing_Flow\Sites;

/**
 * Taxonomy data handling class.
 */
class Taxonomies extends Data {
	const HOOK_EXCLUDE = 'idg_publishing_flow_exclude_taxonomies';

	const HOOK_GET_TERM_ARGS = 'idg_publishing_flow_get_term_args';

	const HOOK_GET_SAVED_TERMS = 'idg_publishing_flow_get_saved_terms';

	const HOOK_GET_TERM_META = 'idg_publishing_flow_get_term_meta';

	const HOOK_GET_TERM_META_TAXONOMY = 'idg_publishing_flow_get_term_meta_';

	const HOOK_INSERT_TERM_META = 'idg_publishing_flow_insert_term_meta';

	const HOOK_INSERT_TERM_META_TAXONOMY = 'idg_publishing_flow_insert_term_meta_';

	/**
	 * Get an instance of the class.
	 */
	public static function instance() {
		return new self();
	}

	/**
	 * Get the meta values of a term.
	 *
	 * @param string $term The term to request.
	 * @return array
	 */
	public static function get_meta_values( $term ) : array {
		global $wp_meta_keys;

		$terms = $wp_meta_keys['term'];

		if ( ! isset( $terms[ $term->taxonomy ] ) ) {
			return [];
		}

		// Get the taxonomy arguments for the required taxonomy.
		$term_tax = $terms[ $term->taxonomy ];

		$meta_terms = [];

		// Loop through the meta and use the args to request singular if set.
		foreach ( $term_tax as $key => $args ) {
			$meta_terms[ $key ] = get_term_meta( $term->term_id, $key, $args['single'] );
		}

		$hook_string = static::HOOK_GET_TERM_META_TAXONOMY . $term->taxonomy;
		$meta_terms  = apply_filters( $hook_string, $meta_terms, $term );
		$meta_terms  = apply_filters( static::HOOK_GET_TERM_META, $meta_terms, $term );

		return $meta_terms;
	}

	/**
	 * Get all the terms attached to the given post.
	 *
	 * @param int|string $post_id The post ID.
	 * @return array An array of inested term information.
	 */
	public function get_post_terms( $post_id ) {
		$terms      = [];
		$excluded   = apply_filters( static::HOOK_EXCLUDE, [] );
		$taxonomies = array_filter(
			get_taxonomies(),
			function( $taxonomy ) use ( $excluded ) {
				return ! in_array( $taxonomy, $excluded, true );
			}
		);

		$term_args = [
			'hide_empty'   => false,
			'orderby'      => 'parent',
			'hierarchical' => true,
		];

		$term_args   = apply_filters( static::HOOK_GET_TERM_ARGS, $term_args, $post_id );
		$saved_terms = wp_get_post_terms( $post_id, $taxonomies, $term_args );

		// foreach ( $saved_terms as $key => $term ) {
		// $terms = array_merge( $terms, $this->get_term_parent( $term ) );
		// }

		$terms = apply_filters( static::HOOK_GET_SAVED_TERMS, $saved_terms, $post_id );

		// Order by term_id.
		usort(
			$terms,
			function( $a, $b ) {
				return $a->term_id <=> $b->term_id;
			}
		);

		$excluded_meta = apply_filters(
			'idg_publishing_flow_excluded_meta',
			[
				'publication',
				'business_unit',
			]
		);

		foreach ( $terms as $key => $term ) {
			$term_meta           = $this->get_meta_values( $term );
			$terms[ $key ]->meta = ( new Data() )->strip_keys( $term_meta, $excluded_meta );
		}

		return $terms;
	}

	/**
	 * Update the terms array to include all parents referenced.
	 *
	 * @param array $terms List of terms.
	 * @return array
	 */
	public function get_term_parent( $term ) : array {
		$ancestors = get_ancestors( $term->term_id, $term->taxonomy, 'taxonomy' );

		$terms = [ $term ];

		foreach ( $ancestors as $ancestor ) {
			$terms[] = get_term( $ancestor );
		}

		return $terms;
	}

	/**
	 * Creates or updates the terms and assigns parents
	 * based on the availability of items in the list.
	 *
	 * @throws \ErrorException Throws when term cannot be inserted.
	 * @param array $terms The terms to be created.
	 * @return array
	 */
	public function insert_terms( $terms ) : array {
		$inserted = [];

		// Insert term.
		foreach ( $terms as $term ) {
			// @todo: Check that taxonomy exists and is valid.
			$inserted_term = $this->get_term_by_chid( $term['term_id'], $term['taxonomy'] );

			$args = [
				'name'        => $term['name'],
				'slug'        => $term['slug'],
				'description' => $term['description'],
			];

			if ( is_wp_error( $inserted_term ) ) {
				$parent         = $this->get_term_by_chid( $term['parent'], $term['taxonomy'] );
				$args['parent'] = ! is_wp_error( $parent ) ? $parent['term_id'] : 0;
				$inserted_term  = wp_insert_term( $term['name'], $term['taxonomy'], $args );
			} else {
				$inserted_term = wp_update_term( $inserted_term['term_id'], $term['taxonomy'], $args );
			}

			error_log(
				print_r(
					[
						'term'        => $term,
						'is_wp_error' => is_wp_error( $inserted_term ),
						'error'       => $inserted_term,
					],
					true
				)
			);

			if ( is_wp_error( $inserted_term ) ) {
				$error_string = sprintf( 'Could not create term (%s)', $term['name'] );
				throw new \ErrorException( $error_string );
			}

			update_term_meta( $inserted_term['term_id'], 'content_hub_id', $term['term_id'] );

			$meta_values = isset( $term['meta'] ) ? $term['meta'] : [];

			$inserted[] = [
				'term'     => $inserted_term,
				'taxonomy' => $term['taxonomy'],
			];

			do_action( static::HOOK_INSERT_TERM_META, $meta_values, $inserted_term );
			do_action( static::HOOK_INSERT_TERM_META_TAXONOMY . $term['taxonomy'], $meta_values, $inserted_term );
		}

		$inserted = group_by_key( 'taxonomy', $inserted, 'term.term_id' );

		return $inserted;
	}

	/**
	 * Insert the new terms and remove the relationship
	 * of the previous terms attached to the post.
	 *
	 * @param array      $terms A list of terms to insert.
	 * @param int|string $post_id The post ID.
	 * @throws ErrorException When term is not found.
	 * @return void
	 */
	public function insert_post_terms( $terms, $post_id ) : void {
		$taxonomies = get_taxonomies( [], 'names' );

		wp_delete_object_term_relationships( $post_id, $taxonomies );

		$inserted = $this->insert_terms( $terms );

		foreach ( $inserted as $inserted_taxonomy => $inserted_term_ids ) {
			wp_set_post_terms( $post_id, $inserted_term_ids, $inserted_taxonomy );
		}
	}

	/**
	 * Get the term by the content hub identifier.
	 *
	 * @param int|string $content_hub_id The id used on the content hub for the term.
	 * @param string     $taxonomy The taxonomy of the term.
	 * @return array
	 */
	public function get_term_by_chid( $content_hub_id, string $taxonomy ) {
		$existing = get_terms(
			[
				'taxonomy'     => $taxonomy,
				'hide_empty'   => false,
				'meta_query'   => [
					[
						'key'     => 'content_hub_id',
						'value'   => $content_hub_id,
						'compare' => '=',
					],
				],
				'hierarchical' => false,
			]
		);

		if ( empty( $existing ) || is_wp_error( $existing ) ) {
			return new \WP_Error(
				'pubflow_ch_term',
				'Term does not exist.',
				[
					'content_hub_id' => $content_hub_id,
					'taxonomy'       => $taxonomy,
				]
			);
		}

		return (array) $existing[0];
	}

	/**
	 * Updates a single term, otherwise will attempt insertion.
	 *
	 * @param array $term The term to insert.
	 * @return array
	 */
	public function update_term( $term ) {
		$existing = $this->get_term_by_chid( $term['term_id'], $term['taxonomy'] );

		if ( is_wp_error( $existing ) ) {
			return $this->insert_terms( [ $term ] );
		}

		$term['term_id'] = $existing[0]->term_id;
		$term['meta']    = $term['meta'] ?: [];

		$updated_term = wp_update_term( $term['term_id'], $term['taxonomy'], $term );

		if ( is_wp_error( $updated_term ) ) {
			return $updated_term;
		}

		// Insert meta after term to ensure any references have been created.
		do_action( static::HOOK_INSERT_TERM_META, $term['meta'], $term );
		do_action( static::HOOK_INSERT_TERM_META_TAXONOMY . $term['taxonomy'], $term['meta'], $term );

		return [ $updated_term['term_id'] ];
	}
}
