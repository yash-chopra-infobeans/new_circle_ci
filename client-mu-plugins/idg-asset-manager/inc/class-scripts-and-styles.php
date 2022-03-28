<?php

namespace IDG\Asset_Manager;

use IDG\Publishing_Flow\Sites;

/**
 * Class responsible for including scripts and styles.
 */
class Scripts_And_Styles {
	const SCRIPT_NAME           = 'idg-asset-manager-script';
	const STYLE_NAME            = 'idg-asset-manager-style';
	const SCRIPT_GUTENBERG_NAME = 'idg-asset-manager-gutenberg-script';
	const STYLE_GUTENBERG_NAME  = 'idg-asset-manager-gutenberg-style';
	const JW_PLAYER_SCRIPT_NAME = 'idg-asset-manager-jw-player-script';

	/**
	 * Add hooks and filters when class is initialized.
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );

		// We can use the exposed components in this plugin with custom fields plugin as it is also loaded at 1 priority.
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_block_assets' ], 1 );
	}

	/**
	 * Return values that are used within JS scripts.
	 *
	 * @return array
	 */
	public function get_localized_script_values() : array {
		$current_user = wp_get_current_user();
		$upload_dir   = wp_upload_dir();

		$player     = cf_get_value( 'third_party', 'jw_player', 'config.player_library_id' );
		$amp_player = cf_get_value( 'third_party', 'jw_player', 'config.amp_player_library_id' );

		if ( empty( $player ) ) {
			$player = 'kAvvfxjt';
		}

		if ( empty( $amp_player ) ) {
			$amp_player = 'wySF9V4I';
		}

		return [
			'root'                => esc_url_raw( rest_url() ),
			'nonce'               => wp_create_nonce( 'wp_rest' ),
			'maxUploadSize'       => wp_max_upload_size(),
			'allowedMimeTypes'    => get_allowed_mime_types(),
			'currentUser'         => [
				'ID'          => $current_user->ID,
				'username'    => $current_user->user_login,
				'displayName' => $current_user->display_name,
			],
			'publishingFlowSites' => Sites::get_sites_list(),
			'uploadDir'           => $upload_dir,
			'imageSizes'          => apply_filters(
				'idg_asset_manager_image_sizes',
				[
					1240,
					300,
					150,
				]
			),
			'imageRatios'         => apply_filters(
				'idg_asset_manager_image_ratios',
				[
					'1:1',
					'16:9',
					'3:2',
				]
			),
			'jwPlayer'            => [
				'players' => [
					'embedPlayer' => $player,
					'ampPlayer'   => $amp_player,
				],
			],
		];
	}

	/**
	 * Includes asset manager scripts and styles on admin pages that require it.
	 *
	 * @param string $hook The current admin page.
	 * @return void
	 */
	public function enqueue_assets( string $hook ) : void {
		if ( 'toplevel_page_asset-manager' !== $hook ) {
			return;
		}

		$plugin_name = basename( IDG_ASSET_MANAGER_DIR );
		$plugin_dir  = WPCOM_VIP_CLIENT_MU_PLUGIN_DIR . '/' . $plugin_name;

		wp_enqueue_script(
			self::SCRIPT_NAME,
			plugins_url( "{$plugin_name}/dist/scripts/" . IDG_ASSET_MANAGER_ADMIN_JS, $plugin_dir ),
			[ 'wp-components', 'wp-i18n', 'wp-element', 'wp-editor' ],
			filemtime( IDG_ASSET_MANAGER_DIR . '/dist/scripts/' . IDG_ASSET_MANAGER_ADMIN_JS ),
			true
		);

		wp_localize_script( self::SCRIPT_NAME, 'assetManager', $this->get_localized_script_values() );

		wp_enqueue_style(
			self::STYLE_NAME,
			plugins_url( "{$plugin_name}/dist/styles/" . IDG_ASSET_MANAGER_ADMIN_CSS, $plugin_dir ),
			[],
			filemtime( IDG_ASSET_MANAGER_DIR . '/dist/styles/' . IDG_ASSET_MANAGER_ADMIN_CSS )
		);

		// enqueue WordPress stylesheets.
		wp_enqueue_style( 'wp-components' );
		wp_enqueue_style( 'wp-editor' );
	}

	/**
	 * Includes asset manager scripts and styles within the editor.
	 *
	 * @return void
	 */
	public function enqueue_block_assets() : void {
		$plugin_name = basename( IDG_ASSET_MANAGER_DIR );
		$plugin_dir  = WPCOM_VIP_CLIENT_MU_PLUGIN_DIR . '/' . $plugin_name;

		wp_enqueue_script(
			self::SCRIPT_GUTENBERG_NAME,
			plugins_url( "{$plugin_name}/dist/scripts/" . IDG_ASSET_MANAGER_GUTENBERG_JS, $plugin_dir ),
			[ 'wp-components', 'wp-i18n', 'wp-element', 'wp-editor', 'wp-blocks' ],
			filemtime( IDG_ASSET_MANAGER_DIR . '/dist/scripts/' . IDG_ASSET_MANAGER_GUTENBERG_JS ),
			true
		);

		wp_localize_script( self::SCRIPT_GUTENBERG_NAME, 'assetManager', $this->get_localized_script_values() );

		wp_enqueue_style(
			self::STYLE_GUTENBERG_NAME,
			plugins_url( "{$plugin_name}/dist/styles/" . IDG_ASSET_MANAGER_GUTENBERG_CSS, $plugin_dir ),
			[],
			filemtime( IDG_ASSET_MANAGER_DIR . '/dist/styles/' . IDG_ASSET_MANAGER_GUTENBERG_CSS )
		);
	}
}
