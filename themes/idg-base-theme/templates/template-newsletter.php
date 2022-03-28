<?php
/* Template Name: Newsletter Template */

// To keep straight quotes in script for email input field
remove_filter( 'the_content', 'wptexturize' );

get_header();
?>
	<main id="primary" class="site-main newsletter-wrapper">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<div class="entry-content">
						<?php the_content(); ?>
				</div><!-- .entry-content -->
			</article><!-- #post-<?php the_ID(); ?> -->

		<?php endwhile; ?>
	</main><!-- #main -->
<?php
get_footer();
