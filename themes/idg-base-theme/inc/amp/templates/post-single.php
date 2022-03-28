<?php

$post_title = get_the_title();

?>

<?php require locate_template( 'inc/amp/partials/header.php' ); ?>

<article class="site-main">
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
	<hr class="wp-block-separator">

	<?php

	if ( get_post_meta( get_the_ID(), 'featured_video_id', true ) ) {
		idg_base_theme_post_video();
	} else {
		idg_base_theme_post_thumbnail();
	}

	?>

	<div class="entry-content">
		<?php
			// phpcs:ignore
			idg_base_theme_amp_toc();
			echo $this->get( 'post_amp_content' );
			idg_base_theme_end_content_price_comparison_block();
			idg_base_theme_affiliate_disclaimer();
		?>
	</div>

	<footer>
		<?php
		idg_base_theme_related_tags();
		idg_base_theme_podcast_footer();
		idg_base_theme_post_bio();
		idg_base_theme_post_social();
		idg_base_theme_post_copyright( false );

		do_action( 'idg_before_footer' );
		?>
	</footer>

</article>

<?php require locate_template( 'inc/amp/partials/footer.php' ); ?>
