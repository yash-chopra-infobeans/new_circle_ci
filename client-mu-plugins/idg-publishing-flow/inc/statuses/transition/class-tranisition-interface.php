<?php
/**
 * Hook will always expect all arguments for handle_transition
 * methods, so ignore the following:
 * @phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
 */

namespace IDG\Publishing_Flow\Statuses\Transition;

/**
 * Interface to defining required methods in a Transition class.
 */
interface Transition_Interface {
	/**
	 * Execute when post transitions from one status to another.
	 * Is triggered by `transition_post_status` in Transition::class.
	 *
	 * @param string $new_status The status transitioning to.
	 * @param string $old_status The status transitioning from.
	 * @param mixed  $post The post object that is transitioning.
	 * @return void
	 */
	public function on_transition( string $new_status, $old_status, $post );

	/**
	 * Executes when a post transitions to a specified status.
	 * Is triggered by `rest_after_insert_post` in Transition::class.
	 *
	 * @param \WP_Post         $post The post object.
	 * @param \WP_REST_Request $request The REST request object.
	 * @param boolean          $creating If the post is new.
	 * @return void
	 */
	public function handle_transition_to( \WP_Post $post, \WP_REST_Request $request, bool $creating = false );

	/**
	 * Executes when a post transitions from a specified status.
	 * Is triggered by `rest_after_insert_post` in Transition::class.
	 *
	 * @param \WP_Post         $post The post object.
	 * @param \WP_REST_Request $request The REST request object.
	 * @param boolean          $creating If the post is new.
	 * @return void
	 */
	public function handle_transition_from( \WP_Post $post, \WP_REST_Request $request, bool $creating = false );
}
