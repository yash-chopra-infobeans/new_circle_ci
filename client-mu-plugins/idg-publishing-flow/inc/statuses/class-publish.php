<?php
/**
 * Hook will always expect all arguments for handle_transition
 * menthods, so ignore the following:
 * @phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
 */

namespace IDG\Publishing_Flow\Statuses;

use IDG\Publishing_Flow\Cache;
use IDG\Publishing_Flow\Loader;
use IDG\Publishing_Flow\Deploy\Article as Deploy_Article;
use IDG\Publishing_Flow\Sites;
use IDG\Publishing_Flow\Statuses\Status;
use IDG\Publishing_Flow\Statuses\Transition\Transition;
use IDG\Publishing_Flow\Statuses\Transition\Transition_Interface;
use WP_REST_Request;

/**
 * Handles any status changes registered as "On Hold".
 *
 * @inheritDoc
 */
class Publish extends Status implements Transition_Interface {
	use Transition;

	/**
	 * A nice reference name for the state.
	 *
	 * @var string
	 */
	public $name = 'publish';

	/**
	 * The label of the status.
	 *
	 * @var string
	 */
	public $label = 'Publish';

	/**
	 * A list of ids from published versions of an article.
	 *
	 * @var array
	 */
	private $published_ids = [];

	/**
	 * The current post undergoing status transition.
	 *
	 * @var array
	 */
	private $post = [];

	/**
	 * The disable state for when the class is parsed
	 * for HTML <option> creation.
	 *
	 * @var boolean
	 */
	public $option_disable = true;

	/**
	 * The pre-conditions as to whether the transition should be handled
	 * in all cases; both to and from.
	 *
	 * In the case of publish pre-conditions, we do not want to run through
	 * any publishing related processes if the post is ***only being saved.***
	 *
	 * @return boolean
	 */
	public function pre_conditions() {
		if ( wp_doing_cron() ) {
			return true;
		}

		if ( Cache::get_action() === 'save' ) {
			return false;
		}

		return true;
	}

	/**
	 * Check whether the "transition from" process should run
	 *
	 * @param string  $new_status The new/updated status.
	 * @param string  $old_status The old status.
	 * @param WP_Post $updated_post The post object.
	 * @return boolean
	 */
	public function should_transition_from( $new_status, $old_status, $updated_post ) : bool {
		if ( Cache::get_action() === 'unpublish' ) {
			return true;
		}

		return false;
	}

	/**
	 * Handles when a post transitions to published.
	 * Will check against a revision list to ensure the state
	 * for those remains.
	 * Before finalising the state transition, publication will be
	 * check to ensure it is actually registered based on the REST
	 * Request data that is sent to the handler. Once validated, the
	 * post will be deployed and the returned data will be stored against
	 * the transitioned post.
	 *
	 * @param \WP_Post         $post The post object.
	 * @param \WP_REST_Request $request The REST request object.
	 * @param boolean          $creating If the post is new.
	 * @throws \Error Throws an error when the response or request fails.
	 * @return void
	 */
	public function handle_transition_to( \WP_Post $post, \WP_REST_Request $request, bool $creating = false ) : void {
		$this->post = $post;

		$publication_id = Sites::get_post_publication( $this->post->ID )->term_id;

		idg_set_error_report_meta(
			[
				'publication' => [
					'id' => $publication_id,
				],
			]
		);

		if ( ! Sites::is_registered( $publication_id ) ) {
			return;
		}

		$this->published_ids = Cache::get_meta( Loader::META_POST_PUBLISHED_IDS, $this->post->ID ) ?: [];

		idg_set_error_report_meta(
			[
				'article' => [
					'published_ids' => $this->published_ids,
				],
			]
		);

		$deploy_article = new Deploy_Article( $this->post, $publication_id );

		$published_key = null;

		if ( is_in_array( $publication_id, $this->published_ids, 'source' ) ) {
			$published_key = find_in_array( $publication_id, $this->published_ids, 'source' );
		}

		if ( isset( $this->published_ids[ $published_key ]['id'] ) && null !== $this->published_ids[ $published_key ]['id'] ) {
			$deploy_response = $deploy_article->update( $this->published_ids[ $published_key ]['id'] );
		} else {
			$deploy_response = $deploy_article->create();
		}

		$response_body = $deploy_response->get_data();

		if ( $deploy_response->failed() || empty( $response_body ) ) {
			idg_set_error_report_meta(
				[
					'deploy_response' => $response_body,
				]
			);

			wp_update_post(
				[
					'ID'          => $this->post->ID,
					'post_status' => 'draft',
				]
			);

			$this->response_body = $response_body;

			add_filter( 'rest_pre_echo_response', [ $this, 'add_rest_error_data' ] );
			return;
		}

		$this->attach_publish_data( $publication_id, $response_body->data, 'publish' );
	}

