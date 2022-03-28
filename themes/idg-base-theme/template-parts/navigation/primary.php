<?php

use function IDG\Base_Theme\Utils\is_amp;

// If amp fallback to sticky header.
if ( is_amp() ) {
	$header_type = 'sticky-header';
}

// Check header type.
$primarynav_class = 'primaryNav--' . $header_type;
// Get custom walker.
$walker = new Menu_With_Chevrons();

$nav_button_content = cf_get_value( 'global_settings', 'navigation' );

$nav_button_one_text = $nav_button_content['navigation_buttons']['button_one_text'];
$nav_button_one_link = $nav_button_content['navigation_buttons']['button_one_link'];
$nav_button_two_text = $nav_button_content['navigation_buttons']['button_two_text'];
$nav_button_two_link = $nav_button_content['navigation_buttons']['button_two_link'];

// Get search details.
$google_search  = false;
$search_details = cf_get_value( 'global_settings', 'google_search', 'google_programmable_search' );
if ( ! empty( $search_details['engine_id'] ) && ! empty( $search_details['slug'] ) ) {
	$google_search = true;
}
?>

<div id="primaryNav" class="<?php echo esc_attr( $primarynav_class ); ?>">
	<div class="primaryNav-wrap">
		<div class="primaryNav-left-wrap">
			<ul class="primaryNav-left">
				<li class="primaryNav-left--menu">
					<!-- AMP markup -->
					<?php if ( is_amp() ) : ?>
						<div id="mobileNav-open-button" on="tap:mobileNav.open" role="button">
							<span><?php esc_html_e( 'Menu', 'idg-base-theme' ); ?></span>
							<?php idg_asset( '/icons/bars.svg' ); ?>
						</div>
					<!-- Non AMP markup -->
					<?php else : ?>
						<a href="#" id="mobileNav-open-button" aria-label="<?php esc_html_e( 'Open mobile menu', 'idg-base-theme' ); ?>" role="button">
							<span><?php esc_html_e( 'Menu', 'idg-base-theme' ); ?></span>
							<?php idg_asset( '/icons/bars.svg' ); ?>
						</a>
					<?php endif; ?>
				</li>
				<li class="primaryNav-left--logo">
					<a href="<?php echo esc_url( site_url() ); ?>">
						<?php idg_asset( '/img/logo.svg' ); ?>
					</a>
				</li>
			</ul>
		</div>
		<?php
		if ( has_nav_menu( 'menu-1' ) ) {
			wp_nav_menu(
				[
					'theme_location'  => 'menu-1',
					'menu_id'         => 'menu-1-primary',
					'menu_class'      => 'primaryNav-menu is-dropdown',
					'container_class' => 'primaryNav-menu-wrap',
					'walker'          => $walker,
				]
			);
		}
		if ( has_nav_menu( 'idg-network' ) ) {
			wp_nav_menu(
				[
					'theme_location'  => 'idg-network',
					'menu_id'         => 'idg-network',
					'menu_class'      => 'primaryNav-network is-dropdown',
					'container_class' => 'primaryNav-network-wrap',
					'walker'          => $walker,
				]
			);
		}
		?>
		<div class="primaryNav-actions-wrap">
			<ul class="primaryNav-actions">
				<?php
				if ( $google_search ) :
					?>
				<li class="primaryNav-action--search">
					<!-- AMP markup -->
					<?php if ( is_amp() ) : ?>
						<a href="<?php echo esc_url( get_home_url( null, $search_details['slug'] ) ); ?>">
							<?php idg_asset( '/icons/search.svg' ); ?>
						</a>
					<!-- Non AMP markup -->
					<?php else : ?>
						<a href="#" id="siteSearch-open-button" aria-label="<?php esc_html_e( 'Open site search', 'idg-base-theme' ); ?>" role="button">
							<?php idg_asset( '/icons/search.svg' ); ?>
						</a>
					<?php endif; ?>
				</li>
					<?php
				endif;
				if ( $nav_button_one_text ) :
					?>
				<li class="primaryNav-action--subscribe">
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
				<li class = 'primaryNav-action--login'>
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
	</div>
	<?php if ( $google_search ) : ?>
	<div id="siteSearch">
		<div class="siteSearch-wrap">
			<div class="siteSearch-close">
				<a href="#" id="siteSearch-close-button" aria-label="<?php esc_html_e( 'Close site search', 'idg-base-theme' ); ?>" role="button">
					<?php idg_asset( '/icons/times.svg' ); ?>
				</a>
			</div>
			<span class="siteSearch-help-text"><?php esc_html_e( 'Type your search and hit enter', 'idg-base-theme' ); ?></span>
			<div class="gcse-searchbox-only" data-resultsUrl="<?php echo esc_url( site_url( $search_details['slug'] ) ); ?>"></div>
		</div>
	</div>
	<?php endif; ?>
</div>
