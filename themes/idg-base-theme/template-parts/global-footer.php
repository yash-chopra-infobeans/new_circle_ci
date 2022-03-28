<?php
/**
 * File for global footer.
 *
 * @package IDG.
 */

use function \IDG\Base_Theme\Utils\is_amp;

// Get custom walker.
$walker = new Menu_With_Chevrons();

$footer_content = cf_get_value( 'global_settings', 'footer_content' );

$footer_social_facebook_url  = $footer_content['footer_social_icons']['footer_social_facebook_url'];
$footer_social_twitter_url   = $footer_content['footer_social_icons']['footer_social_twitter_url'];
$footer_social_youtube_url   = $footer_content['footer_social_icons']['footer_social_youtube_url'];
$footer_social_instagram_url = $footer_content['footer_social_icons']['footer_social_instagram_url'];
$footer_social_linkedin_url  = $footer_content['footer_social_icons']['footer_social_linkedin_url'];

$footer_one_title   = $footer_content['footer_one']['footer_area_one_title'];
$footer_one_image   = $footer_content['footer_one']['footer_area_one_image'];
$footer_one_content = $footer_content['footer_one']['footer_area_one_content'];

$footer_two_title   = $footer_content['footer_two']['footer_area_two_title'];
$footer_two_image   = $footer_content['footer_two']['footer_area_two_image'];
$footer_two_content = $footer_content['footer_two']['footer_area_two_content'];


$more_from_idg_content = cf_get_value( 'global_settings', 'more_from_idg' );



?>

