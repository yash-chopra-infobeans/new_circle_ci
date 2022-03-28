<?php

use function IDG\Base_Theme\Utils\is_amp;

if ( ! function_exists( 'idg_base_theme_amp_assets' ) ) {
	/**
	 * Enqueues AMP related assets
	 */
	function idg_base_theme_amp_assets() {
		idg_base_theme_meta();
		idg_base_theme_opengraph_meta();
		idg_base_theme_var_setup();
		idg_base_theme_typekit_script();

		printf(
			'<style amp-custom>%s</style>',
			//phpcs:ignore
			file_get_contents( get_template_directory() . '/dist/styles/' . IDG_BASE_THEME_AMP_CSS )
		);
	}
}

add_action( 'amp_post_template_head', 'idg_base_theme_amp_assets' );

if ( ! function_exists( 'idg_amp_post_template' ) ) {
	/**
	 * Load custom template for articles.
	 *
	 * @param string $file - The file name.
	 * @param string $type - The template type.
	 * @param object $post - The post object.
	 * @return string
	 */
	function idg_amp_post_template( $file, $type, $post ) {
		if ( 'single' === $type && 'post' === $post->post_type ) {
			return get_template_directory() . '/inc/amp/templates/post-single.php';
		}

		return $file;
	};
}

add_filter( 'amp_post_template_file', 'idg_amp_post_template', 10, 3 );

// Disable the amp customizer.
add_filter( 'amp_customizer_is_enabled', '__return_false' );

// Hacky way to disable query monitor on amp pages.
add_filter(
	'user_has_cap',
	function( array $user_caps ) {
		if ( is_amp() ) {
			$user_caps['view_query_monitor'] = false;
		}

		return $user_caps;
	},
	10,
	4
);
