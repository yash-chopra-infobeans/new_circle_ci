<?php

namespace IDG\Publishing_Flow;

/**
 * Management of some embargo related features.
 */
class Embargo {
	/**
	 * Filter name for checking whether the post has a
	 * publication assigned.
	 */
	const FILTER_EMBARGO_HAS_PUBLICATION = 'idg_publishing_flow_check_embargo_has_publication';

	/**
	 * Initialise the class and attach the required hooks.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'role_capabilities' ], 10 );
		add_action( 'admin_init', [ $this, 'prevent_edit' ], 10 );

		add_filter( 'user_has_cap', [ $this, 'remove_edit_post_role' ], 10, 4 );
		add_filter( 'the_title', [ $this, 'replace_post_list_title' ], 10, 2 );
		add_filter( 'post_row_actions', [ $this, 'remove_post_row_actions' ], 10, 2 );
	}

	/**
	 * Adds roles for ability to edit an embargoed article.
	 *
	 * @TODO: Needs cleaning up and running through correct
	 * role creation processes.
	 * @return void
	 */
	public function role_capabilities() {
		$admin = get_role( 'administrator' );

		if ( $admin ) {
			$admin->add_cap( 'edit_embargoed_posts', true );
		}
	}

	/**
	 * Prevents the edit of any articles that are under embargo
	 * when the url for them is directly visited.
	 *
	 * @return void
	 */
	public function prevent_edit() {
		global $pagenow, $typenow;

		// Ensure we're on the post edit page.
		if ( ! in_array( $pagenow, [ 'post.php' ], true ) && 'post' !== $typenow ) {
			return;
		}

		// Ensure we're actually editing a post.
		$post_id = filter_input( INPUT_GET, 'post', FILTER_VALIDATE_INT );
		if ( ! isset( $post_id ) ) {
			return;
		}

		if ( self::can_view_post( $post_id ) ) {
			return;
		}

		wp_die( esc_html( __( 'You do not have the required role permissions to edit this article.' ) ) );
	}

	/**
	 * Removes the edit_post capability if the user does not meet
	 * the requirements laid out in Embargo::can_view_post when checking
	 * if the user has the capability when requested. If not,
	 * the user is stripped of the edit_post capability in this singular
	 * instance and no other, allowing users to view other posts.
	 *
	 * @param array    $all_capabilities Array of key/value pairs where keys represent a
	 *                 capability name and boolean values represent whether the user has that capability.
	 * @param array    $capabilities Required primitive capabilities for the requested capability.
	 * @param array    $args Arguments that accompany the requested capability check.
	 * @param \WP_User $user The user object.
	 * @return array
	 */
	public function remove_edit_post_role( $all_capabilities, $capabilities, $args, \WP_User $user ) : array {
		if ( 'edit_post' !== $args[0] ) {
			return $all_capabilities;
		}

		/**
		 * Check that we're getting capabilites for a specific post.
		 * This will be a post ID.
		 */
		if ( ! isset( $args[2] ) ) {
			return $all_capabilities;
		}

		if ( self::can_view_post( $args[2] ) ) {
			return $all_capabilities;
		}

		unset( $all_capabilities['edit_post'] );

		return $all_capabilities;
	}

	/**
	 * Replace the title in the admin post list when the
	 * article is embargoed and the user does not meet
	 * conditions to view the article.
	 *
	 * @param string $title The current post title.
	 * @param int    $post_id The current post id.
	 * @return string The updated title.
	 */
	public function replace_post_list_title( $title, $post_id ) : string {
		if ( ! Sites::is_origin() ) {
			return $title;
		}

		if ( ! is_admin() ) {
			return $title;
		}

		if ( self::can_view_post( $post_id ) ) {
			return $title;
		}

		return __( '[Embargoed Article]' );
	}

	/**
	 * Removes all row actions in the admin post listing
	 * when the user does not meet conditions to view the
	 * article. This will prevent them from Editing, trashing,
	 * etc the post.
	 *
	 * @param array    $actions The current list of actions that can be taken.
	 * @param \WP_Post $post The current post.
	 * @return array
	 */
	public function remove_post_row_actions( $actions, \WP_Post $post ) : array {
		if ( self::can_view_post( $post->ID ) ) {
			return $actions;
		}

		return [];
	}

	/**
	 * Checks whether the current user can view or edit
	 * the passed post based on it's embargo status.
	 *
	 * @param int $post_id The requested post id to check.
	 * @return boolean Whether the user can view the post.
	 */
	public static function can_view_post( $post_id ) : bool {
		/**
		 * Get the embargo meta and bail if it doesn't exist.
		 * We know it's not an embargoed post here.
		 */
		$embargo_meta = get_post_meta( $post_id, Loader::META_POST_EMBARGO_DATE, true );
		if ( empty( $embargo_meta ) ) {
			return true;
		}

		/**
		 * Check that the post has a publication assigned to it.
		 */
		$current_user_business_unit = get_user_meta( get_current_user_id(), 'assigned_business_units', true );
		$post_publication           = Sites::get_post_publication( $post_id );
		/**
		 * Filter name for checking whether the post has a
		 * publication assigned.
		 *
		 * This should be used for stating whether a post should have
		 * publication assigned before going further into the embargo
		 * process. If check is required and a post is embargoed,
		 * the post will still be accessible by all.
		 *
		 * @param bool $check Whether to check for a publication.
		 */
		$check_publication = apply_filters( self::FILTER_EMBARGO_HAS_PUBLICATION, false );
		if ( $check_publication && empty( $post_publication ) ) {
			return true;
		}

		/**
		 * Get the Business Unit that the post Publication is assigned to
		 * and check it against the users list of assigned BUs.
		 */
		$business_unit_term = get_term( $post_publication->parent, Sites::TAXONOMY );
		if ( isset( $business_unit_term->term_id ) && in_array( $business_unit_term->term_id, $current_user_business_unit, true ) ) {
			return true;
		}

		/**
		 * Check the post authors Business Units in comparison
		 * to the current users BUs, if they have mutual BUs,
		 * let them through to edit the post.
		 */
		$post                 = get_post( $post_id );
		$author_business_unit = get_user_meta( $post->post_author, User_Profiles::USER_META_ASSIGNED, true );

		$shared_units = array_filter(
			$author_business_unit,
			function( $value ) use ( $current_user_business_unit ) {
				return in_array( $value, $current_user_business_unit, true );
			}
		);

		if ( ! is_null( $shared_units ) && count( $shared_units ) > 0 ) {
			return true;
		}

		// You may pass.
		if ( current_user_can( 'edit_embargoed_posts' ) ) {
			return true;
		}

		return false;
	}
}
