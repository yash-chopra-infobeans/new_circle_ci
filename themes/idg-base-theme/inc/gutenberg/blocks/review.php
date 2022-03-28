<?php

use IDG\Products\Article;

if ( ! function_exists( 'idg_render_review' ) ) {
	/**
	 * Render the review block.
	 *
	 * @param array  $attributes Current block attributes.
	 * @param string $content Content from JS save function.
	 * @return mixed
	 */
	function idg_render_review( $attributes, $content ) {
		if ( is_admin() ) {
			return '';
		}

		if ( doing_filter( 'get_the_excerpt' ) ) {
			return false;
		}

		$editors_choice_logo    = cf_get_value( 'global_settings', 'articles', 'reviews.editors_choice_logo' );
		$has_comparison_product = isset( $attributes['comparisonProductId'] ) && $attributes['comparisonProductId'];

		ob_start();

		echo '<div id="review-body" class="review">';

		if ( $attributes['editorsChoice'] && ! $has_comparison_product ) {
			if ( $editors_choice_logo ) {
				printf(
					'<img class="review-logo" src="%s" alt="%s" />',
					esc_url( $editors_choice_logo ),
					esc_attr( __( "Editors' Choice" ) )
				);
			} else {
				printf(
					'<div class="review-banner">%s</div>',
					esc_html( __( "Editors' Choice" ) )
				);
			}
		}

		printf(
			'<h2 class="review-title">%s</h2>',
			esc_html( $attributes['heading'] ?? __( 'At a glance' ) )
		);

		if ( ! $has_comparison_product && $attributes['rating'] ) {
			echo '<h3 class="review-subTitle">Expert\'s Rating</h3>';

			printf(
				'<div class="starRating" style="--rating: %s;" aria-label="%s"></div>',
				esc_attr( $attributes['rating'] ),
				esc_attr( __( 'Rating of this product is ' ) . $attributes['rating'] . __( ' out of 5.' ) )
			);
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $content;

		echo '</div>';

		if ( isset( $attributes['primaryProductId'] ) && $attributes['primaryProductId'] && ! $attributes['comparisonProductId'] ) {
			// Already cached at this point.
			$products = Article::get_products( get_the_ID() );
			$price    = $products[ $attributes['primaryProductId'] ]['geo_info']->pricing->price_options ?: false;

			if ( isset( $price ) && ! empty( $price ) && $price ) {
				printf(
					'<h3 class="review-price">%s</h3><p>%s</p>',
					esc_html( $attributes['pricingTitle'] ?: __( 'Price When Reviewed', 'idg-base-theme' ) ),
					esc_html( $price )
				);
			}

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo idg_render_price_comparison(
				[
					'productId'     => $attributes['primaryProductId'],
					'linksInNewTab' => true,
					'footerText'    => __( 'Price comparison from over 24,000 stores worldwide', 'idg-base-theme' ),
					'title'         => $attributes['bestPricingTitle'] ?: __( 'Best Prices Today', 'idg-base-theme' ),
					'position_id'   => '001',
					'position'      => 'Price Comparison Top',
				]
			);
		}

		return ob_get_clean();
	}
}
