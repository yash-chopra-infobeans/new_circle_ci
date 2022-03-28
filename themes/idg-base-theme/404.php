<?php
get_header();
?>

	<main id="primary" class="site-main">

		<section class="error-404 not-found">
			<header class="page-header">
				<h1 class="page-title">
				<?php esc_html_e( 'Oops! That page can&rsquo;t be found.', 'idg-base-theme' ); ?>
				</h1>
			</header><!-- .page-header -->

			<div class="page-content">
				<p><?php esc_html_e( 'It looks like nothing was found at this location.', 'idg-base-theme' ); ?></p>
			</div><!-- .page-content -->
		</section><!-- .error-404 -->

	</main><!-- #main -->

<?php
get_footer();