	/**
	 * Adds the error data into the REST response of the
	 * core WordPress posts endpoint.
	 *
	 * @param array $response The current response.
	 * @return array
	 */
	public function add_rest_error_data( array $response ) : array {
		if ( ! $this->response_body ) {
			$this->response_body = (object) [
				'errors' => [ 'There was a problem.' ],
			];
		}

		$response['status']          = 'draft';
		$response['publishing_flow'] = [
			'errors' => $this->response_body->errors ?: $this->response_body,
		];

		$updated = wp_update_post(
			[
				'ID'          => $response['id'],
				'post_status' => $response['status'],
			]
		);

		return $response;
	}

	/**
	 * Attach all data to the post. This usually occurs after article
	 * deployment using the returned data.
	 *
	 * @param string $source The source of the data.
	 * @param array  $data The array of published data to save.
	 * @param string $status The status that the post is transitioning from.
	 * @return void
	 */
	private function attach_publish_data( string $source, $data, $status = 'publish' ) : void {
		$existing_source = is_in_array( $source, $this->published_ids, 'source' );
		$existing_id     = is_in_array( $data->post_id, $this->published_ids, 'id' );

		if ( $existing_source && $existing_id ) {
			return;
		}

		$published_data = [
			'source'    => $source,
			'id'        => $data->post_id,
			'permalink' => $data->permalink,
		];

		if ( $existing_source ) {
			$source_key = find_in_array( $source, $this->published_ids, 'source' );

			$this->published_ids[ $source_key ] = $published_data;
		} else {
			$this->published_ids[] = $published_data;
		}

		update_post_meta( $this->post->ID, Loader::META_POST_PUBLISHED_IDS, $this->published_ids );
		update_post_meta( $this->post->ID, Loader::META_POST_PUBLISHED_STATUS, $status );
	}

	/**
	 * When a post transitions from published, a request will be sent to
	 * a delivery site to remove it from the site, trashing it.
	 *
	 * @param \WP_Post         $post The post object.
	 * @param \WP_REST_Request $request The REST request object.
	 * @param boolean          $creating If the post is new.
	 * @return void
	 */
	public function handle_transition_from( \WP_Post $post, \WP_REST_Request $request, bool $creating = false ) {
		$this->post = $post;

		$publication_id = Sites::get_post_publication( $this->post->ID )->term_id;

		if ( ! Sites::is_registered( $publication_id ) ) {
			return;
		}

		$this->published_ids = get_post_meta( $this->post->ID, Loader::META_POST_PUBLISHED_IDS, true ) ?: [];

		$deploy_article = new Deploy_Article( $post, $publication_id );

		$deploy_article->delete( $this->published_ids[0]['id'] );

		update_post_meta( $this->post->ID, Loader::META_POST_PUBLISHED_IDS, $this->published_ids );
		/**
		 * The post on a content hub isn't trashed, but we should consider the post
		 * on delivery sites to be trashed.
		 */
		update_post_meta( $this->post->ID, Loader::META_POST_PUBLISHED_STATUS, 'trashed' );

		return;
	}
}
