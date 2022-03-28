<?php

namespace IDG\Publishing_Flow\Command;

use WP_CLI;
use IDG\Publishing_Flow\Deploy\Article as Deploy_Article;
use IDG\Publishing_Flow\Sites;

/**
 * Publish command for WPCLI.
 */
class Sync_Article {
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
	private $post_id = null;

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

		if ( isset( $assoc_args['assign-pub'] ) ) {
			$this->assign_publication = true;
		}

		if ( isset( $assoc_args['post-id'] ) ) {
			$this->post_id = $assoc_args['post-id'];
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
	 * Republish and deploy all articles from
	 * the Content Hub to the given publication/Delivery
	 * Site based on the CLI arguments.
	 *
	 * @return void
	 */
	public function republish() {
		$post = get_post( $this->post_id );

		$deploy      = new Deploy_Article( $post, $this->publication );
		$publication = Sites::get_post_publication( $post->ID );

		if ( ! $publication && $this->assign_publication ) {
			$pub_parent_id = $this->publication_term->parent;
			$pub_id        = $this->publication_term->term_id;
			wp_set_post_terms( $post->ID, [ $pub_parent_id, $pub_id ] );
		}

		$deploy->add_headers( [ 'X-IDG-PUBFLOW-SKIP-MISSING-IMAGES' => true ] );
		$deploy->create();

		if ( $deploy->failed() ) {
			$error_messages = $deploy->get_data()->errors ?: [ 'No error message provided.' ];
			WP_CLI::error_multi_line(
				array_merge(
					[
						"Post {$post->ID} could not be imported.",
						'-------',
					],
					json_decode( json_encode( $error_messages ), true ),
				)
			);
		} else {
			WP_CLI::success( "Post {$post->ID} imported." );
		}
	}
}
