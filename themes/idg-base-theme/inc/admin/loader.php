<?php
if ( ! function_exists( 'admin_idg_assets' ) ) {
	/**
	 * Enqueues custom admin stylesheet
	 */
	function admin_idg_assets() {
		wp_enqueue_style(
			'general-admin-styles',
			get_template_directory_uri() . '/dist/styles/' . IDG_BASE_THEME_ADMIN_CSS,
			[],
			filemtime( get_template_directory() . '/dist/styles/' . IDG_BASE_THEME_ADMIN_CSS ),
			'all'
		);
	}
}
add_action( 'admin_enqueue_scripts', 'admin_idg_assets' );
