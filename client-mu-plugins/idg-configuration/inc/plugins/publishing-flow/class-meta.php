<?php

namespace IDG\Configuration\Plugins\Publishing_Flow;

use IDG\Publishing_Flow\Deploy\Article;

/**
 * Handles meta data associated with Publishing Flow.
 */
class Meta {
	/**
	 * Add actions.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'register_meta' ] );
		add_filter( 'idg_publishing_flow_prepare_payload', [ $this, 'handle_payload_meta' ], 1, 2 );
		add_filter( 'idg_publishing_flow_prepare_payload', [ $this, 'add_primary_publication_to_payload' ], 1, 3 );
		add_filter( 'idg_publishing_flow_preinsert_meta', [ $this, 'handle_meta_injest_values' ] );
	}

	/**
	 * Register meta.
	 *
	 * @return void
	 */
	public function register_meta() {
		register_meta(
			'post',
			'primary_publication_id',
			[
				'description' => 'Primary (owner) publication.',
				'type'        => 'number',
				'single'      => true,
				'default'     => 0,
			]
		);
	}

	/**
	 * Add primary puublication as meta to post.
	 *
	 * @param array $payload Payload.
	 * @return array
	 */
	public function add_primary_publication_to_payload( $payload, $post, Article $article ) {
		$payload['meta']['primary_publication_id'] = $article->publication_id;

		return $payload;
	}

	/**
	 * Handle general meta data in the the payload before deploy.
	 *
	 * @param array $payload The current payload to be deployed.
	 * @return array
	 */
	public function handle_payload_meta( array $payload, $post ) : array {
		$meta = $payload['meta'];

		if( isset( $meta['multi_title'] ) ) {

			$title_string = is_array( $meta['multi_title'] ) ? $meta['multi_title'][0] : $meta['multi_title'];

			$titles = json_decode( $title_string, true );

			// See utils.php
			$titles = array_map_recursive( $titles, function($item) {
				// Strip first to ensure completeness with migrated data
				$item = \stripslashes( $item );
				if(mb_detect_encoding($item, 'UTF-8')) {
					return htmlentities($item, ENT_QUOTES);
				}

				return mb_convert_encoding( $item, ENT_QUOTES, 'UTF-8' );
			} );

			$meta['multi_title'][0] = wp_json_encode( $titles );
		}

		/**
		 * If the _idg_category_order meta has not been created in the
		 * migration. This will only affect legacy content.
		 */
		if( ! isset( $meta['_idg_category_order'] ) ) {
			$term_args = [
				'orderby' => 'name',
				'hide_empty' => false,
			];
			$saved_terms = wp_get_post_terms( $post->ID, 'category', $term_args );

			if( ! is_wp_error( $saved_terms ) ) {
				$terms = array_filter($saved_terms, function($term) {
					return ( $term->taxonomy === 'category' );
				});

				$terms = array_map(function($term) {
					return $term->term_id;
				}, $terms);

				$meta['_idg_category_order'] = $terms;
			}
		}

		$payload['meta'] = $meta;

		return $payload;
	}

	public function handle_meta_injest_values( $entry_meta ) {
		if( isset( $entry_meta['multi_title'] ) ) {
			$multi_title = is_array( $entry_meta['multi_title'] ) ? $entry_meta['multi_title'][0] : $entry_meta['multi_title'];

			$titles = array_map_recursive( json_decode( $multi_title, true ), function($item) {
				// Strip first to ensure completeness with migrated data
				$item = \stripslashes( $item );
				$item = html_entity_decode( $item, ENT_QUOTES, 'UTF-8' );
				return htmlentities( $item, ENT_QUOTES, 'UTF-8' );
			} );

			$entry_meta['multi_title'] = wp_json_encode( $titles );
		}

		return $entry_meta;
	}
}
