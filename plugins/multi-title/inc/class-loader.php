<?php

namespace BigBite\MultiTitle;

/**
 * Core Loader class.
 */
class Loader {
	const EDITOR_SCRIPT = 'multi-title-script';
	const EDITOR_STYLE  = 'multi-title-styles';
	const POST_TYPES  	= ['post', 'page'];

	public function __construct() {
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_assets' ], 1 );
		add_action( 'init', [ $this, 'register_meta' ] );
		add_action( 'init', [ $this, 'set_post_template' ] );
		add_action( 'plugins_loaded', [ $this, 'register_multi_title_block_type' ] );
	}

	/**
	 * Registers block in PHP
	 *
	 * @return void
	 */
	public function register_multi_title_block_type() {
		register_block_type(
			'bigbite/multi-title',
			[
				'editor_script' => self::EDITOR_SCRIPT,
				'editor_style'  => self::EDITOR_STYLE,
			]
		);
	}

	/**
	 * Register required plugin meta fields - data is saved into a single meta field by default
	 *
	 * @return void
	 */
	public function register_meta() {
		$args = array(
			'auth_callback' => 'is_user_logged_in',
			'type'          => 'string',
			'single'        => true,
			'show_in_rest'  => true,
		);

		foreach ( self::POST_TYPES as $post_type ){
			register_meta( $post_type, 'multi_title', $args );
		}
	}

	public function set_post_template() {
		$template = [
			[ 'bigbite/multi-title' ],
		];

		foreach ( self::POST_TYPES as $post_type ){

			$post_type_object = get_post_type_object( $post_type );

			if ( ! empty( $post_type_object->template ) ) {
				$template = $post_type_object->template;
			}

			if ( isset( $template[0][0] ) && $template[0][0] !== 'bigbite/multi-title' ) {
				// Set the header as the template.
				$template = array_merge(
					[
						[ 'bigbite/multi-title' ],
					],
					$template
				);
			}

			$post_type_object->template = apply_filters( 'multi_title_post_template', $template );
		}
	}

	/**
	 * Include plugin assets (js & css) within editor
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		global $post;

		if ( isset( $post->post_type ) && ! in_array ( $post->post_type, self::POST_TYPES, true ) ) {
			return;
		}

		$plugin_name = basename( MULTI_TITLE_PLUGIN_DIR );

		wp_enqueue_script(
			self::EDITOR_SCRIPT,
			plugins_url( $plugin_name . '/dist/scripts/' . MULTI_TITLE_INDEX_JS ),
			[ 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-plugins', 'wp-edit-post', 'wp-editor' ],
			filemtime( MULTI_TITLE_PLUGIN_DIR . '/dist/scripts/' . MULTI_TITLE_INDEX_JS ),
			false
		);

		wp_enqueue_style(
			self::EDITOR_STYLE,
			plugins_url( $plugin_name . '/dist/styles/' . MULTI_TITLE_INDEX_CSS ),
			[],
			filemtime( MULTI_TITLE_PLUGIN_DIR . '/dist/styles/' . MULTI_TITLE_INDEX_CSS )
		);
	}
}
