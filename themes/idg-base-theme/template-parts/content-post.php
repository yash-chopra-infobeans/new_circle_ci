<?php
$legacy_data = get_post_meta( get_the_ID(), 'old_id_in_onecms' );
$legacy_id   = ! empty( $legacy_data[0] ) ? 'post-legacy' : null;
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( $legacy_id ); ?>>
	<header class="entry-header">
		<?php
		idg_base_theme_eyebrow();

		the_title( '<h1 class="entry-title">', '</h1>' );

		idg_base_theme_subheadline();

		idg_base_theme_share_icons();

		?>

		<div class="entry-meta">
			<?php idg_base_theme_byline(); ?>
		</div><!-- .entry-meta -->

	</header><!-- .entry-header -->

	<hr class="wp-block-separator" />

	<section class='layout--right-rail'>
		<div class="wp-block-columns">
			<div class="wp-block-column">
				<?php
				if ( get_post_meta( get_the_ID(), 'featured_video_id', true ) ) {
					idg_base_theme_post_video();
				} else {
					idg_base_theme_post_thumbnail();
				}

				?>

				<div class="entry-content">
					<div id="post-toc"></div>
					<?php
					the_content();
					idg_base_theme_end_content_price_comparison_block();
					idg_base_theme_affiliate_disclaimer();
					idg_base_theme_related_tags();
					wp_link_pages(
						[
							'before'           => '<div class="pagination">',
							'after'            => '</div>',
							'next_or_number'   => 'next_and_number',
							'nextpagelink'     => __( '>' ),
							'previouspagelink' => __( '<' ),
							'pagelink'         => '%',
							'echo'             => '...',
						] 
					);
					do_action( 'idg_article_footer' );
					idg_base_theme_podcast_footer();
					idg_base_theme_post_bio();
					idg_base_theme_post_social();
					idg_base_theme_post_copyright( false );
					?>
				</div>
			</div>
			<div class="wp-block-column">
				<?php get_sidebar(); ?>
			</div>
		</div>
	</section>
</article><!-- #post-<?php the_ID(); ?> -->
