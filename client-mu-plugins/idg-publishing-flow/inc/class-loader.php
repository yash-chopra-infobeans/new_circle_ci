<?php

namespace IDG\Publishing_Flow;

use IDG\Publishing_Flow\API\Routes;

/**
 * Core Plugin class.
 */
class Loader {
	/**
	 * For storing the id of the published content.
	 */
	const META_POST_PUBLISHED_IDS = '__idg_published_ids';

	/**
	 * For storing whether the article has been published.
	 */
	const META_POST_PUBLISHED_STATUS = '__idg_published_status';

	/**
	 * For storing whether the article has been published.
	 */
	const META_POST_EMBARGO_DATE = 'embargo_date';

	const SCRIPT_NAME = 'idg-status-flow-script';
	const STYLE_NAME  = 'idg-status-flow-style';

	/**
	 * Initialise the hooks and filters.
	 */
	public function __construct() {
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_assets' ], 1 );
		add_action( 'rest_api_init', [ ( new Routes() ), 'register_routes' ] );
		add_action( 'init', [ $this, 'register_meta' ] );

		/**
		 * Hook to overwrite the default category to prevent one being
		 * assigned when there are none. This aids the forcing of category
		 * in publishing rules.
		 */
		add_filter( 'option_default_category', '__return_zero' );
		add_filter( 'wp_dropdown_cats', [ $this, 'disable_default_category_select' ], 10, 2 );
	}

	/**
	 * Enqueue any required assets for the admin.
	 *
	 * @return void
	 */
	public function enqueue_assets() : void {
		global $post_type;

		if ( 'post' !== $post_type ) {
			return;
		}

		$plugin_name = basename( IDG_PUBLISHING_FLOW_DIR );
		$plugin_dir  = WPCOM_VIP_CLIENT_MU_PLUGIN_DIR . '/' . $plugin_name;

		wp_enqueue_script(
			self::SCRIPT_NAME,
			plugins_url( $plugin_name . '/dist/scripts/' . IDG_PUBLISHING_FLOW_ADMIN_JS, $plugin_dir ),
			[ 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-plugins', 'wp-edit-post' ],
			filemtime( IDG_PUBLISHING_FLOW_DIR . '/dist/scripts/' . IDG_PUBLISHING_FLOW_ADMIN_JS ),
			false
		);

		wp_localize_script(
			self::SCRIPT_NAME,
			'IDGPublishingFlow',
			[
				'statuses'       => Statuses::get_status_list(),
				'sites'          => Sites::get_sites_list(),
				'business_units' => Sites::get_business_units_list(),
				'is_origin'      => Sites::is_origin(),
				'origin_url'     => Sites::get_origin_url(),
			]
		);

		wp_enqueue_style(
			self::STYLE_NAME,
			plugins_url( $plugin_name . '/dist/styles/' . IDG_PUBLISHING_FLOW_ADMIN_CSS, $plugin_dir ),
			[],
			filemtime( IDG_PUBLISHING_FLOW_DIR . '/dist/styles/' . IDG_PUBLISHING_FLOW_ADMIN_CSS )
		);
	}

	/**
	 * Register any meta that is required.
	 *
	 * @return void
	 */
	public function register_meta() {
		register_meta(
			'post',
			self::META_POST_PUBLISHED_IDS,
			[
				'type'          => 'array',
				'single'        => true,
				'description'   => 'A list of sites and their ids which this post has been published on.',
				'show_in_rest'  => false,
				'auth_callback' => '__return_true',
			]
		);

		register_meta(
			'post',
			self::META_POST_PUBLISHED_STATUS,
			[
				'type'          => 'string',
				'single'        => true,
				'description'   => 'The published status of the post.',
				'default'       => 'draft',
				'show_in_rest'  => true,
				'auth_callback' => '__return_true',
			]
		);

		register_meta(
			'post',
			self::META_POST_EMBARGO_DATE,
			[
				'type'          => 'string',
				'single'        => true,
				'description'   => 'The embargo date of an article.',
				'default'       => '',
				'show_in_rest'  => true,
				'auth_callback' => '__return_true',
			]
		);
	}

	/**
	 * Disables the default category option as it is
	 * no longer required since setting `option_default_category`
	 * to return 0 - or no category.
	 *
	 * @param string $output The parsed output - HTML select and options based on categories.
	 * @param array  $parsed_args The arguments passed to wp_dropdown_categories.
	 * @return string
	 */
	public function disable_default_category_select( string $output, array $parsed_args ) : string {
		if ( 'default_category' === $parsed_args['name'] ) {
			$output = '<em>Feature disabled.</em>';
		}

		return $output;
	}
}
