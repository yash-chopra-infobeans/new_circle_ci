<?php
get_header();
?>

	<main id="primary" class="site-main">

		<header class="archive-header">
			<?php
			idg_base_theme_breadcrumbs( 'archive' );
			the_archive_title( '<h1 class="archive-title">', '</h1>' );
			the_archive_description( '<div class="archive-description">', '</div>' );

			?>

		</header><!-- .page-header -->

		<section class='layout--right-rail'>

			<div class="wp-block-columns">

				<div class="wp-block-column">

					<?php if ( have_posts() ) : ?>

					<div class="articleFeed articleFeed--list">
						<div class="articleFeed-inner">
							<?php
							/* Start the Loop */
							while ( have_posts() ) :
								the_post();

								get_template_part( 'template-parts/snippet', get_post_type() );

							endwhile;
							?>

						</div>
					</div>

						<?php

						idg_base_theme_pagination();

					else :

						get_template_part( 'template-parts/content', 'none' );

					endif;
					?>

				</div>

				<div class="wp-block-column">
					<?php get_sidebar(); ?>
				</div>

			</div>

		</section>

	</main><!-- #main -->

<?php
get_footer();
