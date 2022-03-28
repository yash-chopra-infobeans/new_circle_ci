<?php

namespace IDG\Post_Type_Filters;

/**
 * Core Plugin class.
 */
class Loader {
	const SCRIPT_NAME = 'idg-post-type-filters-script';
	const STYLE_NAME  = 'idg-post-type-filters-style';

	/**
	 * Add hooks and filters when class is initialized.
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ], 1 );
		add_action( 'restrict_manage_posts', [ $this, 'additional_post_filters' ] );
		add_filter( 'disable_months_dropdown', [ $this, 'disable_months_dropdown' ], 10, 2 );
		add_filter( 'wp_dropdown_cats', [ $this, 'disable_categories_dropdown' ], 10, 2 );
		add_filter( 'parse_query', [ $this, 'post_list_query' ] );
	}

	/**
	 * Add date query parameter's
	 *
	 * @param \WP_Query $query he WP_Query instance (passed by reference).
	 * @return \WP_Query
	 */
	public function post_list_query( \WP_Query $query ) : \WP_Query {
		if ( is_admin() && 'post' === $query->query['post_type'] ) {
			$from_date = filter_input( INPUT_GET, 'from_date', FILTER_SANITIZE_STRING );
			$to_date   = filter_input( INPUT_GET, 'to_date', FILTER_SANITIZE_STRING );

				$query->set(
					'date_query',
					[
						'after'     => $from_date,
						'before'    => $to_date,
						'inclusive' => true,
					]
				);
		}

		return $query;
	}

	/**
	 * Remove category filter dropdown from post list screen
	 *
	 * @param string $output HTML output.
	 * @return string
	 */
	public function disable_categories_dropdown( string $output ) : string {
		$screen = get_current_screen();

		if ( is_admin() && 'post' === $screen->post_type && empty( $screen->taxonomy ) ) {
			return '';
		}

		return $output;
	}

	/**
	 * Remove months dropdown as were replacing it with a date range option.
	 *
	 * @param boolean $disable Whether to disable the drop-down. Default false.
	 * @param string  $post_type The post type.
	 * @return boolean
	 */
	public function disable_months_dropdown( bool $disable, string $post_type ) : bool {
		if ( 'post' === $post_type ) {
			return true;
		}

		return $disable;
	}

	/**
	 * Enqueue any required assets for the admin.
	 *
	 * @return void
	 */
	public function enqueue_assets( $hook ) : void {
		$screen = get_current_screen();

		if ( 'post' !== $screen->post_type || 'edit.php' !== $hook ) {
			return;
		}

		$plugin_name = basename( IDG_POST_TYPE_FILTERS_DIR );
		$plugin_dir  = WPCOM_VIP_CLIENT_MU_PLUGIN_DIR . '/' . $plugin_name;

		wp_enqueue_script(
			self::SCRIPT_NAME,
			plugins_url( "{$plugin_name}/dist/scripts/" . IDG_POST_TYPE_FILTERS_ADMIN_JS, $plugin_dir ),
			[ 'wp-components', 'wp-i18n', 'wp-element', 'wp-editor' ],
			filemtime( IDG_POST_TYPE_FILTERS_DIR . '/dist/scripts/' . IDG_POST_TYPE_FILTERS_ADMIN_JS ),
			true
		);

		wp_enqueue_style(
			self::STYLE_NAME,
			plugins_url( $plugin_name . '/dist/styles/' . IDG_POST_TYPE_FILTERS_ADMIN_CSS, $plugin_dir ),
			[],
			filemtime( IDG_POST_TYPE_FILTERS_DIR . '/dist/styles/' . IDG_POST_TYPE_FILTERS_ADMIN_CSS )
		);

		// enqueue WordPress stylesheets.
		wp_enqueue_style( 'wp-components' );
		wp_enqueue_style( 'wp-editor' );
	}

	/**
	 * Add div so that we can mount our react form.
	 *
	 * @return void
	 */
	public function additional_post_filters() : void {
		$screen = get_current_screen();

		if ( 'post' === $screen->post_type ) {
			echo '<div id="idg-post-filters"></div>';
		}
	}
}
