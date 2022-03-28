<?php

namespace IDG\Third_Party;

/**
 * Settings for third party integration.
 */
class Settings {
	const KEY = 'third_party';

	/**
	 * Slot settings defined in third party settings.
	 *
	 * @var array
	 */
	public static $settings = [];

	/**
	 * Add actions.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'create_options_page' ] );
	}

	/**
	 * Create options page.
	 *
	 * @return void
	 */
	public function create_options_page() {
		$config = json_decode(
			file_get_contents( IDG_THIRD_PARTY_DIR . '/inc/config/settings-fields.json' )
		);

		cf_register_options_page(
			$config,
			'third_party',
			__( 'Third Party', 'idg' )
		);
	}

	/**
	 * Retrieve a settings value.
	 *
	 * @param string $vendor - The vendor.
	 * @return mixed
	 */
	public static function get( $vendor = null ) {
		if ( ! self::$settings ) {
			self::$settings = cf_get_value( self::KEY );
		}

		if ( ! $vendor ) {
			return self::$settings;
		}

		return self::$settings[ $vendor ];
	}
}
