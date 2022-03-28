<?php

namespace IDG\Publishing_Flow\Command;

use WP_CLI;
use IDG\Publishing_Flow\Data\Taxonomies;
use IDG\Publishing_Flow\Deploy\Taxonomy as Deploy_Taxonomy;
use IDG\Publishing_Flow\Sites;

/**
 * Publish command for WPCLI.
 */
class Destroy {
	private $taxonomies = null;

	/**
	 * Invoke the CLI command, first setting the command
	 * args and then going through the publish process.
	 *
	 * @param array $args WPCLI args.
	 * @param array $assoc_args WPCLI options.
	 * @return void
	 */
	public function __invoke( $args = [], $assoc_args = [] ) {
		if ( Sites::is_origin() ) {
			WP_CLI::error( 'This command can only be run on a delivery site.' );
		}

		$this->create_args( $assoc_args );

		WP_CLI::error_multi_line( [ 'WARNING:', 'This command is destructive. Use at your own risk.' ] );
		WP_CLI::confirm( 'Are you sure you want to do this?', $assoc_args );

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
		$allowed = [ 'terms' ];

		$this->type = $assoc_args['type'];

		if ( ! in_array( $this->type, $allowed, true ) ) {
			WP_CLI::error( "$this->type is not a valid type." );
			die;
		}

		if ( isset( $assoc_args['tax'] ) ) {
			$this->taxonomies = explode( ',', $assoc_args['tax'] );
		}
	}


	/**
	 * Republish and deploy all articles from
	 * the Content Hub to the given publication/Delivery
	 * Site based on the CLI arguments.
	 *
	 * @return void
	 */
	public function republish() {
		switch ( $this->type ) {
			case 'terms':
				$this->terms();
				break;
			default:
				WP_CLI::error( 'No valid type method defined.' );
		}
	}

	/**
	 * Clean up all terms used and added to a delivery site.
	 *
	 * @return void
	 */
	public function terms() {
		if ( ! $this->taxonomies ) {
			$this->taxonomies = get_taxonomies();
		}
		wp_suspend_cache_invalidation( true );

		foreach ( $this->taxonomies as $taxonomy ) {
			$terms = get_terms(
				[
					'taxonmomy'  => $taxonomy,
					'hide_empty' => false,
				]
			);

			WP_CLI::line( WP_CLI::colorize( "%CClearing out taxonomy: $taxonomy.%n" ) );

			$i = 1;

			foreach ( $terms as $term ) {
				remove_all_actions( 'clean_term_cache' );
				wp_delete_term( $term->term_id, $taxonomy, false );
				WP_CLI::success( "#{$i} {$taxonomy} term {$term->name} ({$term->term_id}) deleted." );
				$i++;
			}
		}
	}
}
