<?php

namespace IDG\Third_Party\GPT;

use IDG\Third_Party\Settings;
use function IDG\Base_Theme\Utils\is_amp;
use IDG\Third_Party\Loader;

/**
 * Define templates and conditions.
 */
class Ad_Templates {
	/**
	 * Template definitions.
	 *
	 * @var array
	 */
	public static $templates = [
		[
			'label'       => 'Banner',
			'key'         => 'banner',
			'class'       => 'ad page-ad has-ad-prefix ad-banner is-sticky',
			'out_of_page' => false,
			// Only needed for AMP.
			'count'       => 1,
		],
		[
			'label'       => 'Content',
			'key'         => 'article',
			'class'       => 'ad page-ad has-ad-prefix ad-article',
			'out_of_page' => false,
			// Only needed for AMP.
			'count'       => 1,
		],
		[
			'label'       => 'Footer',
			'key'         => 'footer',
			'class'       => 'ad page-ad has-ad-prefix ad-footer',
			'out_of_page' => false,
			// Only needed for AMP.
			'count'       => 1,
		],
		[
			'label'       => 'Right Rail',
			'key'         => 'right_rail',
			'class'       => 'ad page-ad ad-right-rail is-sticky',
			'out_of_page' => false,
			// Only needed for AMP.
			'count'       => 1,
		],
		[
			'label'       => 'Overlay',
			'key'         => 'overlay',
			'class'       => 'ad ad-overlay',
			'out_of_page' => true,
			// Only needed for AMP.
			'count'       => 1,
		],
		[
			'label'       => 'Skin',
			'key'         => 'skin',
			'class'       => 'ad ad-skin',
			'out_of_page' => true,
			// Only needed for AMP.
			'count'       => 1,
		],
		[
			'label'       => 'Bouncex',
			'key'         => 'bouncex',
			'class'       => 'ad ad-bouncex',
			'out_of_page' => true,
			// Only needed for AMP.
			'count'       => 1,
		],
	];

	/**
	 * Get options.
	 *
	 * @return array
	 */
	public static function get_options() : array {
		$options = array_map(
			function( $template ) {
				return [
					'value' => $template['key'],
					'label' => $template['label'],
				];
			},
			self::$templates
		);

		return array_merge(
			[
				[
					'value'    => '',
					'label'    => 'Select a predefined template',
					'disabled' => true,
				],
			],
			$options
		);
	}

	/**
	 * Are content ads supressed?
	 *
	 * @return bool
	 */
	public static function content_ads_are_suppressed() {
		$suppressed = Loader::get_suppressed_vendors();

		if ( ! isset( $suppressed->content_ads ) ) {
			return false;
		}

		return $suppressed->content_ads;
	}

	/**
	 * Are page ads suppressed?
	 *
	 * @return bool
	 */
	public static function page_ads_are_suppressed() {
		$suppressed = Loader::get_suppressed_vendors();

		if ( ! isset( $suppressed->page_ads ) ) {
			return false;
		}

		return $suppressed->page_ads;
	}

	/**
	 * Render an ad if a defined template exists.
	 *
	 * @param string $key - The template key.
	 *
	 * @return void
	 */
	public static function render( $key ) : void {
		if ( 'article' === $key && self::content_ads_are_suppressed() ) {
			return;
		}

		if ( 'article' !== $key && self::page_ads_are_suppressed() ) {
			return;
		}

		$template_index = array_search( $key, array_column( self::$templates, 'key' ) );

		$template = self::$templates[ $template_index ] ?? null;

		if ( ! $template ) {
			return;
		}

		$slot = Ad_Slots::get_slot_config( $template['key'] );

		if ( isset( $slot['disabled'] ) && $slot['disabled'] ) {
			return;
		}

		/**
		 * Most logic is handled in JS for web ads.
		 *
		 * @see inc/src/modules/gpt
		 */
		if ( ! is_amp() ) {
			printf(
				'<div class="%s" data-ad-template="%s" data-ofp="%s"></div>',
				esc_attr( $template['class'] ?? '' ),
				esc_attr( $template['key'] ),
				esc_attr( $template['out_of_page'] ? 'true' : 'false' )
			);

			return;
		}

		$mobile_sizes = $slot['size_definitions'][2]['sizes'] ?: false;

		// Amp should only render ads with mobile sizes defined.
		if ( ! $mobile_sizes ) {
			return;
		}

		$size              = explode( 'x', $slot['size'] );
		$index_exchange_id = Settings::get( 'index_exchange' )['config']['id'];
		$ad_slot           = Settings::get( 'gpt' )['config']['prefix'] . Ad_Slots::create_ad_slot_name();
		$targeting         = Ad_Targeting::get();
		$targeting['pos']  = str_replace(
			'{{count}}',
			self::$templates[ $template_index ]['count'],
			$slot['pos']
		);

		// Increment count.
		self::$templates[ $template_index ]['count'] = self::$templates[ $template_index ]['count'] + 1;

		?>
			<div class="amp-ad-container">
				<amp-ad
					width="<?php echo esc_attr( $size[0] ); ?>"
					height="<?php echo esc_attr( $size[1] ); ?>"
					type="doubleclick"
					layout="fixed"
					data-slot="<?php echo esc_attr( $ad_slot ); ?>"
					data-multi-size="<?php echo esc_attr( $mobile_sizes ); ?>"
					data-block-on-consent
					data-npa-on-unknown-consent="true"
					data-loading-strategy="prefer-viewability-over-views"
					rtc-config='{
						"vendors": {
							"IndexExchange": {"SITE_ID": "<?php echo esc_attr( $index_exchange_id ); ?>"}
						},
						"urls": [
							"https://idg.amp.permutive.com/rtc?type=doubleclick"
						],
						"timeoutMillis": 1000
					}'
					json='{ "targeting": <?php echo wp_json_encode( $targeting ); ?>} '>
				</amp-ad>
			</div>

		<?php
	}
}
