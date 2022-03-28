<!-- TODO: Set template up correctly -->
<section class="no-results not-found">
	<div class="page-content">
		<?php
		if ( is_search() ) :
			?>

			<p>
			<?php
			esc_html_e(
				'Sorry, but nothing matched your search terms. Please try again with some different keywords.',
				'idg-base-theme'
			);
			?>
				</p>
			<?php
			get_search_form();

		else :
			?>

			<p>
			<?php
			esc_html_e(
				'It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.',
				'idg-base-theme'
			);
			?>
			</p>
			<?php
			get_search_form();

		endif;
		?>
	</div><!-- .page-content -->
</section><!-- .no-results -->
