<?php

if ( ! function_exists( 'idg_base_theme_roles' ) ) {
	/**
	 * Registers custom roles and removes Subscriber on theme activation.
	 */
	function idg_base_theme_roles() {
		wpcom_vip_add_role(
			'design',
			__( 'Design', 'idg-base-theme' ),
			apply_filters( 'idg_role_design_capabilities', [] ),
		);

		wpcom_vip_add_role(
			'managing_editor',
			__( 'Managing Editor', 'idg-base-theme' ),
			apply_filters( 'idg_role_managing_editor_capabilities', [] ),
		);

		wpcom_vip_add_role(
			'sms_adops',
			__( 'SMS AdOps', 'idg-base-theme' ),
			apply_filters( 'idg_role_sms_adops_capabilities', [] ),
		);

		remove_role( 'subscriber' );
	}
}
add_action( 'after_switch_theme', 'idg_base_theme_roles', 0 );
