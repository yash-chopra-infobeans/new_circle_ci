<?php
/**
 * Plugin Name: IDG Sponsored Links
 * Plugin URI: https://rtcamp.com
 * Description: The plugin for sponsored links management.
 * Author: Big Bite
 * Author URI: https://rtcamp.com
 * Version: 1.0.0-alpha.1
 */

namespace IDG\Sponsored_Links;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'IDG_SPONSORED_LINKS_DIR' ) ) {
	define( 'IDG_SPONSORED_LINKS_DIR', rtrim( plugin_dir_path( __FILE__ ), '/' ) );
}

require_once IDG_SPONSORED_LINKS_DIR . '/vendor/autoload_packages.php'; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingCustomConstant

add_action( 'plugins_loaded', __NAMESPACE__ . '\\setup' );
