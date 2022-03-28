<article id="post-<?php the_ID(); ?>" <?php post_class( 'nativo' ); ?>>
	<header class="sponsored-brandpost sponsored-post">
		<div id="learn-more-popup" style="display:none;">
			<p>
				<?php esc_html_e( "SponsoredPosts are written and edited by members of our sponsor community. SponsoredPosts create an opportunity for an individual sponsor to provide insight and commentary from their point-of-view directly to Macworld's audience. The Macworld editorial team does not participate in the writing or editing of SponsoredPosts.", 'idg-base-theme' ); ?>
			</p>
			<div class="popup-close-btn"><a id="learn-more-close" href="#"><?php esc_html_e( 'CLOSE', 'idg-base-theme' ); ?></a></div>
		</div>
		<div class="brandpost-logo"><a href="/native"><!-- @AuthorLogo --></a></div>
		<div class="brandpost-head">
			<div class="brandpost-line1"><span class="sponsored-post">SponsoredPost</span> &nbsp; <span class="sponsored-by">Sponsored by <!-- @Author --></span> <span class="divider">|</span> <a href="#" class="learn-more">Learn More</a></div>
		</div>
	</header>
	<section class="layout--right-rail">
		<div class="wp-block-columns">
			<div class="wp-block-column">
				<div class="entry-content"></div>
			</div>
			<div class="wp-block-column"></div>
		</div>
	</section>
</article><!-- #post-<?php the_ID(); ?> -->
