<?php
/**
 * Contain a layout for sponsorship.
 *
 * @package idg-base-theme
 */

$sponsorship    = idg_base_theme_get_sponsorship( get_the_ID() );
$series_name    = $sponsorship['series_name'];
$intro_text     = $sponsorship['intro_text'];
$brand_color    = $sponsorship['brand_color'];
$brand_url      = $sponsorship['brand_url'];
$brand_logo_id  = $sponsorship['brand_logo_id'];
$brand_logo_arr = $sponsorship['brand_logo_arr'];
$brand_logo_url = $sponsorship['brand_logo_url'];
?>

<div class='sponsorship-header'>
	<?php
	if ( ! empty( $brand_url ) && ! empty( $series_name ) ) {
		printf(
			'<div class="sponsorship-header-headline sponsorship-header-headline--mobile"><a href="%s" style="color:%s" target="_blank" rel="noopener noreferrer">%s</a></div>',
			esc_url( $brand_url ),
			esc_attr( $brand_color ) ? esc_attr( $brand_color ) : 'var(--heading--font-color)',
			esc_attr( $series_name )
		);
	} elseif ( ! empty( $series_name ) ) {
		printf(
			'<div class="sponsorship-header-headline sponsorship-header-headline--mobile"><span style="color:%s">%s</span></div>',
			esc_attr( $brand_color ) ? esc_attr( $brand_color ) : 'var(--heading--font-color)',
			esc_attr( $series_name )
		);
	}
	?>
	<div class='sponsorship-header-wrap'>
		<?php

		if ( ! empty( $brand_logo_url ) && ! empty( $brand_url ) ) {
			printf(
				'<a class="sponsorship-header-logo" href="%s" target="_blank" rel="noopener noreferrer"><img class="sponsorship-header-logo-img" src="%s"></a>',
				esc_url( $brand_url ),
				esc_url( $brand_logo_url )
			);
		} elseif ( ! empty( $brand_logo_url ) ) {
			printf(
				'<div class="sponsorship-header-logo"><img class="sponsorship-header-logo-img" src="%s"></div>',
				esc_url( $brand_logo_url )
			);
		}

		if ( ! empty( $series_name ) || ! empty( $intro_text ) ) {
			printf( '<div class="sponsorship-header-text">' );

			if ( ! empty( $brand_url ) && ! empty( $series_name ) ) {
						printf(
							'<div class="sponsorship-header-headline sponsorship-header-headline--desktop"><a href="%s" style="color:%s" target="_blank" rel="noopener noreferrer">%s</a></div>',
							esc_url( $brand_url ),
							esc_attr( $brand_color ) ? esc_attr( $brand_color ) : 'var(--heading--font-color)',
							esc_attr( $series_name )
						);
			} elseif ( ! empty( $series_name ) ) {
						printf(
							'<div class="sponsorship-header-headline sponsorship-header-headline--desktop"><span style="color:%s">%s</span></div>',
							esc_attr( $brand_color ) ? esc_attr( $brand_color ) : 'var(--heading--font-color)',
							esc_attr( $series_name )
						);
			}

			if ( ! empty( $intro_text ) ) {
					printf(
						'<div class="sponsorship-header-paragraph">%s</div>',
						esc_attr( $intro_text )
					);
			}

			printf( '</div>' );
		}
		?>
	</div>
</div>

<hr class="wp-block-separator" />
