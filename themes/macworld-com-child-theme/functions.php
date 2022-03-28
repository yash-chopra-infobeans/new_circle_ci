<?php
/**
 * Contains required functions for macworld theme
 *
 * @package macworld-com-child-theme
 */

/**
 * Rewrites.
 */
require_once get_theme_file_path( '/inc/rewrite.php' );

/**
 * Frontend.
 */
require get_theme_file_path( '/inc/frontend/loader.php' );

/**
 * Admin.
 */
require get_theme_file_path( '/inc/admin/roles.php' );
require get_theme_file_path( '/inc/admin/permissions.php' );

/**
 * Filters & Actions.
 */
require get_theme_file_path( '/inc/actions.php' );

/**
 * AMP.
 */
require_once get_theme_file_path( '/inc/amp/loader.php' );

/**
 * Asset settings load.
 */
require_once get_stylesheet_directory() . '/inc/asset-settings.php';
