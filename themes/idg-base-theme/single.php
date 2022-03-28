<?php
get_header();

?>

	<main id="primary" class="site-main">

	<?php
	while ( have_posts() ) :
		the_post();

		idg_base_theme_breadcrumbs( 'single' );

		if ( idg_base_theme_get_sponsorship( get_the_ID() ) ) {
			get_template_part( 'template-parts/sponsorship-header' );
		}

		get_template_part( 'template-parts/content', get_post_type() );

		idg_base_theme_show_sponsored_links();
	endwhile;
	?>

	</main><!-- #main -->

<?php

do_action( 'idg_single_footer' );

get_footer();