<footer class="primaryFooter">
	<div class="primaryFooter-wrap">
		<div class ='primaryFooter-return-top' title="Return to Top">
			<?php
			if ( ! is_amp() ) {
				idg_asset( '/icons/to-top.svg' );
			}
			?>
		</div>
		<div class="primaryFooter-top">
			<div class="primaryFooter-top--logo">
				<a href="<?php echo esc_url( site_url() ); ?>" title="<?php esc_attr_e( get_option( 'blogname' ) ); ?>">
					<?php idg_asset( '/img/logo-from-idg.svg' ); ?>
				</a>
			</div>
			<div class="primaryFooter-top--tagline">
				<?php echo esc_html( get_bloginfo( 'description' ) ); ?>
			</div>
			<div class="primaryFooter-top-social-wrap">
				<ul class="primaryFooter-top-social">
					<?php if ( $footer_social_facebook_url ) : ?>
					<li class="primaryFooter-top-social--facebook">
						<a href="<?php printf( esc_url( $footer_social_facebook_url ) ); ?>" target="_blank" rel="noopener noreferrer nofollow" title="Share on Facebook">
							<?php idg_asset( '/icons/facebook.svg' ); ?>
						</a>
					</li>
						<?php
					endif;
					if ( $footer_social_twitter_url ) :
						?>
					<li class="primaryFooter-top-social--twitter">
						<a href="<?php printf( esc_url( $footer_social_twitter_url ) ); ?>" target="_blank" rel="noopener noreferrer nofollow" title="Share on Twitter">
							<?php idg_asset( '/icons/twitter.svg' ); ?>
						</a>
					</li>
						<?php
					endif;
					if ( $footer_social_youtube_url ) :
						?>
					<li class="primaryFooter-top-social--youtube">
						<a href="<?php printf( esc_url( $footer_social_youtube_url ) ); ?>" target="_blank" rel="noopener noreferrer nofollow" title="Share on YouTube">
							<?php idg_asset( '/icons/youtube.svg' ); ?>
						</a>
					</li>
						<?php
					endif;
					if ( $footer_social_instagram_url ) :
						?>
					<li class="primaryFooter-top-social--instagram">
						<a href="<?php printf( esc_url( $footer_social_instagram_url ) ); ?>" target="_blank" rel="noopener noreferrer nofollow" title="Share on Instagram">
							<?php idg_asset( '/icons/instagram.svg' ); ?>
						</a>
					</li>
						<?php
					endif;
					if ( $footer_social_linkedin_url ) :
						?>
					<li class="primaryFooter-top-social--linkedin">
						<a href="<?php printf( esc_url( $footer_social_linkedin_url ) ); ?>" target="_blank" rel="noopener noreferrer nofollow" title="Share on LinkedIn">
							<?php idg_asset( '/icons/linkedin.svg' ); ?>
						</a>
					</li>
						<?php
					endif;
					?>
				</ul>
			</div>
		</div>
		<div class="primaryFooter-bottom">
			<?php
			if ( has_nav_menu( 'footer-primary' ) ) {
				wp_nav_menu(
					[
						'theme_location'  => 'footer-primary',
						'menu_id'         => 'footer-primary',
						'menu_class'      => 'primaryFooter-menu',
						'container_class' => 'primaryFooter-menu-wrap',
						'walker'          => $walker,
					]
				);
			}
			if ( has_nav_menu( 'footer-secondary' ) ) {
				wp_nav_menu(
					[
						'theme_location'  => 'footer-secondary',
						'menu_id'         => 'footer-secondary',
						'menu_class'      => 'primaryFooter-menu',
						'container_class' => 'primaryFooter-menu-wrap',
						'walker'          => $walker,
					]
				);
			}
			if ( $footer_one_title || $footer_one_image || wp_strip_all_tags( $footer_one_content ) ) :
				?>
			<div class="primaryFooter-menu-wrap">
				<ul class="primaryFooter-menu">
				<li class="menu-item-has-children">
				<?php
						echo esc_html( $footer_one_title );
				?>
						<button class="sub-menu-open-button" aria-label="open-close"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M151.5 347.8L3.5 201c-4.7-4.7-4.7-12.3 0-17l19.8-19.8c4.7-4.7 12.3-4.7 17 0L160 282.7l119.7-118.5c4.7-4.7 12.3-4.7 17 0l19.8 19.8c4.7 4.7 4.7 12.3 0 17l-148 146.8c-4.7 4.7-12.3 4.7-17 0z"></path></svg></button>
						<ul class="sub-menu">
						<?php
						if ( $footer_one_image ) :
							?>
							<li>
								<img src='<?php echo esc_url( $footer_one_image ); ?> ' loading="lazy"/>
							</li>
							<?php
						endif;
						?>
							<li>
					<?php
					echo wp_kses_post(
						$footer_one_content
					);
					?>
							</li>
						</ul>
					</li>
				</ul>
			</div>
				<?php
			endif;
			if ( $footer_two_title || $footer_two_image || wp_strip_all_tags( $footer_two_content ) ) :
				?>
			<div class="primaryFooter-menu-wrap">
				<ul class="primaryFooter-menu">
					<li class="menu-item-has-children">
					<?php echo esc_html( $footer_two_title ); ?>
						<button class="sub-menu-open-button" aria-label="open-close"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M151.5 347.8L3.5 201c-4.7-4.7-4.7-12.3 0-17l19.8-19.8c4.7-4.7 12.3-4.7 17 0L160 282.7l119.7-118.5c4.7-4.7 12.3-4.7 17 0l19.8 19.8c4.7 4.7 4.7 12.3 0 17l-148 146.8c-4.7 4.7-12.3 4.7-17 0z"></path></svg></button>
						<ul class="sub-menu">
						<?php
						if ( $footer_two_image ) :
							?>
							<li>
								<img src='<?php echo esc_url( $footer_two_image ); ?> ' alt="Recent cover images of Macworld Digital Magazine" loading="lazy"/>
							</li>
							<?php
						endif;
						?>
							<li>
							<?php
							echo wp_kses_post(
								$footer_two_content
							);
							?>
							</li>
						</ul>
					</li>
				</ul>
			</div>
				<?php
			endif;
			?>
		</div>
	</div>
	<div class='footer-base'>
		<div class ='footer-base-child idg-logo'>
		<?php idg_asset( '/img/idg-logo.svg' ); ?>
		</div>
		<div class='footer-base-child idg-copyright'>
			<?php
				idg_base_theme_post_copyright( true );
			?>
		</div>
		<?php
		if ( $more_from_idg_content['more_from_idg']['dropdown_site'] ) :
			?>
			<div class='footer-base-child related-sites-dropdown'>
			<select id='footerSelect'>
				<option value="#"><?php esc_html_e( 'Explore the IDG Network +' ); ?></option>
				<?php
				foreach ( $more_from_idg_content['more_from_idg']['dropdown_site'] as $idg_link ) {
					printf(
						'<option value="%s">%s</option>',
						esc_url( $idg_link['url'] ),
						esc_html( $idg_link['title'] )
					);
				};
				?>
				</select>
			</div>
		<?php endif; ?>

		<?php
		if ( ! is_amp() ) :
			?>
			<div class ='footer-base-child return-top-mobile'>
				<?php
				idg_asset( '/icons/to-top.svg' );
				?>
				<div class='return-top-text'>
					<a>Top Of Page</a>
				</div>
			</div>
		<?php endif; ?>



		<?php do_action( 'idg_footer_base' ); ?>


	</div>
</footer>
