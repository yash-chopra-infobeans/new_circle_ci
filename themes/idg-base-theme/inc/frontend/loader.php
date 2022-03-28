<?php
/**
 * IDG base theme loader file
 *
 * @package themes idg-base-theme
 */

use function \IDG\Base_Theme\Utils\is_amp;

if ( ! function_exists( 'frontend_enqueue_assets' ) ) {
	/**
	 * Enqueues frontend assets
	 */
	function frontend_enqueue_assets() {

		wp_enqueue_style(
			'frontend-shared-styles',
			get_template_directory_uri() . '/dist/styles/' . IDG_BASE_THEME_SHARED_CSS,
			[],
			filemtime( get_template_directory() . '/dist/styles/' . IDG_BASE_THEME_SHARED_CSS ),
			'all'
		);

		wp_enqueue_style(
			'frontend-styles',
			get_template_directory_uri() . '/dist/styles/' . IDG_BASE_THEME_BUNDLE_CSS,
			[],
			filemtime( get_template_directory() . '/dist/styles/' . IDG_BASE_THEME_BUNDLE_CSS ),
			'all'
		);

		if ( ! is_admin() ) {
			wp_register_script(
				'frontend-scripts',
				get_template_directory_uri() . '/dist/scripts/' . IDG_BASE_THEME_BUNDLE_JS,
				[],
				filemtime( get_template_directory() . '/dist/scripts/' . IDG_BASE_THEME_BUNDLE_JS ),
				true
			);
			wp_enqueue_script( 'frontend-scripts' );
		}

		wp_enqueue_style( 'frontend-styles', get_template_directory_uri() . '/dist/styles/' . IDG_BASE_THEME_BUNDLE_CSS, [], filemtime( get_template_directory() . '/dist/styles/' . IDG_BASE_THEME_BUNDLE_CSS ), 'all' );

		$legacy_data = get_post_meta( get_the_ID(), 'old_id_in_onecms' );

		if ( ! empty( $legacy_data[0] ) ) {
			wp_enqueue_script( 'idg-legacy-colorbox-script', 'https://cdnjs.cloudflare.com/ajax/libs/jquery.colorbox/1.6.4/jquery.colorbox-min.js', [], '1.6.4', false );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'frontend_enqueue_assets' );

if ( ! function_exists( 'idg_base_theme_typekit_script' ) ) {
	/**
	 * Enqueues typekit script
	 */
	function idg_base_theme_typekit_script() {
		// Checks we aren't running a child theme.
		if ( ! is_child_theme() ) {
			wp_enqueue_style( 'typekit-styles', 'https://use.typekit.net/jtt0awt.css', [], '1.0.0' );
		}
	}
}
add_action( 'wp_head', 'idg_base_theme_typekit_script' );
add_action( 'admin_head', 'idg_base_theme_typekit_script' );

if ( ! function_exists( 'idg_base_theme_google_search_script' ) ) {
	/**
	 * Enqueues Google Search script
	 */
	function idg_base_theme_google_search_script() {
		// Gets search details.
		$search_details = cf_get_value( 'global_settings', 'google_search', 'google_programmable_search' );

		// Checks we have an engine id & page to direct search to.
		if ( ! empty( $search_details['engine_id'] ) && ! empty( $search_details['slug'] ) ) {
			wp_register_script(
				'google-search-script',
				'https://cse.google.com/cse.js?cx=' . esc_attr(
					$search_details['engine_id']
				),
				[],
				1,
				true
			);
			wp_enqueue_script( 'google-search-script' );

			wp_register_script( 'google-search-placeholder', false, [], 1, true );
			wp_enqueue_script( 'google-search-placeholder' );
			wp_add_inline_script(
				'google-search-placeholder',
				'window.onload = function(){ document.getElementById("gsc-i-id1").placeholder = "' . esc_attr( $search_details['title'] ? $search_details['title'] : __( 'Search', 'idg-base-theme' ) ) . '"; }'
			);
		}
	}
}
add_action( 'wp_head', 'idg_base_theme_google_search_script' );

if ( ! function_exists( 'idg_base_theme_var_setup' ) ) {
	/**
	 * Adds theme variables to the header
	 */
	function idg_base_theme_var_setup() {

		// Checks we aren't running a child theme.
		if ( ! is_child_theme() ) {

			?>
		<style>
			:root {
				/* Base. */
				--base--font-family: "proxima-nova", sans-serif;
				--base--font-color: #111;

				/* Blockquote. */
				--blockquote--font-family: "proxima-nova", sans-serif;
				--blockquote--font-color: #4d4d4d;
				--blockquote--border-color: #5b7186;
				--blockquote--font-size: 20px;
				--blockquote--line-height: 28px;
				--blockquote--citation-color: #111;
				--blockquote--citation-font-size: 16px;
				--blockquote--citation-line-height: 24px;

				/* Pullquote */
				--pullquote--max-width: 617px;
				--pullquote--quotation-color: #2699cd;
				--pullquote--quotation-font-size: 7rem;
				--pullquote--cite-color: #111;
				--pullquote--cite-font-size: 16px;
				--pullquote--text-color: #4d4d4d;
				--pullquote--text-font-size: 28px;
				--pullquote--quotation-font-weight: 400;

				/* Social Links */
				--social-link-color: #39c;

				/* Text Link. */
				--text-link--font-color: #2a7da7;
				--text-link--hover--font-color: #2a7da7;
				--text-link--active--font-color: #2a7da7;
				--text-link--visited--font-color: #2a7da7;

				/* Skip Link */
				--skip-link--font-color: #fff;
				--skip-link--background-color: #3a3a3a;

				/* Heading. */
				--heading--font-family: "proxima-nova", sans-serif;
				--heading--font-color: #111;
				--heading--link--font-color: #2a7da7;

				/** Block - Price Comparison */
				--price-comparison--border-color: #2A7DA7;
				--price-comparison--header--font-family: "proxima-nova-condensed", sans-serif;
				--price-comparison--header--font-color: #fff;
				--price-comparison--header--letter-spacing: 0.78px;
				--price-comparison--header--background-color: #2A7DA7;
				--price-comparison--record--font-family: "proxima-nova", sans-serif;
				--price-comparison--record--border-color: #C7C7C7;
				--price-comparison--delivery-text--font-color: #3E3E3E;
				--price-comparison--view-button--background-color: #21873A;
				--price-comparison--view-button--letter-spacing: 0.72px;
				--price-comparison--footer--font-color: #666666;
				--price-comparison--view-more-button--font-color: #2A7DA7;

				/** Block - Product chart */
				--product-chart--title--font-family: "proxima-nova", sans-serif;
				--product-chart--information-label--font-family: "proxima-nova", sans-serif;
				--product-chart--information-value--font-color: #2A7DA7;
				--product-chart--review-link--font-color: #2A7DA7;
				--product-chart--description--font-color: #4D4D4D;
				--product-chart--separator--background-color: #404040;

				/** Block - Product widget */
				--product-widget--border-color: #878787;
				--product-widget--block-title--background-color: #2A7DA7;
				--product-widget--block-title--font-color: #fff;
				--product-widget--block-title--letter-spacing: 0.78px;
				--product-widget--block-title--font-family: "proxima-nova-condensed", sans-serif;
				--product-widget--title--font-family: "proxima-nova", sans-serif;
				--product-widget--title--font-color: #111111;
				--product-widget--information-label--font-family: "proxima-nova", sans-serif;
				--product-widget--information-value--font-color: #2A7DA7;

				/* Button Primary. */
				--button-primary--font-color: #fff;
				--button-primary--border-color: #2a7da7;
				--button-primary--background-color: #2a7da7;
				--button-primary--enter--font-color: #fff;
				--button-primary--enter--border-color: #3f8ab0;
				--button-primary--enter--background-color: #3f8ab0;
				--button-primary--hover--font-color: #fff;
				--button-primary--hover--border-color: #3f8ab0;
				--button-primary--hover--background-color: #3f8ab0;
				--button-primary--visited--font-color: #fff;
				--button-primary--visited--border-color: #2a7da7;
				--button-primary--visited--background-color: #2a7da7;
				--button-primary--border-radius: 4px;

				/* Button Outline. */
				--button-outline--font-color: #2a7da7;
				--button-outline--border-color: #2a7da7;
				--button-outline--enter--font-color: #fff;
				--button-outline--enter--border-color: #2a7da7;
				--button-outline--enter--background-color: #2a7da7;
				--button-outline--border-radius: 4px;

				/* Footer. */
				--footer--background-color: #3a3a3a;
				--footer--border-color: #d8d8d8;
				--footer--logo-width: 200px;

				/* Navigation Primary. */
				--navigation-primary--font-family: "proxima-nova", sans-serif;
				--navigation-primary--font-color: #111;
				--navigation-primary--hover--font-color: #2a7da7;
				--navigation-primary--background-color: #39c;
				--navigation-primary--logo-width: 140px;

				/* Navigation Logo Bar. */
				--navigation-logo-bar--background-color: #39c;
				--navigation-logo-bar--logo-width: 235px;

				/* Navigation Secondary. */
				--navigation-secondary--font-family: "proxima-nova", sans-serif;
				--navigation-secondary--font-color: #4d4d4d;
				--navigation-secondary--title--font-family: "proxima-nova-condensed", sans-serif;
				--navigation-secondary--title--font-color: #db7900;
				--navigation-secondary--border-color: #d8d8d8;
				--navigation-secondary--social-icon-color: #4d4d4d;

				/* Navigation Mobile. */
				--navigation-mobile--font-family: "proxima-nova", sans-serif;
				--navigation-mobile--font-color: #111;
				--navigation-mobile--scroll-bar-color: #39c;
				--navigation-mobile--button-color: #4d4d4d;
				--navigation-mobile--menu-button-color: #2a7da7;

				/* Hero. */
				--hero--title-color: #3a3a3a;
				--hero--border-color: #e8e8e8;

				/* Article Feed */
				--articleFeed--border-color: #dedede;
				--articleFeed--meta--font-color: #4d4d4d;

				/* Author Meta. */
				--meta--posted-on--font-color: #757575;

				/* Single Article. */
				--single-article--border-color: #cccccc;
				--single-article--bio--font-color: #4d4d4d;
				--single-article--social--font-color: #888888;
				--single-article--first-byline-border-color: #111;
				--single-article--second-byline-border-color: #888;

				/* Figcaptions. */
				--figcaption--font-family: "proxima-nova", sans-serif;
				--figcaption--font-color: #4d4d4d;

				/* Image. */
				--image--border-color: #ccc;
				--imagecredit--font-color: #757575;

				/* Eyebrows. */
				--eyebrow--default--font-family: "proxima-nova-condensed", sans-serif;
				--eyebrow--default--font-color: #5b7186;
				--eyebrow--default--font-weight: 700;
				--eyebrow--default--letter-spacing: 0.4px;

				--eyebrow--sponsered--font-family: "proxima-nova-condensed", sans-serif;
				--eyebrow--sponsered--font-color: #ac1f2b;
				--eyebrow--sponsered--font-weight: 700;
				--eyebrow--sponsered--letter-spacing: 0.4px;

				/* Nativo / sponsored content eyebrow color */
				--eyebrow--sponsered--content--font-color: #b95804;

				--eyebrow--sponsered--dealpost--font-family: "proxima-nova", sans-serif;
				--eyebrow--sponsered--dealpost--font-color: #4d4d4d;
				--eyebrow--sponsered--dealpost--font-size: 13px;
				--eyebrow--sponsered--dealpost--letter-spacing: 0;
				--eyebrow--sponsered--dealpost--line-height: 20px;

				--hero-eyebrow--default--font-family: "proxima-nova-condensed", sans-serif;
				--hero-eyebrow--default--font-color: #fff;
				--hero-eyebrow--default--font-weight: 500;
				--hero-eyebrow--default--letter-spacing: auto;
				--hero-eyebrow--default--background-color: #3a3a3a;

				--eyebrow--sponsored--sponsored-by--font-color: #111111;
				--eyebrow--sponsered--sponsored-by--font-family: "proxima-nova", sans-serif;
				--eyebrow--sponsored--sponsored-by--font-size:  14px;
				--eyebrow--sponsored--sponsored-by--letter-spacing: 0;
				--eyebrow--sponsored--sponsored-by--line-height: 20px;

				/* Tab Navigation */
				--tab-navigation--background-color: transparent;
				--tab-navigation--font-family: "proxima-nova", sans-serif;
				--tab-navigation--font-color: #4D4D4D;
				--tab-navigation--default--border-color: #D8D8D8;
				--tab-navigation--active--border-color: #39c;

				--tab-navigation--text-hover--border-radius: 2px;
				--tab-navigation--text-hover--font-color: #297BA8;
				--tab-navigation--text-hover--background-color: #E5F2FA;
				--tab-navigation--text-active--font-color: #297BA8;
				--tab-navigation--text-active--background-color: #E5F2FA;
				--tab-navigation--text-visited--font-color: #297BA8;
				--tab-navigation--text-visited--background-color: #E5F2FA;

				--tab-navigation--button--font-color: #fff;
				--tab-navigation--button--border-color: #2a7da7;
				--tab-navigation--button--background-color: #2a7da7;
				--tab-navigation--button--hover--font-color: #fff;
				--tab-navigation--button--hover--border-color: #3f8ab0;
				--tab-navigation--button--hover--background-color: #3f8ab0;
				--tab-navigation--button--visited--font-color: #fff;
				--tab-navigation--button--visited--border-color: #2a7da7;
				--tab-navigation--button--visited--background-color: #2a7da7;
				--tab-navigation--button--border-radius: 2px;

				--tab-navigation--group--background-color: #fff;

				/* Sponsorship Header. */
				--sponsorship-header--font-color: #4d4d4d;

				/* Sponsored Links */
				--sponsored-links--title--font-color: #888888;
				--sponsored-links--border-color: #d8d8d8;

				/* Google Search. */
				--google-search--title--font-family: "proxima-nova", sans-serif;
				--google-search--title--font-color: #111;
				--google-search--snippet--font-family: "proxima-nova", sans-serif;
				--google-search--snippet--font-color: #4d4d4d;

				/* JW Player. */
				--jw-player-border-color: #3eafe9;
				--jw-player-title-color: #3eafe9;
				--jw-player-close-btn-color: #111;

				<?php
					$player_branding = cf_get_value( 'third_party', 'jw_player', 'config.player_branding' );

				if ( ! empty( $player_branding ) ) {
					printf( '--jw-player-branding: url("%s");', esc_attr( $player_branding ) );
				}
				?>
			}
		</style>
			<?php
		}
	}
}
add_action( 'wp_head', 'idg_base_theme_var_setup' );
add_action( 'admin_head', 'idg_base_theme_var_setup' );

if ( ! function_exists( 'idg_base_theme_amazon_insights_script' ) ) {
	/**
	 * Enqueues Amazon Content Insights
	 */
	function idg_base_theme_amazon_insights_script() {
		?>
		<div id="amzn-assoc-ad-10622f81-ee09-465f-830a-63c3cbbab39c"></div>
		<script async src="https://z-na.associates-amazon.com/onetag/v2?MarketPlace=US&instanceId=10622f81-ee09-465f-830a-63c3cbbab39c"></script>
		<?php
	}
}
add_action( 'wp_footer', 'idg_base_theme_amazon_insights_script' );
