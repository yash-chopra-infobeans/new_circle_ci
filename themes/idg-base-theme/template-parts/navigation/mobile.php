<?php
// Get custom walker.
$walker = new Menu_With_Chevrons();

$nav_button_content = cf_get_value( 'global_settings', 'navigation' );

$nav_button_one_text = $nav_button_content['navigation_buttons']['button_one_text'];
$nav_button_one_link = $nav_button_content['navigation_buttons']['button_one_link'];
$nav_button_two_text = $nav_button_content['navigation_buttons']['button_two_text'];
$nav_button_two_link = $nav_button_content['navigation_buttons']['button_two_link'];
?>
<?php if ( \IDG\Base_Theme\Utils\is_amp() ) : ?>
	<!-- AMP markup -->
	<amp-sidebar id="mobileNav" layout="nodisplay" side="left">
<?php else : ?>
	<!-- Non AMP markup -->
	<div id="mobileNav">
<?php endif; ?>
		<div class="mobileNav-close">
		<!-- AMP markup -->
		<?php if ( \IDG\Base_Theme\Utils\is_amp() ) : ?>
			<div id="mobileNav-close-button" on="tap:mobileNav.close" role="button">
				<?php idg_asset( '/icons/times.svg' ); ?>
			</div>
		<!-- Non AMP markup -->
		<?php else : ?>
			<a href="#" id="mobileNav-close-button" aria-label="<?php esc_html_e( 'Close mobile menu', 'idg-base-theme' ); ?>" role="button">
				<?php idg_asset( '/icons/times.svg' ); ?>
			</a>
		<?php endif; ?>
		</div>
		<?php if ( $nav_button_one_text || $nav_button_two_text ) : ?>
		<div class="mobileNav-actions-wrap">
			<ul class="mobileNav-actions">
			<?php
			if ( $nav_button_one_text ) :
				?>
				<li class="mobileNav-action--button">
				<a href="<?php echo esc_url( $nav_button_one_link ); ?>"
						aria-label="<?php echo esc_attr( $nav_button_one_text ); ?>" role="button">
					<?php
					echo esc_html( $nav_button_one_text );
					?>
						</a>
					</li>
				<?php
			endif;

			if ( $nav_button_two_text ) :
				?>
				<li class="mobileNav-action--button">
			<a href="<?php echo esc_url( $nav_button_two_link ); ?>"
						aria-label="<?php echo esc_attr( $nav_button_two_text ); ?>" role="button">
							<?php
							echo esc_html( $nav_button_two_text );
							?>
						</a>
					</li>
				<?php
			endif;
			?>
			</ul>
		</div>
			<?php 
		endif;
		if ( has_nav_menu( 'menu-1' ) ) {
			wp_nav_menu(
				[
					'theme_location'  => 'menu-1',
					'menu_id'         => 'menu-1-mobile',
					'menu_class'      => 'mobileNav-menu',
					'container_class' => 'mobileNav-menu-wrap',
					'walker'          => $walker,
				]
			);
		}
		?>
<?php if ( \IDG\Base_Theme\Utils\is_amp() ) : ?>
	<!-- AMP markup -->
	</amp-sidebar>
<?php else : ?>
	<!-- Non AMP markup -->
	</div>
<?php endif; ?>
<div id="site-overlay"></div>
