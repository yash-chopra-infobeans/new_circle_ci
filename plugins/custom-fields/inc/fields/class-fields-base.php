<?php

namespace Custom_Fields\Fields;

use Exception;
use WP_Error;
use WP_Screen;
use Swaggest\JsonSchema\Schema;

/**
 * The base class for driving the basic fields functionality. This class shoud
 * be extended to apply the functionality to different types of entities.
 *
 * @see inc/fields for different examples.
 */
class Fields_Base {
	const SCHEMA_FILTER = 'cf_field_types';

	const CAPABILITY_FILTER = 'cf_capability';

	const ASSETS_ACTION = 'cf_enqueue_assets';

	const ERROR_CODE = 'cf_invalid_fields';

	const ERROR_MESSAGE = ' invalid field(s).';

	const UNAUTHORIZED_MESSAGE = 'Incorrect persmissions.';

	const SCRIPT_NAME = 'custom-fields-script';

	const STYLE_NAME  = 'custom-fields-style';

	const WINDOW_NAMESPACE  = 'CustomFields';

	/**
	 * @var object
	 */
	public $entity;

	/**
	 * @var string
	 */
	public $capability = 'administrator';

	/**
	 * @var object
	 */
	public $config;

	/**
	 * @var object|null
	 */
	public $options;

	/**
	 * @var object
	 */
	private $config_schema;

	/**
	 * @var array
	 */
	public $errors = [];

	/**
	 * @var int
	 */
	public $error_count = 0;

	/**
	 * @var array
	 */
	protected $values = [];

	/**
	 * @var bool
	 */
	public $add_slashes_to_strings;

	/**
	 * 1. Define variables.
	 * 2. Build config and schema.
	 * 3. Validate configuration.
	 *
	 * @param object $config - The configuration object to define the fields.
	 * @param object $entity - The associated entity that the field data is associated with.
	 * @param object|null $options - Additional options specific to entity type.
	 */
	public function __construct( object $config, object $entity, $options = null ) {
		$this->config = $this->build_config( $config );

		$this->entity = $entity;

		$this->options = $options;

		$this->build_config_schema();

		$this->validate_config();
	}

	/**
	 * Set the default user capability.
	 *
	 * @param string $capability
	 * @return void
	 */
	protected function set_default_capability( string $capability ) : void {
		$this->capability = $capability;
	}

	/**
	 * Enable add slashes which will ensure string values are ran through wp_slash
	 * before being JSON encoded.
	 *
	 * @return void
	 */
	protected function enable_add_slashes() : void {
		$this->add_slashes_to_strings = true;
	}

	/**
	 * Is the current user authorized?
	 *
	 * @param string $capability
	 * @return boolean
	 */
	public function is_user_authorized( $capability = false ) {
		return current_user_can( $capability ?: $this->capability );
	}

	/**
	 * Recursive function to build dynamic config options. If a value
	 * passed is a callable array the result of executing it will replace it's
	 * value. Its values can also contain a callable array which will repeat the proccess.
	 *
	 * @param object|array $config
	 * @return object|array
	 */
	private function build_config( $config ) {
		$is_object = is_object( $config );

		$config = $is_object ? get_object_vars( $config ) : $config;

		if ( ! is_iterable( $config ) ) {
			return $config;
		}

		$config = array_map( function( $item ) {
			return $this->build_config(
				is_callable( $item ) && is_array( $item ) ? call_user_func( $item ) : $item
			);
		}, $config );

		return $is_object ? (object) $config : $config;
	}

	/**
	 * Build the config JSON Schema which will be used to validate the config.
	 *
	 * @return void
	 */
	private function build_config_schema() : void {
		$schema = json_decode( file_get_contents( CUSTOM_FIELDS_DIR . '/inc/schemas/base.json' ) );

		$fields = apply_filters(
			self::SCHEMA_FILTER,
			json_decode( file_get_contents( CUSTOM_FIELDS_DIR . '/inc/schemas/default-fields.json' ) )
		);

		foreach ( $fields as $field ) {
			$field_conditional_schema = (object) [
				'if' => (object) [
					'properties' => (object) [
						'type' => (object) [
							'const' => $field->type
						]
					]
				],
				'then' => (object) $field->schema
			];

			$schema->definitions->fields->items->properties->type->enum[] = $field->type;
			$schema->definitions->fields->items->allOf[] = $field_conditional_schema;
		}

		$this->config_schema = $schema;
	}

	/**
	 * Validate config against schema.
	 *
	 * @return void
	 */
	private function validate_config() : void {
		$validation = Schema::import( $this->config_schema );
		$validation->in( $this->config );
	}

	/**
	 * Validate values against validation rules supplied in config.
	 *
	 * @param array $values
	 * @return void
	 */
	protected function validate( array $values ) : void {
		$this->errors = [];
		$this->error_count = 0;

		foreach( $this->config->sections as $section ) {
			$field_groups = array_filter( $this->config->field_groups, function( $field_group ) use ( $section ) {
				return in_array( $section->name, $field_group->sections );
			} );

			$this->values[$section->name] = json_decode( $values[$section->name], true ) ?? [];

			$this->validate_section( $section, $field_groups );
		}
	}

