<?php

namespace IDG\Third_Party;

/**
 * IAS integration.
 */
class Ias {
	/**
	 * Add actions
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', [ $this, 'head' ] );
	}

	/**
	 * Add IAS script to head
	 *
	 * @return void
	 */
	public function head() {
		$pub_id = Settings::get( 'ias' )['account']['pub_id'] ?? false;

		if ( ! $pub_id ) {
			return;
		}

		?>

		<script async="async" src="//cdn.adsafeprotected.com/iasPET.1.js"></script>

		<?php
	}
}
