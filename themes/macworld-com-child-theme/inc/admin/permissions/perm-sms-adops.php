<?php
if ( ! function_exists( 'idg_role_author_capabilities' ) ) {
	/**
	 * `sms_adops` role capabilities.
	 * Only includes capability differences to idg-base-theme `sms_adops` role.
	 */
	function idg_role_author_capabilities() {
		$role = get_role( 'sms_adops' );

		if ( ! $role ) {
			return;
		}

		$role->add_cap( 'read' );

		/**
		 * Publishing/Editorial.
		 */
		$role->remove_cap( 'read_private_posts' );
		$role->remove_cap( 'edit_posts' );
		$role->remove_cap( 'edit_private_posts' );
		$role->remove_cap( 'edit_others_posts' );
		$role->remove_cap( 'edit_published_posts' );
		$role->remove_cap( 'publish_posts' );
		$role->remove_cap( 'delete_posts' );
		$role->remove_cap( 'delete_private_posts' );
		$role->remove_cap( 'delete_others_posts' );
		$role->remove_cap( 'delete_published_posts' );

		/**
		 * Theme.
		 */
		$role->remove_cap( 'read_private_pages' );
		$role->remove_cap( 'edit_pages' );
		$role->remove_cap( 'edit_private_pages' );
		$role->remove_cap( 'edit_others_pages' );
		$role->remove_cap( 'edit_published_pages' );
		$role->remove_cap( 'publish_pages' );
		$role->remove_cap( 'delete_pages' );
		$role->remove_cap( 'delete_private_pages' );
		$role->remove_cap( 'delete_others_pages' );
		$role->remove_cap( 'delete_published_pages' );

		/**
		 * Users.
		 */
		$role->remove_cap( 'list_users' );
		$role->remove_cap( 'edit_users' );
		$role->remove_cap( 'create_users' );
		$role->remove_cap( 'delete_users' );
		$role->remove_cap( 'promote_users' );

		/**
		 * Image/Media Manager.
		 */
		$role->remove_cap( 'upload_files' );
		$role->remove_cap( 'edit_files' );

		/**
		 * Plugins.
		 */
		$role->remove_cap( 'edit_plugins' );
		$role->remove_cap( 'upload_plugins' );
		$role->remove_cap( 'install_plugins' );
		$role->remove_cap( 'activate_plugins' );
		$role->remove_cap( 'update_plugins' );
		$role->remove_cap( 'delete_plugins' );

		/**
		 * WordPress Themes.
		 */
		$role->remove_cap( 'edit_themes' );
		$role->remove_cap( 'upload_themes' );
		$role->remove_cap( 'install_themes' );
		$role->remove_cap( 'update_themes' );
		$role->remove_cap( 'delete_themes' );
		$role->remove_cap( 'switch_themes' );

		/**
		 * WordPress Core.
		 */
		$role->remove_cap( 'update_core' );
		$role->remove_cap( 'setup_network' );
		$role->add_cap( 'manage_options' );
		$role->remove_cap( 'edit_theme_options' );
		$role->remove_cap( 'manage_categories' );

		/**
		 * Misc.
		 */
		$role->remove_cap( 'unfiltered_html' );
		$role->remove_cap( 'customize' );
		$role->remove_cap( 'edit_dashboard' );
		$role->remove_cap( 'import' );
		$role->remove_cap( 'export' );
		$role->remove_cap( 'manage_links' );
		$role->remove_cap( 'moderate_comments' );

		/**
		 * Taxonomies.
		 */
		$role->remove_cap( 'manage_tags' );
		$role->remove_cap( 'edit_tags' );
		$role->remove_cap( 'delete_tags' );
		$role->remove_cap( 'assign_tags' );

		$role->remove_cap( 'manage_story_types' );
		$role->remove_cap( 'edit_story_types' );
		$role->remove_cap( 'delete_story_types' );
		$role->remove_cap( 'assign_story_types' );

		$role->remove_cap( 'manage_sponsorships' );
		$role->remove_cap( 'edit_sponsorships' );
		$role->remove_cap( 'delete_sponsorships' );
		$role->remove_cap( 'assign_sponsorships' );

		$role->remove_cap( 'manage_blogs' );
		$role->remove_cap( 'edit_blogs' );
		$role->remove_cap( 'delete_blogs' );
		$role->remove_cap( 'assign_blogs' );

		$role->remove_cap( 'manage_podcast_series' );
		$role->remove_cap( 'edit_podcast_series' );
		$role->remove_cap( 'delete_podcast_series' );
		$role->remove_cap( 'assign_podcast_series' );

		$role->remove_cap( 'manage_asset_tag' );
		$role->remove_cap( 'edit_asset_tag' );
		$role->remove_cap( 'delete_asset_tag' );
		$role->remove_cap( 'assign_asset_tag' );

		$role->remove_cap( 'manage_asset_image_rights' );
		$role->remove_cap( 'edit_asset_image_rights' );
		$role->remove_cap( 'delete_asset_image_rights' );
		$role->remove_cap( 'assign_asset_image_rights' );

		$role->remove_cap( 'manage_vendor_code' );
		$role->remove_cap( 'edit_vendor_code' );
		$role->remove_cap( 'delete_vendor_code' );
		$role->remove_cap( 'assign_vendor_code' );

		$role->remove_cap( 'manage_manufacturer' );
		$role->remove_cap( 'edit_manufacturer' );
		$role->remove_cap( 'delete_manufacturer' );
		$role->remove_cap( 'assign_manufacturer' );

		$role->remove_cap( 'manage_origin' );
		$role->remove_cap( 'edit_origin' );
		$role->remove_cap( 'delete_origin' );
		$role->remove_cap( 'assign_origin' );

		$role->remove_cap( 'manage_territory' );
		$role->remove_cap( 'edit_territory' );
		$role->remove_cap( 'delete_territory' );
		$role->remove_cap( 'assign_territory' );

		$role->remove_cap( 'manage_article_type' );
		$role->remove_cap( 'edit_article_type' );
		$role->remove_cap( 'delete_article_type' );
		$role->remove_cap( 'assign_article_type' );
	}
}
add_action( 'after_switch_theme', 'idg_role_sms_adops_capabilities' );
