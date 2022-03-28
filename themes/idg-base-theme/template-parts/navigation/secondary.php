<?php

$nav_social_links = cf_get_value( 'global_settings', 'navigation' );

$nav_social_facebook_url  = $nav_social_links['navigation_social_icons']['nav_social_facebook_url'];
$nav_social_twitter_url   = $nav_social_links['navigation_social_icons']['nav_social_twitter_url'];
$nav_social_instagram_url = $nav_social_links['navigation_social_icons']['nav_social_instagram_url'];
$nav_social_linkedin_url  = $nav_social_links['navigation_social_icons']['nav_social_linkedin_url'];
$nav_social_youtube_url   = $nav_social_links['navigation_social_icons']['nav_social_youtube_url'];
$nav_secondary_title      = $nav_social_links['secondary_nav_title']['secondary_nav_text'];
?>

<div id="secondaryNav">
	<div class="secondaryNav-wrap">
		<?php if ( $nav_secondary_title ) : ?>
		<span class="secondaryNav-title">
			<?php echo esc_html( $nav_secondary_title ); ?>
		</span>
		<?php endif; ?>
		<div class="secondaryNav-menu-wrap">
			<?php
			if ( has_nav_menu( 'hot-topics' ) ) {
				wp_nav_menu(
					[
						'theme_location'  => 'hot-topics',
						'menu_id'         => 'hot-topics',
						'menu_class'      => 'secondaryNav-menu',
						'container_class' => 'secondaryNav-container',
					]
				);
			}
			?>
		</div>
		<div class="secondaryNav-social-wrap">
			<ul class="secondaryNav-social">
			<?php if ( $nav_social_facebook_url ) : ?>
				<li class="secondaryNav-social--facebook">
					<a href="<?php printf( esc_url( $nav_social_facebook_url ) ); ?>" target="_blank" rel="noopener noreferrer nofollow" title="Share on Facebook">
						<?php idg_asset( '/icons/facebook.svg' ); ?>
					</a>
				</li>
				<?php 
			endif;
			if ( $nav_social_twitter_url ) :      
				?>
				<li class="secondaryNav-social--twitter">
					<a href="<?php printf( esc_url( $nav_social_twitter_url ) ); ?>" target="_blank" rel="noopener noreferrer nofollow" title="Share on Twitter">
						<?php idg_asset( '/icons/twitter.svg' ); ?>
					</a>
				</li>
				<?php 
			endif;
			if ( $nav_social_youtube_url ) :     
				?>
				<li class="secondaryNav-social--youtube">
					<a href="<?php printf( esc_url( $nav_social_youtube_url ) ); ?>" target="_blank" rel="noopener noreferrer nofollow" title="Share on YouTube">
						<?php idg_asset( '/icons/youtube.svg' ); ?>
					</a>
				</li>
				<?php 
			endif;
			if ( $nav_social_instagram_url ) :      
				?>
				<li class="secondaryNav-social--youtube">
					<a href="<?php printf( esc_url( $nav_social_instagram_url ) ); ?>" target="_blank" rel="noopener noreferrer nofollow" title="Share on Instagram">
						<?php idg_asset( '/icons/instagram.svg' ); ?>
					</a>
				</li>
				<?php 
			endif;
			if ( $nav_social_linkedin_url ) :      
				?>
				<li class="secondaryNav-social--youtube">
					<a href="<?php printf( esc_url( $nav_social_linkedin_url ) ); ?>" target="_blank" rel="noopener noreferrer nofollow" title="Share on LinkedIn">
						<?php idg_asset( '/icons/linkedin.svg' ); ?>
					</a>
				</li>
				<?php 
			endif;   
			?>
			</ul>
		</div>
	</div>
</div>
