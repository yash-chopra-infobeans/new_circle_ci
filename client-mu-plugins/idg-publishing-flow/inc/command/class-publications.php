<?php

namespace IDG\Publishing_Flow\Command;

use WP_CLI;
use IDG\Publishing_Flow\Sites;

/**
 * Publications command for WPCLI.
 */
class Publications {
	/**
	 * Invoke the CLI command to list all Publications.
	 *
	 * @param array $args WPCLI args.
	 * @param array $assoc_args WPCLI options.
	 * @return void
	 */
	public function __invoke( $args, $assoc_args ) {
		$publications = Sites::get_publications();

		$table = [];

		foreach ( $publications as $publication ) {
			$table[] = [
				'name'          => $publication->name,
				'id'            => $publication->term_id,
				'business_unit' => $publication->parent,
			];
		}

		WP_CLI\Utils\format_items( 'table', $table, [ 'name', 'id', 'business_unit' ] );
	}
}
