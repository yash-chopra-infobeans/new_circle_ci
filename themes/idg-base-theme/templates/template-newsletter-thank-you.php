<?php
/* Template Name: Newsletter Thank You Template */

get_header();
?>
	<main id="primary" class="site-main">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<header class="entry-header">
						<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
				</header><!-- .entry-header -->

				<div class="entry-content">
					<div id="nl-confirm">
						<p>
							<?php
							esc_html_e(
								'IMPORTANT: One more step required to activate your subscription! PLEASE check your email NOW.',
								'idg-base-theme'
							);
							?>
							<br>
							<?php
								esc_html_e(
									'You will be sent an email with an activation link. We cannot begin sending your newsletters until
								you click the link in the email',
									'idg-base-theme'
								);
							?>
							<strong><?php esc_html_e( 'and complete your registration', 'idg-base-theme' ); ?></strong>.
							<?php esc_html_e( 'This is done to protect your privacy and prevent unauthorized sign-ups.', 'idg-base-theme' ); ?>
						</p>

						<ul>
							<li>
								<?php esc_html_e( 'Please check your email. An activation email will be there shortly. If you do not receive it, please check your bulk folder.', 'idg-base-theme' ); ?>
								<?php esc_html_e( 'Or contact:', 'idg-base-theme' ); ?> <a href="mailto:customer_service@macworld.com?Subject=">customer_service@macworld.com</a>
							</li>
						</ul>

						<!-- <div class="button to-home"><a href="/">Back to home page</a></div> -->
						<div class="nl-social">
							<h3 class="subscription"><?php esc_html_e( 'Get more IT peer perspective online', 'idg-base-theme' ); ?></h3>
							<ul class="nl-socialList">
								<li>
									<a href="https://twitter.com/macworld" target="_blank" rel="nofollow">
										<?php idg_asset( '/icons/twitter.svg' ); ?>
										<span><?php esc_html_e( 'Twitter', 'idg-base-theme' ); ?></span>
									</a>
								</li>
								<li>
									<a href="http://www.facebook.com/Macworld" target="_blank" rel="nofollow">
										<?php idg_asset( '/icons/facebook.svg' ); ?>
										<span><?php esc_html_e( 'Facebook', 'idg-base-theme' ); ?></span>
									</a>
								</li>
							</ul>
						</div><!-- end .nl-social -->

						<h3 class="subscription"><?php esc_html_e( 'More Resources', 'idg-base-theme' ); ?></h3>
						<p><?php esc_html_e( 'Visit', 'idg-base-theme' ); ?> <a class="edition-link-url" href="/resources"><?php esc_html_e( 'Macworld\'s library of FREE Online Resources', 'idg-base-theme' ); ?></a> <?php esc_html_e( 'from leading industry analysts and our partners.', 'idg-base-theme' ); ?></p>
					</div><!-- end .nl-confirm -->
				</div><!-- .entry-content -->
			</article><!-- #post-<?php the_ID(); ?> -->

		<?php endwhile; ?>
	</main><!-- #main -->
<?php
get_footer();
