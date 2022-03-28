<?php

namespace Custom_Fields\Fields;

/**
 * Class to create and apply custom fields to an options page.
 */
final class Options_Page extends Fields_Base {
	const RENDER_ID = 'cf-fields';

	const NAMESPACE = 'cf';

	const CAPABILITY_FILTER = 'cf_capability_';

	/**
	 * The option name.
	 *
	 * @var string
	 */
	public $key;

	/**
	 * The title of the options page.
	 *
	 * @var string
	 */
	public $title;

	/**
	 * Undocumented function
	 *
	 * @param object $config
	 * @param string $key
	 * @param string $title
	 */
	public function __construct( object $config, string $key, string $title ) {
		parent::__construct(
			$config,
			(object) [
				'kind'    => 'root',
				'name'    => 'site',
				'prop'    => $key
			],
			[
				'title' => $title
			]
		);

		$this->set_default_capability(
			apply_filters( self::CAPABILITY_FILTER . $key, 'manage_options' )
		);

		$this->key = $key;

		$this->title = $title;

		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'rest_api_init', [ $this, 'register_settings' ] );
		add_action( 'admin_menu', [ $this, 'add_options_page' ]);
		add_action( 'rest_pre_update_setting', [ $this, 'handle_validation' ], 10, 4 );
	}

	/**
	 * Handle verification of settings when saving.
	 */
	public function handle_validation( $updated, $name, $data, $args ) {
		if ( $this->key !== $name ) {
			return false;
		}

		$this->validate( $data );

		$error = $this->get_errors();

		if ( is_wp_error( $error ) ) {
			wp_send_json( [
				'code'    => $error->get_error_code(),
				'data'    => $error->get_error_data(),
				'message' => $error->get_error_message(),
			], $error->get_error_code() );
		}

		update_option( $args['option_name'], $this->get_values() );

		return true;
	}

	/**
	 * Register setting for each section that has been registered.
	 *
	 * @return void
	 */
	public function register_settings() : void {
		$settings_args = [
			'show_in_rest' => [
				'schema' => [
					'type'       => 'object',
					'properties' =>	[]
				]
			]
		];

		foreach( $this->config->sections as $section ) {
			$settings_args['show_in_rest']['schema']['properties'][$section->name] = [
				'type' => 'string'
			];
		}

		register_setting(
			$this->key,
			$this->key,
			$settings_args
		);

		$options = get_option( $this->key );

		$this->validate( $options ?: [] );
		update_option( $this->key, $this->get_values() );
	}

	/**
	 * Register an options page to add our fields to.
	 *
	 * @return void
	 */
	public function add_options_page() {
		$page_hook = add_options_page(
			__( $this->title, 'cf' ),
			__( $this->title, 'cf' ),
			$this->capability,
			$this->key,
			[ $this, 'render_settings' ]
		);

		add_action( "admin_print_scripts-{$page_hook}", [ $this, 'enqueue_assets'] );
	}

	/**
	 * Echo a HTML Element to render the settings app within.
	 *
	 * @return void
	 */
	public function render_settings() {
		echo '<div id="' . esc_attr( self::RENDER_ID ) .'"></div>';
	}
}
