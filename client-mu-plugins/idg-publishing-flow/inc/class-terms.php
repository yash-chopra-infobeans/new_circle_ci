<?php

namespace IDG\Publishing_Flow;

use IDG\Publishing_Flow\Deploy\Taxonomy as Deploy_Taxonomy;
use IDG\Publishing_Flow\Data\Taxonomies;

/**
 * Class for handling terms and taxonomies before deployment.
 */
class Terms {
	const HOOK_DEPLOY_TERM = 'idg_publishing_flow_deploy_term';

	const HOOK_LIMIT_PUBLICATIONS = 'idg_publishing_flow_term_limit_publications';


	/**
	 * Initialise the class.
	 */
	public function __construct() {
		add_action( 'saved_term', [ $this, 'deploy_term' ], 10, 4 );
	}

	/**
	 * Set the term deployment when and build the required data.
	 *
	 * @param string|int $term_id The term id.
	 * @param string|int $tt_id The term taxonomy id.
	 * @param string     $taxonomy The taxonomy slug.
	 * @param bool       $updated Whether the action is an update or create.
	 * @return void
	 */
	public function deploy_term( $term_id, $tt_id, $taxonomy, $updated ) : void {
		$should_deploy = apply_filters( self::HOOK_DEPLOY_TERM, true, $taxonomy, $tt_id, $updated );

		if ( ! $should_deploy ) {
			return;
		}

		$publications = idg_get_publications( true );

		$limit_to_pubs = apply_filters( self::HOOK_LIMIT_PUBLICATIONS, false, $publications );

		foreach ( $publications as $publication ) {
			if ( ! $publication['term'] || ! $publication['isActive'] ) {
				continue;
			}

			$publication_id = $publication['term']->term_id;

			$term        = get_term( $term_id, $taxonomy );
			$hook_string = Taxonomies::HOOK_GET_TERM_META_TAXONOMY . $taxonomy;
			$term->meta  = apply_filters( $hook_string, Taxonomies::get_meta_values( $term ), $term );

			// Check whether to update specific publications.
			if (
				$limit_to_pubs
				&& (
					! empty( $term->meta['publication'] )
					&& ! in_array( $publication_id, $term->meta['publication'], true )
				)
			) {
				continue;
			}

			$deploy_taxonomy = new Deploy_Taxonomy( $term, $publication_id );

			if ( $updated ) {
				$deploy_taxonomy->update();
			} else {
				$deploy_taxonomy->create();
			}
		}
	}
}
