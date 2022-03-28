<?php

namespace IDG\Sponsored_Links;

/**
 * Management of the sponsored_link post type.
 */
class Sponsored_Link_Post_Type {

	const POST_TYPE_SLUG = 'sponsored_link';

	/**
	 * Add actions.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'init' ] );
		add_action( 'rest_pre_insert_' . self::POST_TYPE_SLUG, [ $this, 'handle_validation' ] );
		add_action( 'cf_enqueue_assets', [ $this, 'enqueue_assets'] );
		add_filter( 'cf_field_types', [ $this, 'add_date_field_schema' ] );
	}

	/**
	 * Initalize function.
	 *
	 * @return void
	 */
	public function init() : void {
		$this->register_sponsored_link_post_type();
		$this->add_custom_fields();
	}

	/**
	 * Register the sponsored_link post type.
	 *
	 * @return void
	 */
	public function register_sponsored_link_post_type() : void {
		register_post_type(
			self::POST_TYPE_SLUG,
			[
				'labels'             => [
					'name'          => 'Sponsored Links',
					'singular_name' => 'Sponsored Link',
				],
				'public'             => true,
				'has_archive'        => false,
				'menu_icon'          => 'dashicons-admin-links',
				'supports'           => [
					'title',
					'thumbnail',
					'editor',
					'featured-image',
					'custom-fields',
				],
				'show_in_rest'       => true,
				'publicly_queryable' => false,
				'rewrite'            => true,
			]
		);
	}

	/**
	 * Add custom fields using \IDG\CustomFields
	 *
	 * @see \CustomFields
	 * @return void
	 */
	public function add_custom_fields() : void {
		$config = json_decode(
			file_get_contents( IDG_SPONSORED_LINKS_DIR . '/inc/config/sponsored-links-fields.json' )
		);

		cf_register_post_type( $config, self::POST_TYPE_SLUG, true );
	}

	/**
	 * Prevent sponsored links being saved with an empty post title.
	 *
	 * @param object $post - The post object.
	 * @return mixed
	 */
	public function handle_validation( $post ) {
		if ( is_autosave() ) {
			return $post;
		}

		if ( isset( $post->post_title ) && empty( $post->post_title ) ) {
			return new \WP_Error(
				400,
				__( 'Sponsored campaign title can not be empty.', 'idg-sponsored-links' )
			);
		}

		return $post;
	}

	/**
	 * Enqueue custom field assets.
	 *
	 * @return void
	 */
	public function enqueue_assets() {

		$plugin_name = basename( IDG_SPONSORED_LINKS_DIR );
		$plugin_dir  = WPCOM_VIP_CLIENT_MU_PLUGIN_DIR . '/' . $plugin_name;

		wp_enqueue_script(
			'date_field',
			plugins_url( "{$plugin_name}/dist/scripts/" . IDG_SPONSORED_LINKS_ADMIN_JS, $plugin_dir ),
			[],
			filemtime( IDG_SPONSORED_LINKS_DIR . '/dist/scripts/' . IDG_SPONSORED_LINKS_ADMIN_JS ),
			true
		);
	}

	/**
	 * Adds schema for date field type.
	 *
	 * @param array $fields An array of field schemas.
	 *
	 * @return array
	 */
	public function add_date_field_schema( $fields ) {
		$date_schema = <<<'JSON'
		{
			"type": "date",
			"schema": {
				"properties": {
					"title": {
						"type": "string"
					},
					"key": {
					"type": "string",
					"pattern": "^\\S*$"
					}
				},
				"required": [
					"title",
					"key"
				]
			}
		}
		JSON;

		$fields[] = json_decode( $date_schema );

		return $fields;
	}
}
