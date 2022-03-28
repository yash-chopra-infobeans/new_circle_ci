<?php

namespace IDG\Publishing_Flow\Command;

use WP_CLI;
use IDG\Publishing_Flow\Deploy\Article as Deploy_Article;
use IDG\Publishing_Flow\Sites;

/**
 * Publishing command for WPCLI.
 */
class Sync_Articles {
	/**
	 * The command type.
	 *
	 * @var string
	 */
	private $type = '';

	/**
	 * The publication ID to publish to.
	 *
	 * @var integer|null
	 */
	private $publication = null;

	/**
	 * Whether to force assign a publication.
	 *
	 * @var boolean
	 */
	private $assign_publication = false;

	/**
	 * The number of posts to publish.
	 *
	 * @var integer
	 */
	private $posts_num = 25;

	/**
	 * The number of posts to offset the query by.
	 *
	 * @var integer
	 */
	private $posts_offset = 0;

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

		if ( isset( $assoc_args['posts-num'] ) ) {
			$this->posts_num = $assoc_args['posts-num'];
		}

		if ( isset( $assoc_args['posts-offset'] ) ) {
			$this->posts_offset = $assoc_args['posts-offset'];
		}

		if ( isset( $assoc_args['all'] ) ) {
			$this->type   = 'create';
			$this->status = [ 'publish', 'updated' ];
		}

		if ( isset( $assoc_args['trashed'] ) ) {
			$this->type   = 'delete';
			$this->status = [ 'trash' ];
		}

		if ( isset( $assoc_args['on-hold'] ) ) {
			$this->type   = 'delete';
			$this->status = [ 'ready-publish', 'publish-ready', 'review-ready' ];
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
	 * Create the arguments require for get_posts().
	 *
	 * @return array
	 */
	private function get_posts_args() : array {
		$posts_args = [
			'numberposts'      => $this->posts_num ?: 25,
			'offset'           => $this->posts_offset ?: 0,
			'post_status'      => $this->status,
			'suppress_filters' => false,
		];

		if ( ! $this->assign_publication ) {
			$posts_args = array_merge(
				$posts_args,
				[
					'tax_query' => [
						'taxonomy'         => Sites::TAXONOMY,
						'field'            => 'term_id',
						'terms'            => $this->publication,
						'include_children' => false,
					],
				]
			);
		}

		return $posts_args;
	}

	/**
	 * Republish and deploy all articles from
	 * the Content Hub to the given publication/Delivery
	 * Site based on the CLI arguments.
	 *
	 * @return void
	 */
	public function republish() {
		$posts_args = $this->get_posts_args();

		$posts = get_posts( $posts_args );

		foreach ( $posts as $key => $post ) {
			$output_current = ( $key + 1 ) + $this->posts_offset;

			$deploy      = new Deploy_Article( $post, $this->publication );
			$publication = Sites::get_post_publication( $post->ID );

			if ( ! $publication && $this->assign_publication ) {
				$pub_parent_id = $this->publication_term->parent;
				$pub_id        = $this->publication_term->term_id;
				wp_set_post_terms( $post->ID, [ $pub_parent_id, $pub_id ] );
			}

			$deploy->add_headers( [ 'X-IDG-PUBFLOW-SKIP-MISSING-IMAGES' => true ] );

			switch ( $this->type ) {
				case 'delete':
					$error_message   = "Post {$post->ID} could not be removed.";
					$success_message = "#{$output_current} Post {$post->ID} removed.";
					$deploy->delete( $post->ID );
					break;
				default:
					$error_message   = "Post {$post->ID} could not be imported.";
					$success_message = "#{$output_current} Post {$post->ID} imported.";
					$deploy->create();
					break;
			}

			if ( $deploy->failed() ) {
				$error_messages = $deploy->get_data()->errors ?: [ 'No error message provided.' ];
				WP_CLI::error_multi_line(
					array_merge(
						[
							$error_message,
							'-------',
						],
						json_decode( json_encode( $error_messages ), true ),
					)
				);
			} else {
				WP_CLI::success( $success_message );
			}
		}
	}
}
