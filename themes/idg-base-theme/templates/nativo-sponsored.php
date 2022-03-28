<?php
/* Template Name: Nativo */
get_header(
	'nativo',
	[
		'body_class' => [ 'single-post' ],
	]
);
?>
	<main id="primary" class="site-main">
		<?php
		idg_base_theme_eyebrow();
		the_title( '<h1 class="entry-title">', '</h1>' );
		?>
		<div class="entry-meta">
			<?php idg_base_theme_sponsorship_tooltip(); ?>
		</div><!-- .entry-meta -->

		<div class='temp-featured-image'>
			<?php idg_base_theme_post_thumbnail(); ?>
		</div>
		<?php
		while ( have_posts() ) :
			the_post();

			get_template_part( 'template-parts/content', 'nativo' );
		endwhile;
		?>
	</main><!-- #main -->
	<script>
		// Show an element
		var show = function (elem) {
			elem.style.display = 'block';
		};

		// Hide an element
		var hide = function (elem) {
			elem.style.display = 'none';
		};

		// Toggle element visibility
		var toggle = function (elem) {
			// If the element is visible, hide it
			if (window.getComputedStyle(elem).display === 'block') {
				hide(elem);
				return;
			}
			// Otherwise, show it
			show(elem);
		};

		// Listen to all click events on the document
		document.addEventListener('click', function (event) {
			// If the clicked element does not have and is not contained by an element with the .click-me class, ignore it
			if (!event.target.closest('.learn-more') && !event.target.closest('#learn-more-close')) {
				return;
			}

			event.preventDefault();
			toggle(document.querySelector('#learn-more-popup'));
		});
	</script>
<?php
get_footer();
