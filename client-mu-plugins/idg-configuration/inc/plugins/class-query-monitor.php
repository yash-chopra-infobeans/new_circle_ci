<?php
/**
 * Query montor handler.
 *
 * @package IDG.
 */

namespace IDG\Configuration\Plugins;

/**
 * Class to handle query monitor config and unfiltered
 * html on edit page.
 */
class Query_Monitor {

	/**
	 * Class construct:
	 *
	 * Adds hook to handle query monitor on admin init.
	 * Adds hook to enable unfiltered HTML to admin.
	 */
	public function __construct() {
		add_action( 'admin_init', [ $this, 'enable_query_monitor' ] );
		add_filter( 'map_meta_cap', [ $this, 'enable_unfiltered_html_to_admin' ], 99, 2 ); 
	}

	/**
	 * Add view query monitor capapbility on admin init on specific enviroment.
	 */
	public function enable_query_monitor() {
		$allowed_enviroments = [ 'local', 'develop' ];

		if ( defined( 'VIP_GO_APP_ENVIRONMENT' ) && in_array( VIP_GO_APP_ENVIRONMENT, $allowed_enviroments, true ) ) {
			$role = get_role( 'administrator' );
			$role->add_cap( 'view_query_monitor' );
		}
	}

	/**
	 * Enable admin to add unfiltered HTML.
	 *
	 * @param array  $caps The allowed capability array.
	 * @param string $cap The check capability.
	 * @return array returs allowed updated capability.
	 */
	public function enable_unfiltered_html_to_admin( array $caps, $cap ) {
		$usr = wp_get_current_user();
		if ( in_array( 'administrator', (array) $usr->roles, true ) && 'unfiltered_html' === $cap ) {
			$caps = [ 'unfiltered_html' ];
		}
		return $caps;
	}
}
