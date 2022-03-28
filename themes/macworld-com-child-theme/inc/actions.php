<?php

if ( ! function_exists( 'idg_child_theme_remove_admin_menu_items' ) ) {
	/**
	 * Removes custom post types & all taxonomies from the admin menu.
	 */
	function idg_child_theme_remove_admin_menu_items() {
		remove_menu_page( 'asset-manager' );
		remove_menu_page( 'edit.php?post_type=product' );
		remove_menu_page( 'edit.php?post_type=sponsored_link' );

		remove_submenu_page( 'edit.php', 'post-new.php' );
		remove_submenu_page( 'edit.php', 'edit-tags.php?taxonomy=category' );
		remove_submenu_page( 'edit.php', 'edit-tags.php?taxonomy=post_tag' );
		remove_submenu_page( 'edit.php', 'edit-tags.php?taxonomy=territory' );
		remove_submenu_page( 'edit.php', 'edit-tags.php?taxonomy=publication' );
		remove_submenu_page( 'edit.php', 'edit-tags.php?taxonomy=story_types' );
		remove_submenu_page( 'edit.php', 'edit-tags.php?taxonomy=article_type' );
		remove_submenu_page( 'edit.php', 'edit-tags.php?taxonomy=sponsorships' );
		remove_submenu_page( 'edit.php', 'edit-tags.php?taxonomy=blogs' );
		remove_submenu_page( 'edit.php', 'edit-tags.php?taxonomy=podcast_series' );

		/**
		 * Hiding role specific menu items.
		 */
		$user = wp_get_current_user();

		/**
		 * Removes settings only admins should be able to access.
		 *
		 * This works as a failsafe if the `options-general.php` page is enabled for
		 * access to other settings.
		 */
		if ( ! in_array( 'administrator', (array) $user->roles, true ) ) {
			remove_submenu_page( 'options-general.php', 'options-general.php' );
			remove_submenu_page( 'options-general.php', 'options-writing.php' );
			remove_submenu_page( 'options-general.php', 'options-reading.php' );
			remove_submenu_page( 'options-general.php', 'options-discussion.php' );
			remove_submenu_page( 'options-general.php', 'options-media.php' );
			remove_submenu_page( 'options-general.php', 'options-permalink.php' );
			remove_submenu_page( 'options-general.php', 'options-privacy.php' );
			remove_submenu_page( 'options-general.php', 'bugsnag' );
		}

		/**
		 * Removes `third_party` access from roles without permission.
		 */
		if ( ! in_array( 'administrator', (array) $user->roles, true ) &&
			! in_array( 'managing_editor', (array) $user->roles, true ) &&
			! in_array( 'sms_adops', (array) $user->roles, true ) &&
			! in_array( 'design', (array) $user->roles, true )
			) {
			remove_submenu_page( 'options-general.php', 'third_party' );
		}

		/**
		 * Removes `global_settings` access from roles without permission.
		 */
		if ( ! in_array( 'administrator', (array) $user->roles, true ) ) {
			remove_submenu_page( 'options-general.php', 'global_settings' );
		}

		/**
		 * Removes `linkwrapping_rules` access from roles without permission.
		 */
		if ( ! in_array( 'administrator', (array) $user->roles, true ) &&
			! in_array( 'managing_editor', (array) $user->roles, true ) &&
			! in_array( 'sms_adops', (array) $user->roles, true )
			) {
			remove_submenu_page( 'options-general.php', 'linkwrapping_rules' );
		}
	}
}
add_action( 'admin_menu', 'idg_child_theme_remove_admin_menu_items', 999 );
