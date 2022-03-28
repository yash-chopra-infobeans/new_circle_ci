<?php
/**
 * Assigns `managing_editor` role capabilities
 */
add_filter(
	'idg_role_managing_editor_capabilities',
	function ( $capabilities = [] ) {
		$capabilities = [
			'read'                      => true,

			/**
			 * Publishing/Editorial.
			 */
			'read_private_posts'        => false,
			'edit_posts'                => true,
			'edit_private_posts'        => false,
			'edit_others_posts'         => true,
			'edit_published_posts'      => true,
			'publish_posts'             => true,
			'delete_posts'              => true,
			'delete_private_posts'      => false,
			'delete_others_posts'       => true,
			'delete_published_posts'    => true,

			/**
			 * Theme.
			 */
			'read_private_pages'        => false,
			'edit_pages'                => false,
			'edit_private_pages'        => false,
			'edit_others_pages'         => false,
			'edit_published_pages'      => false,
			'publish_pages'             => false,
			'delete_pages'              => false,
			'delete_private_pages'      => false,
			'delete_others_pages'       => false,
			'delete_published_pages'    => false,

			/**
			 * Users.
			 */
			'list_users'                => false,
			'edit_users'                => false,
			'create_users'              => false,
			'delete_users'              => false,
			'promote_users'             => false,

			/**
			 * Image/Media Manager.
			 */
			'upload_files'              => true,
			'edit_files'                => true,

			/**
			 * Plugins.
			 */
			'edit_plugins'              => false,
			'upload_plugins'            => false,
			'install_plugins'           => false,
			'activate_plugins'          => false,
			'update_plugins'            => false,
			'delete_plugins'            => false,

			/**
			 * WordPress Themes.
			 */
			'edit_themes'               => false,
			'upload_themes'             => false,
			'install_themes'            => false,
			'update_themes'             => false,
			'delete_themes'             => false,
			'switch_themes'             => false,

			/**
			 * WordPress Core.
			 */
			'update_core'               => false,
			'setup_network'             => false,
			'manage_options'            => false,
			'edit_theme_options'        => false,
			'manage_categories'         => true,

			/**
			 * Misc.
			 */
			'unfiltered_html'           => false,
			'customize'                 => false,
			'edit_dashboard'            => false,
			'import'                    => false,
			'export'                    => false,
			'manage_links'              => false,
			'moderate_comments'         => false,

			/**
			 * Taxonomies.
			 */
			'manage_tags'               => true,
			'edit_tags'                 => true,
			'delete_tags'               => true,
			'assign_tags'               => true,

			'manage_story_types'        => true,
			'edit_story_types'          => true,
			'delete_story_types'        => false,
			'assign_story_types'        => true,

			'manage_sponsorships'       => true,
			'edit_sponsorships'         => true,
			'delete_sponsorships'       => true,
			'assign_sponsorships'       => true,

			'manage_blogs'              => true,
			'edit_blogs'                => true,
			'delete_blogs'              => false,
			'assign_blogs'              => true,

			'manage_podcast_series'     => true,
			'edit_podcast_series'       => true,
			'delete_podcast_series'     => false,
			'assign_podcast_series'     => true,

			'manage_asset_tag'          => true,
			'edit_asset_tag'            => true,
			'delete_asset_tag'          => true,
			'assign_asset_tag'          => true,

			'manage_asset_image_rights' => true,
			'edit_asset_image_rights'   => true,
			'delete_asset_image_rights' => false,
			'assign_asset_image_rights' => true,

			'manage_vendor_code'        => true,
			'edit_vendor_code'          => true,
			'delete_vendor_code'        => false,
			'assign_vendor_code'        => true,

			'manage_manufacturer'       => true,
			'edit_manufacturer'         => true,
			'delete_manufacturer'       => false,
			'assign_manufacturer'       => true,

			'manage_origin'             => true,
			'edit_origin'               => true,
			'delete_origin'             => false,
			'assign_origin'             => true,

			'manage_territory'          => true,
			'edit_territory'            => true,
			'delete_territory'          => false,
			'assign_territory'          => true,

			'manage_article_type'       => false,
			'edit_article_type'         => false,
			'delete_article_type'       => false,
			'assign_article_type'       => true,
		];

		return $capabilities;
	}
);
