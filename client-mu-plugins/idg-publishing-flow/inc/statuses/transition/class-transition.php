<?php
/**
 * When an status transitions we have to run through a couple
 * checks before passing it to the correct handler.
 *
 * @package idg-publishing-flow
 */

namespace IDG\Publishing_Flow\Statuses\Transition;

use IDG\Publishing_Flow\Sites;
use WP_REST_Request;

trait Transition {
	/**
	 * When an status transitions we have to run through a couple
	 * checks before passing it to the correct handler. This includes
	 * not handling autosaves, non-post posttypes and checking that,
	 * if defined, that preconditions are met. Once they pass, we
	 * then register the correct handlers for the transition.
	 * Executed by `transition_post_status` hook.
	 *
	 * @param string   $new_status The status transitioning to.
	 * @param string   $old_status The status being transitioned from.
	 * @param \WP_Post $post The post undergoing status transition.
	 * @return void
	 * @SuppressWarnings(PHPMD)
	 */
	public function on_transition( $new_status, $old_status, $post ) : void {
		if ( ! Sites::is_origin() ) {
			return;
		}

		if ( $this->is_autosave() ) {
			return;
		}

		$allowed_types = [ 'post' ];

		if ( ! in_array( $post->post_type, $allowed_types, true ) ) {
			return;
		}

		if ( 'new' === $old_status ) {
			return;
		}

		if ( method_exists( $this, 'pre_conditions' ) && ! $this->pre_conditions() ) {
			return;
		}

		$this->new_status = $new_status;
		$this->old_status = $old_status;

		// Status hasn't changed.
		if (
			(
			( $this->name === $new_status )
			|| ( method_exists( $this, 'should_transition_to' ) && $this->should_transition_to( $old_status, $new_status, $post ) ) ) && ( apply_filters( 'idg_filter_handle_transition_to_' . $new_status, true, $old_status ) )
		) {
			global $wp_actions;
			do_action( 'idg_handle_transition_to_' . $new_status, $old_status, $post );

			if ( defined( 'REST_REQUEST' ) ) {
				add_action( 'rest_after_insert_post', [ $this, 'handle_transition_to' ], 10, 3 );
			} elseif ( defined( 'DOING_CRON' ) || did_action( 'wp_trash_post' ) ) {
				$this->handle_transition_to( $post, new WP_REST_Request() );
			}
		}

		if (
			(
			( $this->name === $old_status && $new_status !== $old_status )
			|| ( method_exists( $this, 'should_transition_from' ) && $this->should_transition_from( $old_status, $new_status, $post ) ) ) && ( apply_filters( 'idg_filter_handle_transition_from_' . $old_status, true, $new_status ) )
		) {
			do_action( 'idg_handle_transition_from_' . $old_status, $new_status, $post );
			add_action( 'rest_after_insert_post', [ $this, 'handle_transition_from' ], 10, 3 );
		}
	}

	/**
	 * Check whether the current action is from
	 * undergoing an autosave.
	 *
	 * @return boolean
	 */
	protected function is_autosave() : bool {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return true;
		}

		return false;
	}
}
