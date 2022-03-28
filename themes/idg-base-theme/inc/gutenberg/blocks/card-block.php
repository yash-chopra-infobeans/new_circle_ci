<?php

if ( ! function_exists( 'idg_render_card_block' ) ) {
	/**
	 * Render the card block
	 *
	 * @param array $attributes current block attributes.
	 * @return string
	 */
	function idg_render_card_block( $attributes ) {
		$items       = $attributes['items'];
		$block_title = $attributes['blockTitle'];
		$style       = $attributes['blockStyle'];
		$style_attr  = '';
		$cta_link    = $attributes['ctaLink'];
		$cta_title   = $attributes['ctaTitle'];
		$cta_style   = $attributes['ctaStyle'];
		$cta_attr    = 'cta-link-button';

		if ( ! isset( $items ) || empty( $items ) ) {
			return '';
		}

		if ( 'block-title-style' === $style ) {
			$style_attr = 'card-block--style-block';
		}

		if ( 'cta-link-style' === $cta_style ) {
			$cta_attr = 'cta-link--style';
		}

		ob_start();

		printf( '<div class="card-block %s">', esc_html( $style_attr ) );

		if ( ! empty( $block_title ) ) {
			printf(
				'<div class="block-title">%s</div>',
				esc_html( $block_title )
			);
		}
		foreach ( $items as $item ) {
			$title   = trim( $item ['card_content_title'] );
			$content = $item['card_content_text'];
			$eyebrow = $item['card_content_eyebrow'];

			printf( '<div class="card-items">' );

			if (
				( ! empty( $title ) || ! empty( $content ) || ! empty( $eyebrow ) )
			) {
				if ( '' !== $eyebrow ) {
					printf(
						'<div class="card-content-eyebrow">%s</div>',
						esc_html( $eyebrow )
					);
				}
				printf(
					'<h2 class="card-title">%s</h2>',
					esc_html( $title )
				);
				printf(
					'<p class="card-content">%s</p>',
					wp_kses_post( $content )
				);
			}

			printf( '<hr />' );
			printf( '</div>' );
		}

		if ( ! empty( $cta_link ) && ! empty( $cta_title ) ) {
			printf(
				'<div class="%s"><a href="%s" class="cta-button">%s</a></div>',
				esc_attr( $cta_attr ),
				esc_html( $cta_link ),
				esc_html( $cta_title )
			);
		}
		printf( '</div>' );


		return ob_get_clean();
	}
}
