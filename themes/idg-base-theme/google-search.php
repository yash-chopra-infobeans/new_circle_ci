<?php
/*
Template Name: Google Programmable Search
*/
get_header();

$google_search_options = cf_get_value( 'global_settings', 'google_search', 'google_programmable_search' );
$search_engine_id      = $google_search_options['engine_id'];
$search_title          = $google_search_options['title'];
?>

	<main id="primary" class="site-main">

		<article id="page-<?php the_ID(); ?>" <?php post_class(); ?>>
			<div class="entry-content">

			<header class="entry-header">
				<?php
				if ( isset( $search_title ) ) {
					printf( '<h1 class="entry-title">%s</h1>', esc_attr( $search_title ? $search_title : __( 'Search', 'idg-base-theme' ) ) );
				}
				?>
			</header>

			<?php
			if ( isset( $search_engine_id ) ) {
				printf(
					'
				<div class="gcse-search"></div>',
					esc_attr( $search_engine_id )
				);
			}
			?>

			</div><!-- .entry-content -->
		</article><!-- #post-<?php the_ID(); ?> -->

	</main><!-- #main -->

<?php
get_footer();
