<?php
/**
 * Contains code to display articles listing on static pages
 *
 * @package Idg-base-theme
 */

$post_title            = get_the_title();
$post_link             = get_the_permalink();
$featured_image        = wp_get_attachment_image_url( get_post_thumbnail_id(), '300-r3:2' );
$featured_image_srcset = wp_get_attachment_image_srcset( get_post_thumbnail_id(), '300-r3:2' );
$excerpt               = idg_base_theme_get_the_excerpt();
$author_id             = get_the_author_meta( 'ID' );

$review_score = idg_base_theme_review_score();
$author       = idg_base_theme_get_author_name( intval( $author_id ), intval( $query->post->ID ) );
$video_class  = has_term( 'video', 'article_type' ) ? ' item-image--video' : '';

// Overide for `byline` meta on legacy data.
$legacy_byline = get_post_meta( $query->post->ID, 'byline', true );
if ( ! empty( $legacy_byline ) && is_string( $legacy_byline ) ) {
	$author = $legacy_byline;
}

$timezone = wp_timezone();
$datetime = new \DateTime();
$datetime->setTimeZone( $timezone );
$wp_timestamp_now = strtotime( $datetime->format( 'Y-m-d H:i:s' ) );
$post_date        = human_time_diff( get_post_time(), $wp_timestamp_now );
$story_type_name  = '';
$story_type       = get_the_terms( get_the_ID(), 'story_types' );
if ( $story_type ) {
	$story_type_name = $story_type[0]->name;
}
?>

<article class="item" role="article" aria-label="Article: <?php echo esc_attr( format_for_aria_label( $post_title ) ); ?>" tabindex="0">
	<div class="item-inner">
		<?php if ( $featured_image ) : ?>
			<div class="item-image<?php echo esc_attr( $video_class ); ?>">
			<?php
			if ( $post_link ) {
				printf(
					'<a href="%s" target="_blank" rel="noopener noreferrer"><img src="%s" alt="%s" srcset="%s" /></a>',
					esc_url( $post_link ),
					esc_url( $featured_image ),
					esc_attr( $post_title ),
					esc_html( $featured_image_srcset )
				);
			} else {
				printf(
					'<img src="%s" alt="" srcset="%s" />',
					esc_url( $featured_image ),
					esc_html( $featured_image_srcset )
				);
			}
			?>
			</div>
		<?php endif; ?>
		<div class="item-text">
			<div class="item-text-inner">
			<?php idg_base_theme_feed_eyebrow(); ?>

			<?php if ( $post_title ) : ?>
				<h3>
				<?php
					printf( '<a href="%s">%s</a>', esc_url( $post_link ), esc_html( $post_title ) );
				?>
				</h3>
			<?php endif; ?>

			<?php if ( $excerpt ) : ?>
				<span class="item-excerpt">
					<?php
						printf( '%s', wp_kses_post( $excerpt ) );
					?>
				</span>
			<?php endif; ?>

			<div class="item-meta">
				<?php if ( ! empty( $author ) && 'brandpost' !== strtolower( $story_type_name ) ) : ?>
					<span class="item-byline"><?php printf( 'By %s', esc_html( $author ) ); ?></span>
				<?php endif; ?>
				<?php if ( $post_date ) : ?>
				<span class="item-date">
					<?php
					printf( '%s ago', esc_html( $post_date ) );
					?>
				</span>
				<?php endif; ?>
				<?php if ( $review_score ) : ?>
				<span class="item-score">
					<?php
					printf(
						'<div class="starRating" style="--rating: %s;" aria-label="%s"></div>',
						esc_attr( $review_score ),
						esc_attr( __( 'Rating of this product is' ) . $review_score . __( 'out of 5.' ) )
					);
					?>
				</span>
				<?php endif; ?>
			</div>

			</div>
		</div>
	</div>
</article>
