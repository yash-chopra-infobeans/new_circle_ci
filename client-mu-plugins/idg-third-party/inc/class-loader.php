<?php

namespace IDG\Third_Party;

use IDG\Territories\Geolocation;
use IDG\Products\Article;
use IDG\Products\Vendors\Amazon;

/**
 * Loader for third party settings scripts/actions.
 */
class Loader {
	const SUPPRESION_META_KEY = 'suppress_monetization';
	const WINDOW_NAMESPACE    = 'IDG';
	const SCRIPT_NAME         = 'third-party-integrations-script';
	const STYLE_NAME          = 'third-party-integrations-style';
	const INLINE_HEAD_ACTION  = 'idg_head_inline';
	const SETTINGS_FILTER     = 'idg_third_party_settings';

	/**
	 * Third party suppresions.
	 *
	 * @var [type]
	 */
	public static $suppressed = null;

	/**
	 * Add actions.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'register_meta' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'load' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_editor_assets' ], 1 );
	}

	/**
	 * Register post meta for each section that has been registered.
	 *
	 * @return void
	 */
	public function register_meta() : void {
		foreach ( [ 'post', 'page' ] as $post_type ) {
			register_post_meta(
				$post_type,
				self::SUPPRESION_META_KEY,
				[
					'type'         => 'string',
					'single'       => true,
					'show_in_rest' => true,
					'default'      => '{}',
				]
			);
		}
	}

	/**
	 * Retrieve the the supression meta if available.
	 *
	 * @return null|object
	 */
	public static function get_suppressed_vendors() {
		if ( ! is_null( self::$suppressed ) ) {
			return self::$suppressed;
		}

		$post_id = get_the_ID();

		if ( ! $post_id ) {
			return null;
		}

		$sponsorship = idg_base_theme_get_sponsorship( $post_id );

		if ( $sponsorship && isset( $sponsorship['disable_ads'] ) && $sponsorship['disable_ads'] ) {
			self::$suppressed = (object) [
				'page_ads'    => true,
				'content_ads' => true,
				'jwplayer'    => true,
				'nativo'      => true,
				'outbrain'    => true,
			];

			return self::$suppressed;
		}

		$meta = get_post_meta( $post_id, self::SUPPRESION_META_KEY );

		if ( ! $meta || empty( $meta ) ) {
			return null;
		}

		$suppressed = json_decode( $meta[0] );

		self::$suppressed = $suppressed;

		return $suppressed;
	}

	/**
	 * Load.
	 *
	 * @return void
	 */
	public function load() {
		$plugin_name = basename( IDG_THIRD_PARTY_DIR );
		$plugin_dir  = WPCOM_VIP_CLIENT_MU_PLUGIN_DIR . '/' . $plugin_name;

		wp_enqueue_style(
			self::STYLE_NAME,
			plugins_url( $plugin_name . '/dist/styles/' . IDG_THIRD_PARTY_INDEX_CSS, $plugin_dir ),
			[],
			filemtime( IDG_THIRD_PARTY_DIR . '/dist/styles/' . IDG_THIRD_PARTY_INDEX_CSS )
		);

		$settings = apply_filters( self::SETTINGS_FILTER, Settings::get() );

		ob_start();

		do_action( self::INLINE_HEAD_ACTION, $settings );

		$script = ob_get_clean();


		wp_enqueue_script(
			self::SCRIPT_NAME,
			plugins_url( "{$plugin_name}/dist/scripts/" . IDG_THIRD_PARTY_INDEX_JS, $plugin_dir ),
			[],
			filemtime( IDG_THIRD_PARTY_DIR . '/dist/scripts/' . IDG_THIRD_PARTY_INDEX_JS ),
			false
		);

		wp_enqueue_script(
			'ad-block-check',
			plugins_url( "{$plugin_name}/dist/scripts/" . IDG_THIRD_PARTY_PREBID_ADS_JS, $plugin_dir ),
			[],
			filemtime( IDG_THIRD_PARTY_DIR . '/dist/scripts/' . IDG_THIRD_PARTY_PREBID_ADS_JS ),
			false
		);

		wp_add_inline_script(
			self::SCRIPT_NAME,
			$script,
			'before'
		);

		$post_id  = get_the_ID();
		$products = $post_id ? Article::get_products( $post_id ) : [];

		wp_localize_script(
			self::SCRIPT_NAME,
			self::WINDOW_NAMESPACE,
			[
				'settings'              => $settings,
				'GPT'                   => [
					'ad_slot_name' => GPT\Ad_Slots::create_ad_slot_name(),
					'prefix'       => $settings['gpt']['config']['prefix'],
					'targeting'    => GPT\Ad_Targeting::get(),
				],
				'geolocation'           => Geolocation::get_country_code(),
				'suppress_monetization' => self::get_suppressed_vendors(),
				// @TODO Move these to the products plugin.
				// Calling these here means they are fetched and cached straight away.
				'products'              => $products,
				'vendor_pricing'        => [
					'amazon' => Amazon::fetch( $products ),
				],
			]
		);
	}

	/**
	 * Enqueue editor assets.
	 *
	 * @return void
	 */
	public function enqueue_editor_assets() {
		$plugin_name = basename( IDG_THIRD_PARTY_DIR );
		$plugin_dir  = WPCOM_VIP_CLIENT_MU_PLUGIN_DIR . '/' . $plugin_name;

		wp_enqueue_script(
			self::SCRIPT_NAME,
			plugins_url( $plugin_name . '/dist/scripts/' . IDG_THIRD_PARTY_ADMIN_JS, $plugin_dir ),
			[],
			filemtime( IDG_THIRD_PARTY_DIR . '/dist/scripts/' . IDG_THIRD_PARTY_ADMIN_JS ),
			false
		);
	}
}
