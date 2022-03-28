<?php
if ( ! function_exists( 'cio_child_theme_typekit_script' ) ) {
	/**
	 * Enqueues typekit script
	 */
	function cio_child_theme_typekit_script() {
		wp_enqueue_style( 'typekit-styles', 'https://use.typekit.net/rar2qld.css', [], '1.0.0' );
	}
}
add_action( 'wp_head', 'cio_child_theme_typekit_script' );
add_action( 'admin_head', 'cio_child_theme_typekit_script' );

if ( ! function_exists( 'cio_child_theme_var_setup' ) ) {
	/**
	 * Add theme variables to the header
	 */
	function cio_child_theme_var_setup() {
		?>
		<style>
			:root {
				/* Base. */
				--base--font-family: "fira-sans", sans-serif;
				--base--font-color: #322a2a;

				/* Blockquote. */
				--blockquote--font-family: "fira-sans", sans-serif;
				--blockquote--font-color: #ed1b24;
				--blockquote--border-color: #ed1b24;

				/* Text Link. */
				--text-link--font-color: #3C807C;
				--text-link--hover--font-color: #0979C3;
				--text-link--active--font-color: #0979C3;
				--text-link--visited--font-color: #3C807C;

				/* Skip Link */
				--skip-link--font-color: #fff;
				--skip-link--background-color: #3a3a3a;

				/* Heading. */
				--heading--font-family: "fira-sans", sans-serif;
				--heading--font-color: #141414;
				--heading--link--font-color: #3C807C;

				/* Button Primary. */
				--button-primary--font-color: #fff;
				--button-primary--border-color: #ed1b24;
				--button-primary--background-color: #ed1b24;
				--button-primary--enter--font-color: #fff;
				--button-primary--enter--border-color: #ef323a;
				--button-primary--enter--background-color: #ef323a;
				--button-primary--hover--font-color: #fff;
				--button-primary--hover--border-color: #ef323a;
				--button-primary--hover--background-color: #ef323a;
				--button-primary--visited--font-color: #fff;
				--button-primary--visited--border-color: #ed1b24;
				--button-primary--visited--background-color: #ed1b24;
				--button-primary--border-radius: 4px;

				/* Button Outline. */
				--button-outline--font-color: #ed1b24;
				--button-outline--border-color: #ed1b24;
				--button-outline--enter--font-color: #fff;
				--button-outline--enter--border-color: #ed1b24;
				--button-outline--enter--background-color: #ed1b24;
				--button-outline--border-radius: 4px;

				/* Footer. */
				--footer--background-color: #000;
				--footer--border-color: #d8d8d8;
				--footer--logo-width: 150px;

				/* Navigation Primary. */
				--navigation-primary--font-family: "fira-sans", sans-serif;
				--navigation-primary--font-color: #322a2a;
				--navigation-primary--hover--font-color: #2a7da7;
				--navigation-primary--background-color: #ed1b24;
				--navigation-primary--logo-width: 65px;

				/* Navigation Logo Bar. */
				--navigation-logo-bar--background-color: #ed1b24;
				--navigation-logo-bar--logo-width: 130px;

				/* Navigation Secondary. */
				--navigation-secondary--font-family: "fira-sans", sans-serif;
				--navigation-secondary--font-color: #4d4d4d;
				--navigation-secondary--title--font-family: "fira-sans-condensed", sans-serif;
				--navigation-secondary--title--font-color: #db7900;
				--navigation-secondary--border-color: #d8d8d8;
				--navigation-secondary--social-icon-color: #4d4d4d;

				/* Navigation Mobile. */
				--navigation-mobile--font-family: "fira-sans", sans-serif;
				--navigation-mobile--font-color: #322a2a;
				--navigation-mobile--scroll-bar-color: #ed1b24;
				--navigation-mobile--button-color: #4d4d4d;

				/* Hero. */
				--hero--title-color: #3a3a3a;
				--hero--border-color: #e8e8e8;
				--hero--eyebrow-background-color: #3a3a3a;
				--hero--eyebrow-font-color: #fff;

				/* Article Feed */
				--articleFeed--border-color: #dedede;
				--articleFeed--meta--font-color: #4d4d4d;

				/* Author Meta. */
				--meta--posted-on--font-color: #757575;

				/* Single Article. */
				--single-article--border-color: #cccccc;

				/* Figcaptions. */
				--figcaption--font-family: "proxima-nova", sans-serif;
				--figcaption--font-color: #4D4D4D;
				--figcaption--border-color: #ccc;

				/* Eyebrows. */
				--eyebrow--default--font-family: "fira-sans", sans-serif;
				--eyebrow--default--font-color: #5b7186;
				--eyebrow--default--font-weight: 700;
				--eyebrow--default--letter-spacing: 0.4px;

				--eyebrow--sponsered--font-family: "fira-sans", sans-serif;
				--eyebrow--sponsered--font-color: #ac1f2b;
				--eyebrow--sponsered--font-weight: 700;
				--eyebrow--sponsered--letter-spacing: 0.4px;

				/* Nativo / sponsored content eyebrow color */
				--eyebrow--sponsered--content--font-color: #b95804;

				--eyebrow--sponsored--sponsored-by--font-color: #111111;
				--eyebrow--sponsered--sponsored-by--font-family: "fira-sans", sans-serif;
				--eyebrow--sponsored--sponsored-by--font-size:  14px;
				--eyebrow--sponsored--sponsored-by--letter-spacing: 0;
				--eyebrow--sponsored--sponsored-by--line-height: 20px;

				/* Tab Navigation CIO */
				--tab-navigation--background-color: #000;
				--tab-navigation--font-family: "proxima-nova", sans-serif;
				--tab-navigation--font-color: #D8D8D8;
				--tab-navigation--default--border-color: #D8D8D8;
				--tab-navigation--active--border-color: #ED1B24;

				--tab-navigation--text-hover--border-radius: 10px;
				--tab-navigation--text-hover--font-color: #000;
				--tab-navigation--text-hover--background-color: #E5F2FA;
				--tab-navigation--text-active--font-color: #000;
				--tab-navigation--text-active--background-color: #E5F2FA;
				--tab-navigation--text-visited--font-color: #000;
				--tab-navigation--text-visited--background-color: #E5F2FA;

				--tab-navigation--button--font-color: #fff;
				--tab-navigation--button--border-color: #ED1B24;
				--tab-navigation--button--background-color: #ED1B24;
				--tab-navigation--button--hover--font-color: #fff;
				--tab-navigation--button--hover--border-color: #ED1B24;
				--tab-navigation--button--hover--background-color: #ED1B24;
				--tab-navigation--button--visited--font-color: #fff;
				--tab-navigation--button--visited--border-color: #ED1B24;
				--tab-navigation--button--visited--background-color: #ED1B24;
				--tab-navigation--button--border-radius: 15px;

				--tab-navigation--group--background-color: #fff;

				/* JW Player. */
				--jw-player-border-color: #000;
				--jw-player-title-color: #000;
				--jw-player-close-btn-color: #000;

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
add_action( 'wp_head', 'cio_child_theme_var_setup' );
add_action( 'admin_head', 'cio_child_theme_var_setup' );
