<?php

if ( ! function_exists( 'idg_register_global_settings_page' ) ) {
	function idg_register_global_settings_page() {
		$config = json_decode(
			file_get_contents( __DIR__ . '/configs/global.json' )
		);

		cf_register_options_page( $config, 'global_settings', __( 'Global', 'idg-base-theme' ) );
	}
}

add_action( 'init', 'idg_register_global_settings_page' );
