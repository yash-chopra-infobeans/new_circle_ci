<?php

namespace IDG\Publishing_Flow;

use ErrorException;
use IDG\Publishing_Flow\Statuses;

/**
 * Runs the plugin setup sequence.
 *
 * @return void
 */
function setup() {
	if ( ! defined( 'PUBLISHING_FLOW_EXPECTED_SOURCE_URL' ) ) {
		wp_die( 'Please define PUBLISHING_FLOW_EXPECTED_SOURCE_URL' );
	}

	if ( ! defined( 'PUBLISHING_FLOW_ENTRY_ORIGIN_HEADER' ) ) {
		define( 'PUBLISHING_FLOW_ENTRY_ORIGIN_HEADER', 'X-Entry-Origin' );
	}

	/**
	 * Filter requires removing as user check fails due to ES method
	 * \ElasticPress\Indexables::factory()->get( 'post' ) returning a boolean
	 * and it being method chained (see: mu-plugins/search/includes/classes/class-cache.php:L37).
	 * To get around this we're unhooking the auth call that starts this erroneous sequence.
	 *
	 * See here for a full stacktrace that led to this being included;
	 * https://app.bugsnag.com/big-bite/idg/errors/5fd22df86799f20017846b56
	 */
	remove_filter( 'determine_current_user', 'WP\\OAuth2\\Authentication\\attempt_authentication', 11 );

	new Auth();
	new Loader();
	new Sites();
	new Statuses();
	new Authors();
	new Terms();
	new Cache();
	new Embargo();
	new User_Profiles();
}