	/**
	 * Validate a section.
	 *
	 * @param array $values
	 * @param array $field_groups
	 * @return void
	 */
	private function validate_section( $section, array $field_groups = []) {
		foreach ( $field_groups as $field_group ) {
			if ( isset( $section->tabs ) ) {
				foreach ( $section->tabs as $tab ) {
					$this->validate_fields( [
						'fields'  => $field_group->fields,
						'values'  => $this->values[$section->name],
						'section' => $section->name,
						'tab'     => $tab['name'],
						'scope'   => "{$tab['name']}.{$field_group->name}."
					] );
				}

				continue;
			}

			$this->validate_fields( [
				'fields'  => $field_group->fields,
				'values'  => $this->values[$section->name],
				'section' => $section->name,
				'scope'   => "{$field_group->name}."
			] );
		}
	}

	/**
	 * Recursive function to validate an array of fields/fields within fields.
	 *
	 * @param array $args = [
	 *     'fields'  => 'The field settings',
	 * 	   'values'  => 'The values to validate',
	 *     'section' => 'The section name',
	 *     'scope'   => 'The array key in dot notation [key1.key2.key3]'
	 * ]
	 * @return void
	 */
	private function validate_fields( array $args ) {
		foreach( $args['fields'] ?? [] as $field ) {
			$key = "{$args['scope']}{$field->key}";

			$role_cap = $field->capability ?? false;
			$is_authorized = $this->is_user_authorized( $role_cap );

			if ( ! $is_authorized ) {
				$this->set_error(
					"{$args['section']}.{$key}",
					self::UNAUTHORIZED_MESSAGE
				);

				continue;
			}

			$value = array_dot_get( $args['values'], $key );

			if ( is_null( $value ) ) {
				$default_value = isset( $field->default_tabs )
					? $field->default_tabs->{$args['tab']}
					: ($field->default ?? '');

				if ( $default_value ) {
					array_dot_set( $this->values, "{$args['section']}.{$key}", $default_value );
				};
			}

			try {
				if ( isset( $field->validation ) ) {
					$validation = Schema::import( $field->validation->schema );
					$validation->in( $value );
				}
			} catch ( Exception $e ) {
				$this->set_error(
					"{$args['section']}.{$key}",
					$field->validation->message ?? $e->getMessage()
				);
			}

			if ( $this->add_slashes_to_strings && is_string( $value ) ) {
				array_dot_set( $this->values, "{$args['section']}.{$key}", wp_slash( $value ) );
			}

			if ( isset( $field->fields ) && is_array( $value ) ) {
				foreach ( $value as $index => $value ) {
					$this->validate_fields( [
						'fields'  => $field->fields,
						'values'  => $args['values'],
						'section' => $args['section'],
						'scope'   => "{$key}.{$index}."
					] );
				}
			}
		}
	}

	/**
	 * Set an error on the errors array.
	 *
	 * @param string $key The key of the field to attach the error to, in dot notaion.
	 * @param string $message
	 * @return void
	 */
	private function set_error( string $key, string $message ) : void {
		$this->error_count++;

		array_dot_set(
			$this->errors,
			$key,
			$message
		);
	}

	/**
	 * Check if there are any errors.
	 *
	 * @return boolean
	 */
	protected function has_errors() {
		return ! empty( $this->errors );
	}

	/**
	 * If there are errors return an instance of WP_Error with the errors attached.
	 *
	 * @return WP_Error|boolean;
	 */
	protected function get_errors() {
		if ( ! $this->has_errors() ) {
			return false;
		}

		$error = new WP_Error(
			400,
			$this->error_count . __( self::ERROR_MESSAGE, 'cf' ),
			[
				self::ERROR_CODE => $this->errors,
			]
		);

		return $error;
	}

	/**
	 * Return the validated values.
	 *
	 * @return array
	 */
	protected function get_values() : array {
		return array_map( function( $section ) {
			return wp_json_encode( is_array( $section ) && !empty( $section ) ? $section : (object) [] );
		}, $this->values );
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @return void
	 */
	public function enqueue_assets() : void {
		$plugin_name = basename( CUSTOM_FIELDS_DIR );

		wp_enqueue_script(
			self::SCRIPT_NAME,
			plugins_url( $plugin_name . '/dist/scripts/' . CUSTOM_FIELDS_ADMIN_JS ),
			[ 'wp-i18n', 'wp-block-editor', 'wp-blocks', 'wp-block-library', 'wp-element', 'wp-components', 'wp-data', 'wp-core-data', 'wp-media-utils'],
			filemtime( CUSTOM_FIELDS_DIR . '/dist/scripts/' . CUSTOM_FIELDS_ADMIN_JS ),
			true
		);

		wp_enqueue_style( 'wp-format-library' );

		wp_enqueue_style(
			self::STYLE_NAME,
			plugins_url( $plugin_name . '/dist/styles/' . CUSTOM_FIELDS_ADMIN_CSS ),
			['wp-edit-blocks'],
			filemtime( CUSTOM_FIELDS_DIR . '/dist/styles/' . CUSTOM_FIELDS_ADMIN_CSS )
		);

		wp_localize_script( self::SCRIPT_NAME, self::WINDOW_NAMESPACE, (object) [
			'config'        => $this->config,
			'entity'        => $this->entity,
			'options'       => $this->options,
			'fieldTypes'    => (object) [],
			'plugins'       => (object) []
		] );

		do_action( self::ASSETS_ACTION, $this->entity );
	}
}
