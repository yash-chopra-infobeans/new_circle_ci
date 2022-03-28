<?php
if ( ! function_exists( 'idg_role_administrator_capabilities' ) ) {
	/**
	 * `administrator` role capabilities.
	 * Only includes capability differences to standard WordPress `administrator` role.
	 *
	 * @see https://wordpress.org/support/article/roles-and-capabilities/#capability-vs-role-table
	 */
	function idg_role_administrator_capabilities() {
		$role = get_role( 'administrator' );

		if ( ! $role ) {
			return;
		}

		$role->add_cap( 'read' );

		/**
		 * Publishing/Editorial.
		 */
		$role->add_cap( 'read_private_posts' );
		$role->add_cap( 'edit_posts' );
		$role->add_cap( 'edit_private_posts' );
		$role->add_cap( 'edit_others_posts' );
		$role->add_cap( 'edit_published_posts' );
		$role->add_cap( 'publish_posts' );
		$role->add_cap( 'delete_posts' );
		$role->add_cap( 'delete_private_posts' );
		$role->add_cap( 'delete_others_posts' );
		$role->add_cap( 'delete_published_posts' );

		/**
		 * Theme.
		 */
		$role->add_cap( 'read_private_pages' );
		$role->add_cap( 'edit_pages' );
		$role->add_cap( 'edit_private_pages' );
		$role->add_cap( 'edit_others_pages' );
		$role->add_cap( 'edit_published_pages' );
		$role->add_cap( 'publish_pages' );
		$role->add_cap( 'delete_pages' );
		$role->add_cap( 'delete_private_pages' );
		$role->add_cap( 'delete_others_pages' );
		$role->add_cap( 'delete_published_pages' );

		/**
		 * Users.
		 */
		$role->add_cap( 'list_users' );
		$role->add_cap( 'edit_users' );
		$role->add_cap( 'create_users' );
		$role->add_cap( 'delete_users' );
		$role->add_cap( 'promote_users' );

		/**
		 * Image/Media Manager.
		 */
		$role->add_cap( 'upload_files' );
		$role->remove_cap( 'edit_files' );

		/**
		 * Plugins.
		 */
		$role->add_cap( 'edit_plugins' );
		$role->add_cap( 'upload_plugins' );
		$role->add_cap( 'install_plugins' );
		$role->add_cap( 'activate_plugins' );
		$role->add_cap( 'update_plugins' );
		$role->add_cap( 'delete_plugins' );

		/**
		 * WordPress Themes.
		 */
		$role->add_cap( 'edit_themes' );
		$role->add_cap( 'upload_themes' );
		$role->add_cap( 'install_themes' );
		$role->add_cap( 'update_themes' );
		$role->add_cap( 'delete_themes' );
		$role->add_cap( 'switch_themes' );

		/**
		 * WordPress Core.
		 */
		$role->add_cap( 'update_core' );
		$role->add_cap( 'setup_network' );
		$role->add_cap( 'manage_options' );
		$role->add_cap( 'edit_theme_options' );
		$role->add_cap( 'manage_categories' );

		/**
		 * Misc.
		 */
		$role->add_cap( 'unfiltered_html' );
		$role->add_cap( 'customize' );
		$role->add_cap( 'edit_dashboard' );
		$role->add_cap( 'import' );
		$role->add_cap( 'export' );
		$role->add_cap( 'manage_links' );
		$role->add_cap( 'moderate_comments' );

		/**
		 * Taxonomies.
		 */
		$role->add_cap( 'manage_tags' );
		$role->add_cap( 'edit_tags' );
		$role->add_cap( 'delete_tags' );
		$role->add_cap( 'assign_tags' );

		$role->add_cap( 'manage_story_types' );
		$role->add_cap( 'edit_story_types' );
		$role->add_cap( 'delete_story_types' );
		$role->add_cap( 'assign_story_types' );

		$role->add_cap( 'manage_sponsorships' );
		$role->add_cap( 'edit_sponsorships' );
		$role->add_cap( 'delete_sponsorships' );
		$role->add_cap( 'assign_sponsorships' );

		$role->add_cap( 'manage_blogs' );
		$role->add_cap( 'edit_blogs' );
		$role->add_cap( 'delete_blogs' );
		$role->add_cap( 'assign_blogs' );

		$role->add_cap( 'manage_podcast_series' );
		$role->add_cap( 'edit_podcast_series' );
		$role->add_cap( 'delete_podcast_series' );
		$role->add_cap( 'assign_podcast_series' );

		$role->add_cap( 'manage_asset_tag' );
		$role->add_cap( 'edit_asset_tag' );
		$role->add_cap( 'delete_asset_tag' );
		$role->add_cap( 'assign_asset_tag' );

		$role->add_cap( 'manage_asset_image_rights' );
		$role->add_cap( 'edit_asset_image_rights' );
		$role->add_cap( 'delete_asset_image_rights' );
		$role->add_cap( 'assign_asset_image_rights' );

		$role->add_cap( 'manage_vendor_code' );
		$role->add_cap( 'edit_vendor_code' );
		$role->add_cap( 'delete_vendor_code' );
		$role->add_cap( 'assign_vendor_code' );

		$role->add_cap( 'manage_manufacturer' );
		$role->add_cap( 'edit_manufacturer' );
		$role->add_cap( 'delete_manufacturer' );
		$role->add_cap( 'assign_manufacturer' );

		$role->add_cap( 'manage_origin' );
		$role->add_cap( 'edit_origin' );
		$role->add_cap( 'delete_origin' );
		$role->add_cap( 'assign_origin' );

		$role->add_cap( 'manage_territory' );
		$role->add_cap( 'edit_territory' );
		$role->add_cap( 'delete_territory' );
		$role->add_cap( 'assign_territory' );

		$role->add_cap( 'manage_article_type' );
		$role->add_cap( 'edit_article_type' );
		$role->add_cap( 'delete_article_type' );
		$role->add_cap( 'assign_article_type' );
	}
}
add_action( 'after_switch_theme', 'idg_role_administrator_capabilities_child' );
