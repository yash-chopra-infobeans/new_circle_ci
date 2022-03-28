<?php
/** 
 * Product Widget Block Render.
 *
 * @package idg-base-theme
 */

use IDG\Products\Article;

use function IDG\Base_Theme\Utils\array_to_attrs;
use function IDG\Base_Theme\Utils\is_amp;

if ( ! function_exists( 'idg_render_product_widget' ) ) {
	/**
	 * Callback function to render product-widget block on frontend.
	 *
	 * @param array $attributes Block attributes.
	 *
	 * @return string HTML of the block.
	 */
	function idg_render_product_widget( $attributes ) {
		if ( is_admin() ) {
			return '';
		}

		if ( empty( $attributes['productId'] ) || empty( absint( $attributes['productId'] ) ) ) {
			return '';
		}

		$product_id = absint( $attributes['productId'] );
		$post_id    = get_the_ID();

		// Cached at this point as any products will already have been added to the datalayer.
		$products              = Article::get_products( get_the_ID() );
		$product               = $products[ $product_id ] ?? false;
		$amp_products_endpoint = Article::get_amp_products_endpoint() . $post_id . '/' . $product_id . '/pw';

		if ( ! $product ) {
			return '';
		}

		// Unpublished reviews won't exist and this array is already in correct order.
		$product_review = $product['reviews'][0] ?? false;
		if ( isset( $product['reviews'] ) && ! empty( $product['reviews'] ) ) {
			foreach ( $product['reviews'] as $key => $review ) {
				if ( true === $review['active'] && 'publish' === $review['status'] ) {
					$activeKey = $key;
					break;
				}
			}
			if ( ! empty( $activeKey ) ) {
				$product_review = $product['reviews'][ $activeKey ];
			}
		}
		$pricing = idg_get_product_pricing( $product );

		$pricing_details = [];

		if ( ! empty( $pricing ) && is_array( $pricing ) ) {
			foreach ( $pricing as $index => $pricing_detail ) {
				if ( ( $index > 2 ) || ( false === $pricing_detail['inStock'] ) ) { // Out of stock items will always be at bottom.
					break;
				}

				$pricing_data = [
					'string'         => '',
					'link'           => $pricing_detail['link'] ?? '',
					'link_data_attr' => [],
				];

				$pricing_data['string'] = $pricing_detail['price'] ?? '';

				if ( ! empty( $pricing_data['string'] ) && ! empty( $pricing_detail['vendor'] ) ) {
					$pricing_data['string'] .= sprintf(
						/* translators: %1$s: Vendor name */
						' at %1$s',
						$pricing_detail['vendor']
					);

					$pricing_details[] = $pricing_data;
				}
			}
		}

		$open_product_link_in_new_tab = (
			! empty( $attributes['linksInNewTab'] ) &&
			( true === $attributes['linksInNewTab'] )
		);

		$block_classes = [
			'wp-block-product-widget-block',
			'product-widget',
		];

		if ( ! empty( $attributes['isHalfWidth'] ) && true === $attributes['isHalfWidth'] ) {
			$block_classes[] = 'is-half-width';
		}

		if ( ! empty( $attributes['isFloatRight'] ) && true === $attributes['isFloatRight'] ) {
			$block_classes[] = 'is-float-right';
		}

		ob_start();

		?>

		<div class="<?php echo esc_attr( implode( ' ', $block_classes ) ); ?>">
			<div class="product-widget__block-title-wrapper">
				<h4 class="product-widget__block-title">
					<?php echo esc_html( $attributes['blockTitle'] ); ?>
				</h4>
			</div>

			<div class="product-widget__content-wrapper">
				<?php if ( ! empty( $product['name'] ) ) : ?>
					<div class="product-widget__title-wrapper">
						<h3 class="product-widget__title"><?php echo esc_html( $product['name'] ); ?></h3>
					</div>
				<?php endif; ?>

				<?php if ( isset( $product['featured_media'] ) ) : ?>
					<div class="product-widget__image-outer-wrapper">
						<div class="product-widget__image-wrapper">
							<img
								class="product-widget__image"
								src="<?php echo esc_url( $product['featured_media']->source_url ); ?>"
								loading="lazy"
								alt="<?php echo esc_attr( $product['name'] ); ?>"
							/>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( $product_review && is_array( $product_review ) ) : ?>
					<div class="review product-widget__review-details">
						<?php

						if ( isset( $product_review['editors_choice'] ) && $product_review['editors_choice'] && 'primary' === $product_review['type'] ) {
							$logo = cf_get_value( 'global_settings', 'articles', 'reviews.editors_choice_logo' );

							if ( $logo ) {
								printf(
									'<img
										class="product-widget__review-details--editors-choice-logo"
										src="%s"
										alt="%s"
										loading="lazy"
									/>',
									esc_url( $logo ),
									esc_attr( __( "Editors' Choice" ) )
								);
							} else {
								printf(
									'<div class="review-product-banner">%s</div>',
									esc_html( __( "Editors' Choice" ) )
								);
							}
						}

						if ( ( $product_review && isset( $product_review['rating'] ) ) || $product_review['permalink'] ) :
							?>
							<div class="product-widget__rating-and-review-link">
								<?php
								if ( isset( $product_review['rating'] ) && $product_review['rating'] ) {
									printf(
										'<div class="product-widget__review-details--rating">
											<div
												class="starRating"
												style="--rating: %s;"
												aria-label="Rating of this product is %s out of 5"
											></div>
										</div>',
										esc_attr( $product_review['rating'] ),
										esc_attr( $product_review['rating'] )
									);
								}
								if ( $product_review['permalink'] ) { 
									?>
									<a class="product-widget__review-link" href="<?php echo esc_url( $product_review['permalink'] ); ?>" <?php echo $open_product_link_in_new_tab ? "target='_blank'" : ''; ?>><?php echo esc_html_e( 'Read our review' ); ?></a>
									<?php	
								}
								?>
							</div>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<div class="product-widget__information">
				<?php
				if ( is_amp() ) { 
					?>
						<amp-list noloading binding="no" src="<?php echo esc_url( $amp_products_endpoint ); ?>" width="auto" height="50" layout="fixed-height">
							<template type="amp-mustache">
								<div class="product-widget__information--rrp-wrapper">
									{{#rrp_label}}
										<span class="product-widget__information--rrp-label">{{rrp_label}}</span>
									{{/rrp_label}}
									{{#rrp_price}}
										<span class="product-widget__information--rrp-value">{{rrp_price}}</span>
									{{/rrp_price}}
								</div>
							</template>
							<div placeholder>
								<div class="product-widget__information--rrp-wrapper placeholder-section small">
									<span class="product-widget__information--rrp-label"></span>
									<span class="product-widget__information--rrp-value"></span>
								</div>
							</div>
							<div fallback></div>
						</amp-list>
						<?php
				} else {
					if ( ! empty( $product['geo_info']->pricing ?: '' ) ) :
						$price_value = function_exists( 'get_price_with_currency' ) ? get_price_with_currency( (array) $product['geo_info']->pricing ) : [];
						$rrp_pricing = $product['geo_info']->pricing->price_options ?: $price_value ?? '';
						?>
							<div class="product-widget__information--rrp-wrapper">
								<span class="product-widget__information--rrp-label">
							<?php
							if ( $rrp_pricing && ! empty( $rrp_pricing ) ) {
								echo esc_html( $product['labels']->rrp_field_label ?? '' ) . ':';
							}
							?>
								</span>
								<span class="product-widget__information--rrp-value">
								<?php echo esc_html( $rrp_pricing ?? '' ); ?>
								</span>
							</div>
						<?php 
						endif; 
				} 
				?>

					<?php if ( ! empty( $pricing_details ) ) : ?>
						<div class="product-widget__pricing-details  <?php echo is_amp() ? ' amp-product-chart-item' : ''; ?>">
							<?php if ( ! is_amp() ) : ?>
								<span class="product-widget__pricing-details--label">
									<?php echo esc_html( $product['labels']->best_prices_field_label ); ?>:
								</span>
							<?php endif; ?>
							<span class="product-widget__pricing-details--links-wrapper">
								<?php
								if ( is_amp() ) { 
									?>
									<amp-list noloading binding="no" src="<?php echo esc_url( $amp_products_endpoint ); ?>" width="auto" height="50" layout="fixed-height">
										<template type="amp-mustache">
											<span>
												{{#string}}
													<span class="product-widget__pricing-details--label">
														{{best_price_label}}
													</span>
												{{/string}}
												{{#separator}}
													<span class="amp-bar"> | </span>
												{{/separator}}
												{{#url}}
													<a class="product-widget__pricing-details--link"
													<?php echo esc_attr( array_to_attrs( $product['attributes'] ) ); ?>
													<?php echo $open_product_link_in_new_tab ? 'target="_blank"' : ''; ?> 
													data-vars-outbound-link="{{url}}"
													data-vars-link-position-id='005'
													data-vars-link-position='Product Sidebar'
													data-amp-link="amp-list-widget"
													href="{{url}}" >{{string}}</a>
												{{/url}}
												{{^url}}
													<span class="product-widget__pricing-details--link">{{string}}</span>
												{{/url}}
											</span>
										</template>
										<div placeholder>
											<div class="placeholder-section medium">
												<span class="product-widget__pricing-details--label"></span>
												<span class="amp-bar"></span>
												<a class="product-widget__pricing-details--link"></a>
											</div>
										</div>
										<div fallback></div>
									</amp-list>
									<?php 
								} else {
									foreach ( $pricing_details as $index => $pricing_detail ) {
										if ( ( 0 !== $index ) ) { 
											?>
											<span class="amp-bar"><?php echo esc_html( ' | ' ); ?></span>
																		<?php
										}
	
										if ( ! empty( $pricing_detail['link'] ) ) {
											$link_attributes = array_merge(
												$product['attributes'],
												[
													'data-vars-link-position-id' => '005',
													'data-vars-link-position'    => 'Product Sidebar',
													'data-vars-outbound-link'    => $pricing_detail['link'],
												]
											);
	
											printf(
												'<a class="product-widget__pricing-details--link" href="%s" %s %s>%s</a>',
												esc_url( $pricing_detail['link'] ),
												$open_product_link_in_new_tab ? 'target="_blank"' : '',
                                                // phpcs:ignore
                                                array_to_attrs( $link_attributes ),
												esc_html( $pricing_detail['string'] )
											);
										} else {
											printf(
												'<span class="product-widget__pricing-details--link">%s</a>',
												esc_html( $pricing_detail['string'] )
											);
										}
									}
								} 
								?>
							</span>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<?php

		return (string) ob_get_clean();
	}
}
