<?php
/**
 * Price Comparison Block Render.
 *
 * @package idg-base-theme
 */

use IDG\Products\Article;
use function IDG\Base_Theme\Utils\is_amp;
use function IDG\Base_Theme\Utils\array_to_attrs;

if ( ! function_exists( 'idg_render_price_comparison' ) ) {
	/**
	 * Render the 'Price Comparison' block.
	 *
	 * @param array $attributes Current block attributes.
	 * @return mixed
	 */
	function idg_render_price_comparison( $attributes ) : string {
		if ( is_admin() || empty( $attributes ) || ! is_array( $attributes ) ) {
			return '';
		}

		$product_id      = ! empty( absint( $attributes['productId'] ) ) ? absint( $attributes['productId'] ) : 0;
		$instance_id     = ! empty( $attributes['instanceId'] ) ? $attributes['instanceId'] : 0;
		$amp_wrapper_key = "hiddenrecord{$instance_id}";
		$post_id         = get_the_ID();

		// Cached at this point as any products will already have been added to the datalayer.
		$products = Article::get_products( get_the_ID() );
		$product  = $products[ $product_id ] ?? false;

		$product_pricing_details = idg_get_product_pricing( $product );
		$amp_products_endpoint   = Article::get_amp_products_endpoint() . $post_id . '/' . $product_id . '/pco';

		if ( empty( $product_pricing_details ) || ! is_array( $product_pricing_details ) ) {
			return '';
		}

		$open_product_link_in_new_tab = (
			! empty( $attributes['linksInNewTab'] ) &&
			( true === $attributes['linksInNewTab'] )
		);

		$will_have_hidden_records = count( $product_pricing_details ) > 4;
		$placeholder_count        = count( $product_pricing_details ) + 2;
		$amp_count                = count( $product_pricing_details ) > 1 ? ( count( $product_pricing_details ) * 100 ) - 50 : 150;

		ob_start();

		?>

		<?php if ( ! empty( $attributes['title'] ) ) : ?>
		<h3 class="review-best-price">
			<?php echo esc_html( $attributes['title'] ); ?>
		</h3>
		<?php endif; ?>
		<div class="wp-block-price-comparison price-comparison <?php echo is_amp() ? ' amp-product-chart-item' : ''; ?>">
			<div class="price-comparison__record price-comparison__record--header">
				<div>
					<span><?php esc_html_e( 'Retailer', 'idg-base-theme' ); ?></span>
				</div>
				<div class="price-comparison__price">
					<span><?php esc_html_e( 'Price', 'idg-base-theme' ); ?></span>
				</div>
				<div class="price-comparison__delivery">
					<span><?php esc_html_e( 'Delivery', 'idg-base-theme' ); ?></span>
				</div>
			</div>

			<?php
			if ( is_amp() ) { 
				?>
				<amp-list noloading src="<?php echo esc_url( $amp_products_endpoint ); ?>" height="<?php echo esc_attr( $amp_count ); ?>" binding="refresh" width="auto" layout="fixed-height">
					<template type="amp-mustache">
						<div class="pco-amp-list">
							{{#show_hidden}}
								<div
									class="price-comparison__hidden-records-wrapper"
									data-amp-bind-class="'price-comparison__hidden-records-wrapper ' + ( <?php echo esc_attr( $amp_wrapper_key ); ?> ? 'price-comparison__hidden-records-wrapper--is-open' : '' )"
								>
							{{/show_hidden}}
								<div class="price-comparison__record">
									<div class="price-comparison__image">
										{{#vendor_logo}}
											<amp-img layout="fill" src="{{vendor_logo}}" alt="{{vendor}}"
											/>
										{{/vendor_logo}}
										{{^vendor_logo}}
											<span>{{vendor}}</span>
										{{/vendor_logo}}
									</div>
									<div class="price-comparison__price">
										<span>{{price}}</span>
									</div>
									<div class="price-comparison__delivery">
										<span>{{delivery_text}}</span>
									</div>
									<a class="price-comparison__view-button" href="{{url}}"
									<?php echo esc_attr( array_to_attrs( $product['attributes'] ) ); ?>
									<?php echo $open_product_link_in_new_tab ? 'target="_blank"' : ''; ?>
									data-vars-outbound-link="{{url}}"
									data-amp-link="amp-list-price-comparison"
									data-vars-link-position="Price Comparison Body"
									data-vars-link-position-id='000'
									><?php esc_html_e( 'View', 'idg-base-theme' ); ?></a> 
								</div>
							{{#show_hidden}}
								</div>
							{{/show_hidden}}
							{{#footer_text}}
								<div class="price-comparison__record price-comparison__record--footer">
									<span class="price-comparison__footer-text">
										<?php if ( ! empty( $attributes['footerText'] ) ) : ?>
											<?php echo esc_html( $attributes['footerText'] ); ?>
										<?php endif; ?>
									</span>
									{{#will_have_hidden_records}}
										<button 
											class="price-comparison__view-more-button"
											data-amp-bind-text="<?php echo esc_attr( $amp_wrapper_key ); ?> ?
												'<?php esc_attr_e( 'View fewer prices', 'idg-base-theme' ); ?>' :
												'<?php esc_attr_e( 'View more prices' ); ?>'"
											on="tap:AMP.setState({<?php echo esc_attr( $amp_wrapper_key ); ?>:
												! <?php echo esc_attr( $amp_wrapper_key ); ?>})"
										>
											<?php esc_html_e( 'View more prices', 'idg-base-theme' ); ?>
										</button>
									{{/will_have_hidden_records}}
								</div>
							{{/footer_text}}
						</div>
					</template>
					<div placeholder>
						<?php for ( $i = 0;$i < $placeholder_count;$i++ ) { ?>
							<div class="placeholder-row">
								<div class="placeholder-section small"></div>
								<div class="placeholder-section small"></div>
								<div class="placeholder-section small"></div>
								<div class="placeholder-section small"></div>
							</div>
						<?php } ?>
					</div>
					<div fallback></div>
				</amp-list>
				<?php
			} else {
				foreach ( $product_pricing_details as $index => $record ) :
					$product_price = ! empty( $record['price'] ) ? $record['price'] : '-'; 
					?>
					<?php if ( 4 === $index && $will_have_hidden_records ) : ?>
						<div
							class="price-comparison__hidden-records-wrapper"
							data-amp-bind-class="'price-comparison__hidden-records-wrapper ' + ( <?php echo esc_attr( $amp_wrapper_key ); ?> ? 'price-comparison__hidden-records-wrapper--is-open' : '' )"
						>
					<?php endif; ?>
						<div class="price-comparison__record">
							<div class="price-comparison__image">
								<?php $vendor_logo = idg_products_get_vendor_logo( $record['vendor'] ); ?>
								<?php if ( ! empty( $vendor_logo ) ) : ?>
									<img
										src="<?php echo esc_url( $vendor_logo ); ?>"
										alt="<?php echo esc_attr( $record['vendor'] ); ?>"
										loading="lazy"
									/>
								<?php else : ?>
									<span><?php echo esc_html( $record['vendor'] ); ?></span>
								<?php endif; ?>
							</div>
							<div class="price-comparison__price">
								<span><?php echo esc_html( $product_price ); ?></span>
							</div>
							<div class="price-comparison__delivery">
								<span><?php echo esc_html( idg_products_get_delivery_text( $record ) ); ?></span>
							</div>
							<div>
								<?php
								$link_attributes = array_merge(
									$product['attributes'],
									[
										'data-vars-link-position-id' => $attributes['position_id'] ?: '000',
										'data-vars-link-position' => $attributes['position'] ?: 'Price Comparison Body',
										'data-vars-outbound-link' => $record['link'],
									]
								);


								if ( $open_product_link_in_new_tab ) {
									$link_attributes['target'] = '_blank';
								}

								printf(
									'<a class="price-comparison__view-button" href="%s" %s>View</a>',
									esc_url( $record['link'] ),
                                    // phpcs:ignore
                                    array_to_attrs( $link_attributes ),
								);
								?>
							</div>
						</div>
				<?php endforeach; ?>
				<?php if ( $will_have_hidden_records ) : ?>
					</div>
					<?php 
				endif; 
				?>
				<div class="price-comparison__record price-comparison__record--footer">
					<span class="price-comparison__footer-text">
						<?php if ( ! empty( $attributes['footerText'] ) ) : ?>
							<?php echo esc_html( $attributes['footerText'] ); ?>
						<?php endif; ?>
						</span>
					<?php if ( $will_have_hidden_records ) : ?>
						<button 
							class="price-comparison__view-more-button"
							data-amp-bind-text="<?php echo esc_attr( $amp_wrapper_key ); ?> ?
								'<?php esc_attr_e( 'View fewer prices', 'idg-base-theme' ); ?>' :
								'<?php esc_attr_e( 'View more prices' ); ?>'"
							on="tap:AMP.setState({<?php echo esc_attr( $amp_wrapper_key ); ?>:
								! <?php echo esc_attr( $amp_wrapper_key ); ?>})"
						>
							<?php esc_html_e( 'View more prices', 'idg-base-theme' ); ?>
						</button>
					<?php endif; ?>
				</div>
				<?php
			} 
			?>
		</div>
		<?php

		return (string) ob_get_clean();
	}
}
