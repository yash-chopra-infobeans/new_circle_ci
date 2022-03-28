<?php

namespace IDG\Migration\Images;

use WP_CLI;

// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

/**
 * Migrate image CLI class.
 */
class CLI {
	public $type = 'attachment';

	public $post_type = 'post';

	/**
	 * Amount of posts to migrate.
	 *
	 * @var integer
	 */
	public $amount = 0;

	/**
	 * Offset.
	 *
	 * @var integer
	 */
	public $offset = 0;

	/**
	 * Array of post IDs to migrate.
	 *
	 * @var array
	 */
	public $include = [];

	public $taxonomy = 'category';

	public $publish = false;

	public $publications = [];

	/**
	 * Invoke the CLI command, first setting the command
	 * args and then going through the process.
	 *
	 * @param array $args WPCLI args.
	 * @param array $assoc_args WPCLI options.
	 * @return void
	 */
	public function __invoke( $args = [], $assoc_args = [] ) {
		$this->create_args( $assoc_args );

		switch ( $this->type ) {
			case 'content':
				Content::instance( $this )->migrate();
				break;
			case 'db':
				DB::instance( $this )->migrate();
				break;
			case 'featured_image':
				Featured_Image::instance( $this )->migrate();
				break;
			case 'users':
				Users::instance( $this )->migrate();
				break;
			case 'taxonomy':
				Taxonomy::instance( $this )->migrate();
				break;
			default:
				break;
		}
	}

	/**
	 * Create all the args and options from those
	 * passed through the CLI.
	 *
	 * @param array $assoc_args WPCLI options.
	 * @return void
	 */
	private function create_args( array $assoc_args ) {
		$this->type = $assoc_args['type'];

		if ( isset( $assoc_args['post_type'] ) ) {
			$this->post_type = $assoc_args['post_type'];
		}

		if ( isset( $assoc_args['taxonomy'] ) ) {
			$this->taxonomy = $assoc_args['taxonomy'];
		}

		if ( isset( $assoc_args['amount'] ) ) {
			$this->amount = $assoc_args['amount'];
			$this->amount = $this->amount > 0 ? $this->amount : 1;
		}

		if ( isset( $assoc_args['offset'] ) ) {
			$this->offset = $assoc_args['offset'];
		}

		if ( isset( $assoc_args['include'] ) ) {
			$this->include = array_map( 'intval', explode( ',', $assoc_args['include'] ) );
			$this->offset  = 0;
			$this->amount  = count( $this->include );
		}

		if ( isset( $assoc_args['publish'] ) ) {
			$this->publish = true;
		}

		if ( isset( $assoc_args['publications'] ) ) {
			$this->publications = array_map( 'intval', explode( ',', $assoc_args['publications'] ) );
		}
	}
}
