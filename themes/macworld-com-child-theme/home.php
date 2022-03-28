<?php
/**
 * This file is used to display layout on home and archive pages.
 *
 * @package macworld-com-child-theme
 */

use IDG\Third_Party\GPT\Ad_Templates;

get_header();
Ad_Templates::render( 'banner' );
?>
	<main id="primary" class="site-main">

		<header class="archive-header">
			<h1>
			<?php
			if ( function_exists( 'single_post_title' ) ) {
				single_post_title();
			} 
			?>
			</h1>
		</header><!-- .page-header -->

		<section class='layout--right-rail'>

			<div class="wp-block-columns">

				<div class="wp-block-column">

					<?php if ( have_posts() ) : ?>

					<div class="articleFeed articleFeed--list">
						<div class="articleFeed-inner">

							<?php
							// Count number of article.
							$count_article = -1;
							/* Start the Loop */
							while ( have_posts() ) :
								the_post();
								do_action( 'idg_render_article_feed_item', $count_article, 0 );
								$count_article++;
								get_template_part( 'template-parts/snippet', get_post_type() );

							endwhile;
							?>

						</div>
					</div>

						<?php
						if ( function_exists( 'idg_base_theme_pagination' ) ) :
							idg_base_theme_pagination();
						endif;
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
