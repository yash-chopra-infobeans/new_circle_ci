<?php

namespace IDG\Configuration\Plugins;

use IDG\Configuration\Error_Reporting;

class Bugsnag {
	public function __construct() {
		add_action( 'plugins_loaded', [ $this, 'setup' ] );
		add_action( 'admin_print_scripts', [ $this, 'js_api_key' ] );
	}

	/**
	 * Set base information that will be set with each report.
	 *
	 * @return void
	 */
	public function setup() {
		if ( ! Error_Reporting::can_use_bugsnag() ) {
			return;
		}

		global $bugsnagWordpress;

		$bugsnagWordpress->setReleaseStage( VIP_GO_APP_ENVIRONMENT );
		$bugsnagWordpress->setType( 'wordpress' );
	}


	/**
	 * Output the api key within the admin to allow usage
	 * with javscript.
	 *
	 * @return void
	 */
	public function js_api_key() {
		$api_key = get_site_option( 'bugsnag_api_key', '' );

		echo '<script>var bugsnagAPIKey = "' . esc_html( $api_key ) . '"</script>';
	}
}
