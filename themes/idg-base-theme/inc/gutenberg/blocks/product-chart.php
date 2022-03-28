<?php
/**
 * Product Chart Block Content.
 *
 * @package idg-products
 */

use IDG\Publishing_Flow\Sites;
use IDG\Products\Article;
use function IDG\Base_Theme\Utils\array_to_attrs;
use function IDG\Base_Theme\Utils\is_amp;

/**
 * Checks if the function exists or not.
 * 
 * @SuppressWarnings(PHPMD).
 */
if ( ! function_exists( 'idg_render_product_chart' ) ) {
	/**
	 * Callback function to render product-chart block on frontend.
	 *
	 * @param array $attributes Block attributes.
	 *
	 * @return string HTML of the block.
	 */
	function idg_render_product_chart( $attributes ) {
		if ( is_admin() || empty( $attributes['productData'] ) || ! is_array( $attributes['productData'] ) ) {
			return '';
		}

		// Cached at this point as any products will already have been added to the datalayer.
		$products     = Article::get_products( get_the_ID() );
		$post_id      = get_the_ID();
		$product_data = [];

		ob_start();
		?>

		<div class="wp-block-product-chart product-chart">
		<?php
		foreach ( $attributes['productData'] as $product_atts ) :
			$product = $products[ $product_atts['productId'] ] ? $products[ $product_atts['productId'] ] : false;

			if ( ! $product ) {
				continue;
			}

			$title                 = $product_atts['titleOverride'] ? $product_atts['productTitle'] : $product['name'];
			$product_title         = isset( $product['name'] ) ? $product['name'] : '';
			$version               = $product_atts['version'] ? $product_atts['version'] : '1.0.0';
			$product_id            = $product_atts['productId'];
			$amp_products_endpoint = Article::get_amp_products_endpoint() . $post_id . '/' . $product_id . '/pc';           
			

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
			// If it is a comparison view, we shouldn't show the rating.
			$product_rating = ( isset( $product_review ) && 'comparison' === $product_review['type'] ) ? false : $product_review['rating'];
			// If there is a rating override, show that instead.
			$rating = $product_atts['ratingOverride'] ? $product_atts['productRating'] : $product_rating;

			$pricing = idg_get_product_pricing( $product );

			$open_new_tab = (
				! empty( $attributes['linksInNewTab'] ) &&
				( true === $attributes['linksInNewTab'] )
			);

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

			$content            = $product_atts['productContentInner'];
			$oldContent         = $product_atts['productContent'];
			$rank               = $product_atts['rank'] ?? '';
			$is_showing_rank    = isset( $attributes['isShowingRank'] ) && true === $attributes['isShowingRank'];
			$title_text         = $is_showing_rank ? "{$rank}.  {$title}" : $title;
			$product_image_size = strtolower( $product_atts['productImageSize'] );
			?>
			<div class="product-chart-separator"></div>
			<div class="wp-block-product-chart-item product-chart-item">
				<?php if ( ! empty( $title ) ) : ?>
					<div class="product-chart-item__title-wrapper">
						<h3
							class="product-chart-item__title-wrapper--title"
						>
							<?php echo esc_html( $title_text ); ?>
						</h3>
					</div>
				<?php endif; ?>

				<?php 
				if ( ! empty( $product_atts['productImage'] ) || isset( $product['featured_media'] ) ) :
					$image_url = ! empty( $product_atts['productImage'] ) ? $product_atts['productImage'] : $product['featured_media']->source_url; 
					?>
					<div
						class="product-chart-item__image-outer-wrapper
						product-chart-item__image-outer-wrapper--<?php echo esc_attr( $product_image_size ); ?>"
					>
						<div class="product-chart-item__image-wrapper">
							<img
								class="product-chart-item__image"
								alt="<?php echo esc_attr( $title ); ?>"
								src="<?php echo esc_url( $image_url ); ?>"
								loading="lazy"
							/>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( isset( $product_review ) && isset( $product_review['editors_choice'] ) || $rating ) : ?>
					<div class="review product-chart-item__review-details">
						<?php
						if ( $product_review['editors_choice'] && 'primary' === $product_review['type'] ) {
							// WordPress caches this anyway.
							$logo = cf_get_value( 'global_settings', 'articles', 'reviews.editors_choice_logo' );

							if ( $logo ) {
								printf(
									'<img
										class="product-chart-item__review-details--editors-choice-logo"
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

						if ( $rating ) {
							printf(
								'<div class="product-chart-item__review-details--ratin">
									<div
										class="starRating"
										style="--rating: %s;"
										aria-label="Rating of this product is %s out of 5"
									></div>
								</div>',
								esc_attr( $rating ),
								esc_attr( $rating )
							);
						}
						?>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $product['geo_info']->pricing ) || ! empty( $pricing_details ) ) : ?>
					<div class="product-chart-item__information <?php echo is_amp() ? ' amp-product-chart-item' : ''; ?>">
					<?php
					if ( is_amp() ) { 
						?>
							<amp-list noloading binding="no" src="<?php echo esc_url( $amp_products_endpoint ); ?>" width="auto" height="50" layout="fixed-height">
								<template type="amp-mustache">
									<div class="product-chart-item__information--rrp-wrapper">
										{{#rrp_label}}
											<span class="product-chart-item__information--rrp-label">{{rrp_label}}</span>
										{{/rrp_label}}
										{{#rrp_price}}
											<span class="product-chart-item__information--rrp-value">{{rrp_price}}</span>
										{{/rrp_price}}
									</div>
								</template>
								<div placeholder>
									<div class="product-chart-item__information--rrp-wrapper placeholder-section small">
										<span class="product-chart-item__information--rrp-label"></span>
										<span class="product-chart-item__information--rrp-value"></span>
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
								<div class="product-chart-item__information--rrp-wrapper">
									<span class="product-chart-item__information--rrp-label">

								<?php
								if ( $rrp_pricing && ! empty( $rrp_pricing ) ) {
									echo esc_html( $product['labels']->rrp_field_label ?? '' ) . ':';
								}
								?>
									</span>
									<span class="product-chart-item__information--rrp-value">
									<?php echo esc_html( $rrp_pricing ?? '' ); ?>
									</span>
								</div>
							<?php 
						endif; 
					} 
					?>

						<?php if ( ! empty( $pricing_details ) ) : ?>
							<div class="product-chart-item__pricing-details <?php echo is_amp() ? ' amp-product-chart-item' : ''; ?>">
								<?php if ( ! is_amp() ) : ?>
									<span class="product-chart-item__pricing-details--label">
										<?php echo esc_html( $product['labels']->best_prices_field_label ); ?>:
									</span>
								<?php endif; ?>
								<span class="product-chart-item__pricing-details--links-wrapper">
									<?php
									if ( is_amp() ) { 
										?>
										<amp-list noloading binding="no" src="<?php echo esc_url( $amp_products_endpoint ); ?>" width="auto" height="50" layout="fixed-height">
											<template type="amp-mustache">
												<span>
													{{#string}}
														<span class="product-chart-item__pricing-details--label">
															{{best_price_label}}
														</span>
													{{/string}}
													{{#separator}}
														<span class="amp-bar"> | </span>
													{{/separator}}
													{{#url}}
														<a class="product-chart-item__pricing-details--link"
														<?php echo esc_attr( array_to_attrs( $product['attributes'] ) ); ?>
														<?php echo $open_new_tab ? 'target="_blank"' : ''; ?>
														data-amp-link="amp-list-chart"
														data-vars-outbound-link="{{url}}"
														data-vars-link-position-id='003'
														data-vars-link-position='Product Chart'
														href="{{url}}" >{{string}}</a>
													{{/url}}
													{{^url}}
														<span class="product-chart-item__pricing-details--link">{{string}}</span>
													{{/url}}
												</span>
											</template>
											<div placeholder>
												<div class="placeholder-section medium">
													<span class="product-chart-item__pricing-details--label"></span>
													<span class="amp-bar"></span>
													<a class="product-chart-item__pricing-details--link" ></a>
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
														'data-vars-link-position-id' => '003',
														'data-vars-link-position'    => 'Product Chart',
														'data-vars-outbound-link'    => $pricing_detail['link'],
													]
												);

												if ( ! is_amp() ) { 
													printf(
														'<a class="product-chart-item__pricing-details--link" href="%s" %s %s>%s</a>',
														esc_url( $pricing_detail['link'] ),
														$open_new_tab ? 'target="_blank"' : '',
                                                        // phpcs:ignore
                                                        array_to_attrs( $link_attributes ),
														esc_html( $pricing_detail['string'] )
													);
												}
											} else {
												if ( ! is_amp() ) { 
													printf(
														'<span class="product-chart-item__pricing-details--link">%s</span>',
														esc_html( $pricing_detail['string'] )
													);
												}
											}
										}
									} 
									?>
								</span>
							</div>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php
				if ( $version === '1.0.0' || ! empty( $oldContent ) ) { 
					?>
					<p class="product-description">
						<?php echo wp_kses_post( $oldContent ); ?>
					</p>
					<?php
				} elseif ( $version === '1.1.0' && empty( $oldContent ) ) {
					if ( ! empty( $content ) ) { 
						?>
						<div class="product-content">
						<?php
						if ( is_array( $content ) ) {
							foreach ( $content as $innerblock ) { 
								?>
									<div class="product-content-item">
									<?php
									if ( $innerblock['name'] === 'core/embed' && isset( $innerblock['attributes']['url'] ) ) {
										$yt_video = check_yt_video( $innerblock['attributes']['url'] );
										if ( $yt_video['yes'] ) { 
											?>
												<iframe src='<?php echo esc_url( $yt_video['embed-url'] ); ?>' width='620' height='360' frameborder='0'></iframe>
																		<?php
										}
									} elseif ( $innerblock['name'] === 'idg-base-theme/jwplayer' && isset( $innerblock['attributes']['id'] ) ) {
                                        echo jw_player_block_render_callback( $innerblock['attributes'] ); // phpcs:ignore
									} elseif ( empty( $innerblock['originalContent'] ) ) {
										if ( $innerblock['name'] === 'core/image' ) { 
											?>
												<div class="extendedBlock-wrapper block-coreImage">
													<figure class="wp-block-image size-full is-resized">
														<img src="<?php echo esc_url( $innerblock['attributes']['url'] ); ?>" 
														width="<?php echo esc_attr( $innerblock['attributes']['width'] ); ?>"
														height="<?php echo esc_attr( $innerblock['attributes']['height'] ); ?>" />
													</figure>
													<p class="imageCredit"><?php echo esc_html( $innerblock['attributes']['credit'] ); ?></p>
												</div>
												<?php
										} elseif ( $innerblock['name'] === 'core/heading' ) {
											echo wp_kses_post( '<h' . $innerblock['attributes']['level'] . ' id="heading-block" >' . $innerblock['attributes']['content'] . '</h' . $innerblock['attributes']['level'] . '>' );
										} elseif ( $innerblock['name'] === 'core/paragraph' || $innerblock['name'] === 'core/html' ) { 
											echo wp_kses_post( '<p>' . $innerblock['attributes']['content'] . '</p>' );
										} elseif ( $innerblock['name'] === 'core/list' ) {
											$liststart = $innerblock['attributes']['ordered'] ? '<ol>' : '<ul>';
											$listend   = $innerblock['attributes']['ordered'] ? '</ol>' : '</ul>';
	
											echo wp_kses_post( $liststart . $innerblock['attributes']['values'] . $listend );
										}
									} else {
										if ( $innerblock['name'] === 'core/paragraph' ) {
											echo wp_kses_post( '<p>' . $innerblock['originalContent'] . '</p>' );
										} else {
											echo wp_kses_post( $innerblock['originalContent'] );
										}
									} 
									?>
									</div>
									<?php
							}
						} else {
							echo wp_kses_post( $content );
						} 
						?>
						</div>
						<?php
					}
				}

				if ( isset( $product_review ) && $product_review['permalink'] ) { 
					?>
					Read our full 
					<a class="product-chart-item__review-link" href="<?php echo esc_url( $product_review['permalink'] ); ?>" <?php echo $open_new_tab ? "target='_blank'" : ''; ?>><?php echo esc_html( $product_title ); ?> review</a>
																				<?php
				}
				?>
			</div>
			<?php

			do_action( 'idg_after_product_chart_item' );
		endforeach;
		?>
		</div>

		<?php

		return ob_get_clean();
	}
}

/**
 * Checks if the function exists or not.
 * 
 * @SuppressWarnings(PHPMD).
 */
if ( ! function_exists( 'filter_product_chart_attributes' ) ) {
	/**
	 * Filters individual 'product-chart-item' block's description markup.
	 *
	 * @param array $block Block details.
	 *
	 * @return array Modified block's data.
	 */
	function filter_product_chart_attributes( $block ) {
		if ( Sites::is_origin() ) {
			return $block;
		}
		if ( empty( $block ) || ! is_array( $block ) || 'idg-base-theme/product-chart-block' !== $block['blockName'] ) {
			return $block;
		}

		if ( is_array( $block['attrs']['productData'] ) && ! empty( $block['attrs']['productData'] ) ) {
			foreach ( $block['attrs']['productData'] as $index => $product_data ) {
				if ( empty( $product_data['productContent'] ) ) {
					continue;
				}
				
				$product_content = $product_data['productContent'];
				$encoded_content = serialize_block_attributes( $product_content );
				$encoded_content = trim( $encoded_content, '"' );
				$block['attrs']['productData'][ $index ]['productContent'] = $encoded_content;
			}
		}

		if ( is_array( $block['innerBlocks'] ) && ! empty( $block['attrs']['productData'] ) ) {
			foreach ( $block['innerBlocks'] as $index => $inner_block ) {
				if ( empty( $product_data['productContent'] ) ) {
					continue;
				}

				$product_content = $inner_block['attrs']['productContent'];
				$encoded_content = serialize_block_attributes( $product_content );
				$encoded_content = trim( $encoded_content, '"' );
				$block['innerBlocks'][ $index ]['attrs']['productContent'] = $encoded_content;
			}
		}

		return $block;
	}
}

add_filter( 'idg_publishing_flow_parse_idg-base-theme/product-chart-block', 'filter_product_chart_attributes' );

/**
 * Check if a source is a YouTube video.
 *
 * @param  string $jwppp_video_url a full url to check.
 * @return array if a YouTube video, the embed url and the preview image.
 */
function check_yt_video( $jwppp_video_url ) {
	$youtube1      = 'https://www.youtube.com/watch?v=';
	$youtube2      = 'https://youtu.be/';
	$youtube_embed = 'https://www.youtube.com/embed/';
	$is_yt         = false;
	if ( strpos( $jwppp_video_url, $youtube1 ) !== false ) {
		$jwppp_embed_url = str_replace( $youtube1, $youtube_embed, $jwppp_video_url );
		$is_yt           = true;
	} elseif ( strpos( $jwppp_video_url, $youtube2 ) !== false ) {
		$jwppp_embed_url = str_replace( $youtube2, $youtube_embed, $jwppp_video_url );
		$is_yt           = true;
	} elseif ( strpos( $jwppp_video_url, $youtube_embed ) !== false ) {
		$jwppp_embed_url = $jwppp_video_url;
		$is_yt           = true;
	} else {
		$jwppp_embed_url = $jwppp_video_url;
		$is_yt           = false;
	}

	return [
		'yes'       => $is_yt,
		'embed-url' => $jwppp_embed_url,
	];
}
