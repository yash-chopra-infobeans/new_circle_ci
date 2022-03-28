<?php

namespace IDG\Asset_Manager;

/**
 * Class for managing admin menu.
 */
class Menus {
	/**
	 * Add hooks and filters when class is initialized.
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'register_menu' ] );

		add_filter( 'parent_file', [ $this, 'highlight_sub_level_menu_item' ] );
	}

	/**
	 * Remove media library menu item and add the asset manager menu item
	 *
	 * @return void
	 */
	public function register_menu() : void {
		// remove standard WordPress Media Library menu item.
		remove_menu_page( 'upload.php' );

		// add menu item for asset manager.
		add_menu_page(
			__( 'Asset Manager', 'idg-plugin-assets' ),
			__( 'Asset Manager', 'idg-plugin-assets' ),
			'read',
			'asset-manager',
			[ $this, 'display_image_manager_page' ],
			'dashicons-format-gallery',
			10
		);

		// add the taxonomy as a sub page underneath the asset manager menu item.
		add_submenu_page(
			'asset-manager',
			__( 'Image Rights', 'idg-plugin-assets' ),
			__( 'Image Rights', 'idg-plugin-assets' ),
			'read',
			'edit-tags.php?taxonomy=asset_image_rights'
		);

		// add the taxonomy as a sub page underneath the asset manager menu item.
		add_submenu_page(
			'asset-manager',
			__( 'Tags', 'idg-plugin-assets' ),
			__( 'Tags', 'idg-plugin-assets' ),
			'read',
			'edit-tags.php?taxonomy=asset_tag'
		);
	}

	/**
	 * Includes path to asset manager template (which is just a div with an id that the react app uses)
	 *
	 * @return void
	 */
	public function display_image_manager_page() : void {
		require_once realpath( IDG_ASSET_MANAGER_DIR . '/inc/views/asset-manager.php' );
	}

	/**
	 * Highlight the appropriate menu item as by default it'll highlight the posts menu item
	 *
	 * @param string $parent_file The parent file.
	 * @return string
	 */
	public function highlight_sub_level_menu_item( string $parent_file ) : string {
		$taxonomy = filter_input( INPUT_GET, 'taxonomy', FILTER_SANITIZE_STRING );

		if ( 'asset_image_rights' === $taxonomy ) {
			$parent_file = 'asset-manager';
		}

		if ( 'asset_tag' === $taxonomy ) {
			$parent_file = 'asset-manager';
		}

		return $parent_file;
	}
}
