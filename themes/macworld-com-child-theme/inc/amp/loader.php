<?php

if ( ! function_exists( 'macworld_amp_assets' ) ) {
	/**
	 * Enqueues AMP related assets
	 */
	function macworld_amp_assets() {
		macworld_com_child_theme_var_setup();
	}
}

add_action( 'amp_post_template_head', 'macworld_amp_assets' );
