<?php

namespace IDG\Publishing_Flow;

use IDG\Publishing_Flow\Data\Authors as Authors_Data;
use IDG\Publishing_Flow\Deploy\Author as Deploy_Author;

/**
 * Handles the authors, or users, creation and profile updating.
 */
class Authors {
	const HOOK_DEPLOY_AUTHOR = 'idg_publishing_flow_deploy_author';

	/**
	 * Instantiates the class and registers the required hooks.
	 */
	public function __construct() {
		add_action( 'user_register', [ $this, 'deploy_author' ] );
		add_action( 'profile_update', [ $this, 'deploy_author' ] );
	}

	/**
	 * Runs the deploy process when the author is created or updated.
	 *
	 * @param int $author_id The author ID.
	 * @return void
	 */
	public function deploy_author( $author_id ) {
		$should_deploy = apply_filters( self::HOOK_DEPLOY_AUTHOR, true, $author_id );

		if ( ! $should_deploy ) {
			return;
		}

		$publications = idg_get_publications( true );

		foreach ( $publications as $publication ) {
			if ( ! $publication['term'] || ! $publication['isActive'] ) {
				continue;
			}

			$publication_id = $publication['term']->term_id;
			$author         = Authors_Data::instance()->format( $author_id );

			$deploy_author = new Deploy_Author( $author[0], intval( $publication_id ) );
			$deploy_author->create_or_update();
		}
	}
}
