<?php

namespace IDG\Migration\Images;

use WP_CLI;
use IDG\Migration\Images\CLI;

// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
class Featured_Image extends Base {
	private $cli = null;

	public function __construct( CLI $cli ) {
		$this->cli          = $cli;
		$this->amount       = $cli->amount;
		$this->offset       = $cli->offset;
		$this->include      = $cli->include;
		$this->post_type    = $cli->post_type;
		$this->publish      = $cli->publish;
		$this->publications = $cli->publications;
	}

	public static function instance( CLI $cli ) {
		return new self( $cli );
	}

	/**
	 * Start the migration run by looping through the
	 * the amount and offset counters. Each entry will
	 * be processed and handled on it's own.
	 *
	 * @return void
	 */
	public function migrate() {
		$end_num = $this->offset + ( $this->amount - 1 );

		for ( $i = $this->offset; $i <= $end_num; $i++ ) {
			// phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.get_posts_get_posts -- We do not want to use cached results for this process and require current data at time of request.
			$posts = get_posts(
				[
					'numberposts' => 1,
					'offset'      => $i,
					'include'     => $this->include,
					'post_type'   => $this->post_type,
				]
			);

			$post = isset( $posts[0] ) ? $posts[0] : false;

			if ( ! $post ) {
				WP_CLI::line( WP_CLI::colorize( '%yNo Post matching get_posts query.%n' ) );
				continue;
			}

			$this->process_post( $post );

			if ( $this->publish && 'product' !== $this->post_type ) {
				$this->deploy_post( $post );
			}
		}
	}

	/**
	 * Post/entry processing here.
	 *
	 * @param \WP_Post $post The post object to be processed.
	 * @return void
	 */
	private function process_post( $post ) {
		// Migrate post featured image(s).
		$this->post_featured_image( $post );

		WP_CLI::success( "Post {$post->ID} content imported." );
	}
}
