<?php

namespace IDG\Third_Party;

use function IDG\Base_Theme\Utils\is_amp;

/**
 * Base Data Layer.
 */
class Base_Data_Layer {
	const FILTER = 'idg_data_layer';

	/**
	 * The datalayer.
	 *
	 * @var array
	 */
	public static $data = [];

	/**
	 * Add the datalayer to wp_head.
	 */
	public function __construct() {
		add_action( Loader::INLINE_HEAD_ACTION, [ $this, 'generate' ] );
		add_action( 'amp_post_template_head', [ $this, 'generate' ] );
	}

	/**
	 * Get default data.
	 *
	 * @return array
	 */
	public static function get_default_data() {
		return [
			'adBlockerEnabled' => true, // Set in JS on page load.
			'arenaId'          => '', // Set via JS, defaults to empty string.
			'audience'         => 'consumer', // Will only need updating when doing B2B.
			'ccpaOptedOut'     => 'false', // Set in JS on page load.
			'contentStrategy'  => '@TODO',
			'environment'      => 'wp_' . constant( 'VIP_GO_APP_ENVIRONMENT' ),
			'sessionNumber'    => '@TODO',
			'firstSessionDate' => '@TODO',
			'lastSessionDate'  => '@TODO',
			'timestamp'        => time(),
			'url'              => get_permalink(),
		];
	}

	/**
	 * Generate the datalayer and attach to the window.
	 *
	 * @return void
	 */
	public function generate() {
		self::$data = apply_filters( self::FILTER, self::get_default_data() );

		if ( is_amp() ) {
			return;
		}

		?>
			window.dataLayer = window.dataLayer || [];
			window.dataLayer.push(<?php echo wp_json_encode( self::$data ); ?>);
		<?php
	}
}
