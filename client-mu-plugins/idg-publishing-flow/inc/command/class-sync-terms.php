<?php

namespace IDG\Publishing_Flow\Command;

use WP_CLI;
use IDG\Publishing_Flow\Data\Taxonomies;
use IDG\Publishing_Flow\Deploy\Taxonomy as Deploy_Taxonomy;
use IDG\Publishing_Flow\Sites;

/**
 * Term sync command for WPCLI.
 */
class Sync_Terms {
	/**
	 * The publication ID to publish to.
	 *
	 * @var integer|null
	 */
	private $publication = null;

	/**
	 * The post ID being published
	 *
	 * @var integer|null
	 */
	private $taxonomies = [];

	/**
	 * Invoke the CLI command, first setting the command
	 * args and then going through the publish process.
	 *
	 * @param array $args WPCLI args.
	 * @param array $assoc_args WPCLI options.
	 * @return void
	 */
	public function __invoke( $args = [], $assoc_args = [] ) {
		$this->create_args( $assoc_args );
		$this->republish();
	}

	/**
	 * Create all the args and options from those
	 * passed through the CLI.
	 *
	 * @param array $assoc_args WPCLI options.
	 * @return void
	 */
	private function create_args( array $assoc_args ) {
		$this->publication = $assoc_args['publication'];

		if ( ! $this->does_publication_exist() ) {
			WP_CLI::error( "Publication with ID {$this->publication} does not exist." );
			die;
		}

		if ( isset( $assoc_args['taxonomies'] ) ) {
			$this->taxonomies = explode( ',', $assoc_args['taxonomies'] );
		}
	}

	/**
	 * Check whether the chosen publication exists
	 * in the system.
	 *
	 * @return bool
	 */
	private function does_publication_exist() : bool {
		$publication = get_term( $this->publication, 'publication' );

		if ( ! $publication || is_wp_error( $publication ) ) {
			return false;
		}

		$this->publication_term = $publication;

		return true;
	}


	/**
	 * Republish and deploy all terms from
	 * the Content Hub to the given publication/Delivery
	 * Site based on the CLI arguments.
	 *
	 * @return void
	 */
	public function republish() {
		if ( empty( $this->taxonomies ) ) {
			$this->taxonomies = get_taxonomies();
		}

		$expected_taxonomies = [ 'category', 'post_tag', 'publication', 'territory', 'story_types', 'article_types', 'sponsorships', 'blogs', 'podcast_series' ];

		$counter = 1;

		foreach ( $this->taxonomies as $taxonomy ) {
			if ( ! in_array( $taxonomy, $expected_taxonomies ) ) {
				continue;
			}

			$terms = get_terms(
				[
					'taxonomy'   => $taxonomy,
					'hide_empty' => false,
				]
			);

			usort(
				$terms,
				function( $a, $b ) {
					return $a->term_id <=> $b->term_id;
				}
			);

			WP_CLI::colorize( "%CProcessing taxonomy: $taxonomy.%n" );

			foreach ( $terms as $key => $term ) {
				$term->meta = Taxonomies::get_meta_values( $term );

				$deploy = new Deploy_Taxonomy( $term, $this->publication );

				$deploy->add_headers( [ 'X-IDG-PUBFLOW-SKIP-MISSING-IMAGES' => true ] );
				$deploy->create();

				if ( $deploy->failed() ) {
					$error_messages = $deploy->get_data()->errors ?: [ 'No error message provided.' ];
					WP_CLI::error_multi_line(
						array_merge(
							[
								"Term {$term->term_id} could not be imported.",
								'-------',
							],
							json_decode( json_encode( $error_messages ), true ),
						)
					);
				} else {
					WP_CLI::success( "#{$counter} {$term->taxonomy} term {$term->name} ({$term->term_id}) imported." );
				}

				unset( $terms[ $key ] ); // Clear out data when not required.
				$counter++;
			}
		}
	}
}
