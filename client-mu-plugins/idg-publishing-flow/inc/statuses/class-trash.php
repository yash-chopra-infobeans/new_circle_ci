<?php
/**
 * Hook will always expect all arguments for handle_transition
 * menthods, so ignore the following:
 * @phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
 *
 * Disable the missing paramtag when expecting inheritence for docblock data.
 * @phpcs:disable Squiz.Commenting.FunctionComment.MissingParamTag
 */

namespace IDG\Publishing_Flow\Statuses;

use IDG\Publishing_Flow\Statuses\Status;
use IDG\Publishing_Flow\Statuses\Transition\Transition;
use IDG\Publishing_Flow\Statuses\Transition\Transition_Interface;

use IDG\Publishing_Flow\Deploy\Article as Deploy_Article;
use IDG\Publishing_Flow\Sites;
use IDG\Publishing_Flow\Loader;

/**
 * Handles any status changes registered as "Ready for Publish".
 *
 * @inheritDoc
 */
class Trash extends Status implements Transition_Interface {
	use Transition;

	/**
	 * A nice reference name for the state.
	 *
	 * @var string
	 */
	public $name = 'trash';

	/**
	 * The label of the status.
	 *
	 * @var string
	 */
	public $label = 'Trash';

	/**
	 * The disable state for when the class is parsed
	 * for HTML <option> creation.
	 *
	 * @var boolean
	 */
	 public $option_disable = true;
	/**
	 * Currently unused.
	 *
	 * @see IDG\Publishing_Flow\Statuses\Transition\Transition_Interface::handle_transition_to()
	 */
	public function handle_transition_to( \WP_Post $post, \WP_REST_Request $request, bool $creating = false ) : void {
		$this->post = $post;

		$publication_id = Sites::get_post_publication( $this->post->ID )->term_id;

		if ( ! Sites::is_registered( $publication_id ) ) {
			return;
		}

		$this->published_ids = get_post_meta( $this->post->ID, Loader::META_POST_PUBLISHED_IDS, true ) ?: [];

		if ( ! $this->published_ids ) {
			return;
		}

		$deploy_article = new Deploy_Article( $post, $publication_id );

		$deploy_article->delete( $this->published_ids[0]['id'] );

		update_post_meta( $this->post->ID, Loader::META_POST_PUBLISHED_IDS, $this->published_ids );
		update_post_meta( $this->post->ID, Loader::META_POST_PUBLISHED_STATUS, 'trashed' );
	}

	/**
	 * Currently unused.
	 *
	 * @see IDG\Publishing_Flow\Statuses\Transition\Transition_Interface::handle_transition_from()
	 */
	public function handle_transition_from( \WP_Post $post, \WP_REST_Request $request, bool $creating = false ) {
	}
}
