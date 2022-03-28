<?php
/**
 * Template tags handler.
 *
 * @package IDG.
 */

use function \IDG\Base_Theme\Utils\is_amp;

if ( ! function_exists( 'idg_base_theme_posted_on' ) ) {
	/**
	 * Prints HTML with meta information for the current post-date/time.
	 */
	function idg_base_theme_posted_on() {
		$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time>';

		$time_string = sprintf(
			$time_string,
			esc_attr( get_the_date( DATE_W3C ) ),
			esc_html( get_the_date( 'M j, Y g:i a T' ) )
		);

		echo '<span class="posted-on">' . $time_string . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	}
}

if ( ! function_exists( 'idg_base_theme_publication_site' ) ) {
	/**
	 * Prints HTML with meta information for the current publishing site.
	 */
	function idg_base_theme_publication_site() {
		$post_id          = get_the_ID();
		$publication_id   = get_post_meta( $post_id, 'primary_publication_id', true );
		$publication_term = idg_get_publication_by_id( $publication_id );
		$sponsored_post   = get_the_terms( $post_id, 'sponsorships' );
		$podcast_series   = get_the_terms( $post_id, 'podcast_series' );
		$blog             = get_the_terms( $post_id, 'blogs' );

		// we don't need to add publication site to a sponsored, podcast or blog.
		if ( ! $publication_term || $sponsored_post || $podcast_series || $blog ) {
			return;
		}

		echo '<span class="publication-site"> ' . esc_html( $publication_term->name ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

/**
 * Prints HTML with meta information for the current author.
 *
 * @SuppressWarnings(PHPMD)
 */
if ( ! function_exists( 'idg_base_theme_posted_by' ) ) {
	/**
	 * Prints HTML with meta information for the current author.
	 *
	 * @param boolean $show_job_title whether to show the job title field.
	 * @param boolean $show_author whether to show the author field.
	 */
	function idg_base_theme_posted_by( $show_job_title = true, $show_author = true ) {

		if ( ! $show_author && ! $show_job_title ) {
			return;
		}

		$post_id = get_the_ID();

		$author_id        = get_the_author_meta( 'ID' );
		$author_job_title = get_user_meta( $author_id, 'job_title', true );
		$author           = idg_base_theme_get_author_name( intval( $author_id ), intval( $post_id ) );
		$slugged_author   = sanitize_title( get_the_author_meta( 'display_name', $author_id ) );

		// Overide for `byline` meta on legacy data.
		$legacy_byline = get_post_meta( $post_id, 'byline', true );

		if ( ! empty( $legacy_byline ) && is_string( $legacy_byline ) && $show_author ) {
			echo '<span class="byline">';
			printf(
				/* translators: %s: post author. */
				esc_html_x( 'By %s', 'post author', 'idg-base-theme' ),
				'<span class="author vcard">' . esc_html( $legacy_byline ) . '</span>'
			);
			echo '</span>';
			return;
		}
		// Never show `job_title` if using legacy byline.
		if ( $legacy_byline && ! $show_author && $show_job_title ) {
			return;
		}

		if ( ! $author ) {
			return;
		}

		if ( idg_base_theme_is_no_author( $author_id ) ) {
			$vcard_string = esc_html( $author );
		} else {
			$vcard_string = sprintf(
				'<a href="%s">%s</a>',
				esc_url( get_author_posts_url( $author_id, $slugged_author ) ),
				esc_html( $author )
			);
		}

		$byline = sprintf(
			/* translators: %s: post author. */
			esc_html_x( 'By %1$s%2$s%3$s', 'post author', 'idg-base-theme' ),
			'<span class="author vcard">',
			$vcard_string,
			'</span>'
		);

		if ( ! $show_author ) {
			$job_title = $author_job_title ? '<span class="job-title">' . esc_html( $author_job_title ) . ' </span>' : '';
		} else {
			$job_title = $author_job_title ? ', <span class="job-title">' . esc_html( $author_job_title ) . '</span>' : '';
		}


		if ( $show_author && $show_job_title ) {
			echo '<span class="byline"> ' . $byline . $job_title . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} elseif ( $show_author ) {
			echo '<span class="byline"> ' . $byline . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} elseif ( $show_job_title ) {
			echo '<span class="byline"> ' . $job_title . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

	}
}

/**
 * Displays an optional post thumbnail.
 *
 * @SuppressWarnings(PHPMD)
 */
if ( ! function_exists( 'idg_base_theme_post_thumbnail' ) ) {
	/**
	 * Displays an optional post thumbnail.
	 *
	 * Wraps the post thumbnail in an anchor element on index views, or a div
	 * element when on single views.
	 */
	function idg_base_theme_post_thumbnail() {
		if ( post_password_required() || is_attachment() || ! has_post_thumbnail() ) {
			return;
		}

		if ( is_singular() ) :
			?>

			<div class="post-thumbnail">
				<?php
				the_post_thumbnail( 'post-thumbnail', [ 'data-hero' => '' ] );
				$caption           = get_the_post_thumbnail_caption();
				$featured_image_id = get_post_thumbnail_id( get_the_ID() );
				$credit            = get_post_meta( $featured_image_id, 'credit', true );
				$credit_url        = get_post_meta( $featured_image_id, 'credit_url', true );

				if ( ! empty( $caption ) || ! empty( $credit ) ) :
					?>
				<div class="post-thumbnail-text">
					<?php

					if ( ! empty( $credit_url ) ) {
						echo '<a href="' . esc_url( $credit_url ) . '" target="_blank">';
					}

					echo '<span class="credit">' . esc_html( $credit ) . '</span>';

					if ( ! empty( $credit_url ) ) {
						echo '</a>';
					}
					?>
				</div>
				<?php endif; ?>
			</div><!-- .post-thumbnail -->

		<?php else : ?>

			<a class="post-thumbnail" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
				<?php
					the_post_thumbnail(
						'post-thumbnail',
						[
							'alt' => the_title_attribute(
								[
									'echo' => false,
								]
							),
						]
					);
				?>
			</a>

			<?php
		endif; // End is_singular().
	}
}

/**
 * Displays an optional post thumbnail.
 *
 * @SuppressWarnings(PHPMD)
 */
if ( ! function_exists( 'idg_base_theme_post_video' ) ) {
	/**
	 * Displays an optional post thumbnail.
	 *
	 * Wraps the post thumbnail in an anchor element on index views, or a div
	 * element when on single views.
	 */
	function idg_base_theme_post_video() {
		$attachment_id = get_post_meta( get_the_ID(), 'featured_video_id', true );
		$attachment    = get_post( $video_id );

		// If not featured video set or attachment doesn't exist, just return.
		if ( ! $attachment_id || ! $attachment ) {
			return;
		}

		$jw_player_media_id = get_post_meta( $attachment_id, 'jw_player_media_id', true );

		// TO DO: If video is not JW Player video render an alternative way, for now just return.
		if ( ! $jw_player_media_id ) {
			return;
		}

		if ( is_amp() ) {
			idg_jw_player_amp_video(
				[
					'data-media-id' => $jw_player_media_id,
				],
			);
		} else {
			printf( '<div id="jwplayer--featuredVideo" data-media-id="%s" class="jwplayer"></div>', esc_attr( $jw_player_media_id ) );
		}
	}
}

/**
 * Render jw video amp player.
 *
 * @SuppressWarnings(PHPMD)
 */
if ( ! function_exists( 'idg_jw_player_amp_video' ) ) {
	/**
	 * Render jw video amp player
	 *
	 * @param array $player_args - Player args.
	 * @param array $dock_args - Dock args.
	 * @return string
	 */
	function idg_jw_player_amp_video( $player_args = [], $dock_args = [] ) {
		$player = cf_get_value( 'third_party', 'jw_player', 'config.amp_player_library_id' );

		if ( empty( $player ) ) {
			// IDG - AMP embed player - https://cdn.jwplayer.com/libraries/8IS0Lb8W.js.
			$player = 'wySF9V4I';
		}

		$attributes = array_merge(
			[
				'layout'         => 'responsive',
				'width'          => 16,
				'height'         => 9,
				'data-player-id' => $player,
			],
			$player_args
		);

		if ( ! isset( $attributes['data-media-id'] ) || ! isset( $attributes['data-player-id'] ) ) {
			return '';
		}

		$jwplayer = '<amp-jwplayer ' . join(
			' ',
			array_map(
				function( $key ) use ( $attributes ) {
					if ( is_bool( $attributes[ $key ] ) ) {
						return $attributes[ $key ] ? $key : '';
					}

					return $key . '="' . $attributes[ $key ] . '"';
				},
				array_keys( $attributes )
			)
		) . ' />';

		if ( ! $attributes['dock'] || ! $dock_args['id'] ) {
			return printf( wp_kses_post( $jwplayer ) );
		}

		$dock_attributes = array_merge(
			[
				'layout' => 'responsive',
				'width'  => '16',
				'height' => '9',
			],
			$dock_args
		);

		$dock = '<amp-layout ' . join(
			' ',
			array_map(
				function( $key ) use ( $dock_attributes ) {
					if ( is_bool( $dock_attributes[ $key ] ) ) {
						return $dock_attributes[ $key ] ? $key : '';
					}
					return $key . '="' . $dock_attributes[ $key ] . '"';
				},
				array_keys( $dock_attributes )
			)
		) . ' />';

		return printf( wp_kses_post( $jwplayer . $dock ) );
	}
}

if ( ! function_exists( 'idg_render_floating_video' ) ) {
	/**
	 * Displays an optional post thumbnail.
	 *
	 * Wraps the post thumbnail in an anchor element on index views, or a div
	 * element when on single views.
	 */
	function idg_render_floating_video() {
		$playlist = cf_get_value( 'third_party', 'jw_player', 'config.floating_player_playlist_id' );

		if ( ! $playlist ) {
			return;
		}

		?>

		<div class="jwPlayer--floatingContainer">
			<div id="jwplayer--floatingVideo" class="jwplayer" data-media-id="<?php echo esc_attr( $playlist ); ?>"></div>
		</div>

		<?php
	}
}

/**
 * Prints HTML with image for the current author.
 *
 * @SuppressWarnings(PHPMD)
 */
if ( ! function_exists( 'idg_base_theme_author_image' ) ) {
	/**
	 * Prints HTML with image for the current author.
	 */
	function idg_base_theme_author_image() {
		$post_id = get_the_ID();

		// If no author image and not a podcast or blog, return.
		$podcast_series = get_the_terms( $post_id, 'podcast_series' );
		$blog           = get_the_terms( $post_id, 'blogs' );
		if ( get_avatar( get_the_author_meta( 'ID' ) ) === 0 && ! $podcast_series && ! $blog ) {
			return;
		}

		if ( $blog ) {
			// If it's a blog use the blog logo first.
			$blog_id      = $blog[0]->term_id;
			$term_logo_id = get_term_meta( $blog_id, 'logo', true );
		} elseif ( $podcast_series ) {
			// If it's a podcast use the podcast logo.
			$podcast_series_id = $podcast_series[0]->term_id;
			$term_logo_id      = get_term_meta( $podcast_series_id, 'logo', true );
		}

		if ( $term_logo_id ) {
			// If there is as logo get the logo info.
			$term_logo_array  = wp_get_attachment_image_src( $term_logo_id, 'thumbnail' );
			$term_logo_srcset = wp_get_attachment_image_srcset( $term_logo_id, '' );
			$term_logo_url    = $term_logo_array[0];
		}

		if ( ( $blog || $podcast_series ) && ! $term_logo_url ) {
			// If podcast or blog but no logo don't show `author-image` section.
			return;
		}

		$author_id     = get_the_author_meta( 'ID' );
		$post_id       = get_the_ID();
		$legacy_byline = get_post_meta( $post_id, 'byline', true );

		if ( $legacy_byline || idg_base_theme_is_no_author( $author_id ) ) {
			return;
		}

		$user_avatar = get_avatar( $author_id, 150 );

		if ( $term_logo_url ) {
			printf( '<div class="author-image"><img src="%s" srcset="%s" /></div>', esc_url( $term_logo_url ), esc_html( $term_logo_srcset ) );
		} elseif ( $user_avatar ) {
			printf( '<div class="author-image">%s</div>', $user_avatar ); //phpcs:ignore
		}

	}
}

if ( ! function_exists( 'idg_base_theme_subheadline' ) ) {
	/**
	 * Prints HTML with the subheadline.
	 */
	function idg_base_theme_subheadline() {
		$post_meta = get_post_meta( get_the_ID(), 'multi_title', true );

		if ( ! $post_meta ) {
			return;
		}

		$multi_title = json_decode( $post_meta );
		$headline    = $multi_title->titles->headline;

		if ( ! isset( $headline->additional->headline_subheadline ) ) {
			return;
		}

		?>
			<div class="subheadline">
				<?php echo esc_html( $headline->additional->headline_subheadline ); ?>
			</div>
		<?php
	}
}

if ( ! function_exists( 'idg_base_theme_get_blog_title' ) ) {
	/**
	 * Prints HTML with the title of blog from the `blogs` taxonomy.
	 */
	function idg_base_theme_get_blog_title() {
		$blogs = get_the_terms( get_the_ID(), 'blogs' );

		if ( ! isset( $blogs ) ) {
			return;
		}

		?>
		<div class="blog-title">
			<?php echo esc_html( $blogs[0]->name ); ?>
		</div>
		<?php
	}
}

if ( ! function_exists( 'idg_base_theme_get_podcast_title' ) ) {
	/**
	 * Prints HTML with the title of podcast from the `podcast_series` taxonomy.
	 */
	function idg_base_theme_get_podcast_title() {
		$podcast = get_the_terms( get_the_ID(), 'podcast_series' );

		if ( ! isset( $podcast ) ) {
			return;
		}

		?>
		<div class="podcast-title">
			<?php echo esc_html( $podcast[0]->name ); ?>
		</div>
		<?php
	}
}

/**
 * Prints HTML with meta information for the current posts copyright info.
 *
 * @SuppressWarnings(PHPMD)
 */
if ( ! function_exists( 'idg_base_theme_post_copyright' ) ) {
	/**
	 * Prints HTML with meta information for the current posts copyright info.
	 *
	 * @param string $footer footer.
	 */
	function idg_base_theme_post_copyright( $footer ) {
		$copyright_info_footer = cf_get_value( 'global_settings', 'footer_content', 'copyright_text' )['footer_copyright_text'];
		$copyright_info        = get_post_meta( get_the_ID(), '_idg_copyright_info', true );

		if ( true === $footer ) {
			if ( ! empty( $copyright_info_footer ) ) {
				?>
			<div class="entry-copyright">
				<?php
				printf(
					'<a href="%s">%s</a>',
					esc_url( 'https://www.idg.com/terms-of-service-agreement/' ),
					esc_html( $copyright_info_footer )
				);
				?>
			</div>
				<?php
			}
		} else {
			if ( ! empty( $copyright_info ) ) {
				?>
			<div class="entry-copyright">
				<?php echo esc_html( $copyright_info ); ?>
			</div>
				<?php
			}
		}
	}
}

if ( ! function_exists( 'idg_base_theme_post_bio' ) ) {
	/**
	 * Prints HTML with meta information for the current posts author bio.
	 */
	function idg_base_theme_post_bio() {
		$author_id          = get_the_author_meta( 'ID' );
		$author_description = get_user_meta( $author_id, 'description', true );

		if ( idg_base_theme_is_no_author( $author_id ) || empty( $author_id ) ) {
			return;
		}

		?>
			<div class="entry-bio">
				<?php echo wp_kses_post( $author_description ); ?>
			</div>
		<?php
	}
}

/**
 * Prints HTML with meta information for the current posts author social info.
 *
 * @SuppressWarnings(PHPMD)
 */
if ( ! function_exists( 'idg_base_theme_post_social' ) ) {
	/**
	 * Prints HTML with meta information for the current posts author social info.
	 */
	function idg_base_theme_post_social() {
		$post_id       = get_the_ID();
		$author_id     = get_the_author_meta( 'ID' );
		$legacy_byline = get_post_meta( $post_id, 'byline', true );

		if ( idg_base_theme_is_no_author( $author_id ) || empty( $author_id ) || isset( $legacy_byline ) && is_string( $legacy_byline ) ) {
			return;
		}

		// Social meta fields.
		$author_url          = get_author_posts_url( $author_id );
		$author_twitter      = get_user_meta( $author_id, 'twitter', true );
		$author_linkedin     = get_user_meta( $author_id, 'linkedin', true );
		$author_social_email = get_user_meta( $author_id, 'social_email', true );
		$author_rss          = get_author_feed_link( $author_id );
		$author_facebook     = get_user_meta( $author_id, 'facebook', true );

		// Social meta fields display.
		$hide_author       = get_user_meta( $author_id, 'hide_author_on_articles', true );
		$hide_twitter      = get_user_meta( $author_id, 'hide_twitter_on_articles', true );
		$hide_linkedin     = get_user_meta( $author_id, 'hide_linkedin_on_articles', true );
		$hide_social_email = get_user_meta( $author_id, 'hide_social_email_on_articles', true );
		$hide_rss          = get_user_meta( $author_id, 'hide_rss_on_articles', true );
		$hide_facebook     = get_user_meta( $author_id, 'hide_facebook_on_articles', true );

			// Return if all are hidden.
		if ( $hide_author && $hide_twitter && $hide_linkedin && $hide_social_email && $hide_rss && $hide_facebook ) {
			return;
		}

		?>
			<ul class="entry-social">
				<li><?php echo esc_html__( 'Follow', 'idg-base-theme' ); ?></li>
				<?php
				if ( ! empty( $author_url ) && ! $hide_author ) {
					printf( '<li><a href="%s" target="_blank" rel="noopener noreferrer nofollow">%s</a></li>', esc_url( $author_url ), wp_kses( get_idg_asset( '/icons/user-alt.svg' ), apply_filters( 'idg_svg_allowed_tags', [] ) ) );
				}
				if ( ! empty( $author_twitter ) && ! $hide_twitter ) {
					printf( '<li><a href="%s" target="_blank" rel="noopener noreferrer nofollow">%s</a></li>', esc_url( $author_twitter ), wp_kses( get_idg_asset( '/icons/twitter.svg' ), apply_filters( 'idg_svg_allowed_tags', [] ) ) );
				}
				if ( ! empty( $author_linkedin ) && ! $hide_linkedin ) {
					printf( '<li><a href="%s" target="_blank" rel="noopener noreferrer nofollow">%s</a></li>', esc_url( $author_linkedin ), wp_kses( get_idg_asset( '/icons/linkedin.svg' ), apply_filters( 'idg_svg_allowed_tags', [] ) ) );
				}
				if ( ! empty( $author_social_email ) && ! $hide_social_email ) {
					printf( '<li><a href="mailto:%s">%s</a></li>', esc_attr( $author_social_email ), wp_kses( get_idg_asset( '/icons/envelope.svg' ), apply_filters( 'idg_svg_allowed_tags', [] ) ) );
				}
				if ( ! empty( $author_rss ) && ! $hide_rss ) {
					printf( '<li><a href="%s" target="_blank" rel="noopener noreferrer nofollow">%s</a></li>', esc_url( $author_rss ), wp_kses( get_idg_asset( '/icons/rss.svg' ), apply_filters( 'idg_svg_allowed_tags', [] ) ) );
				}
				if ( ! empty( $author_facebook ) && ! $hide_facebook ) {
					printf( '<li><a href="%s" target="_blank" rel="noopener noreferrer nofollow">%s</a></li>', esc_url( $author_facebook ), wp_kses( get_idg_asset( '/icons/facebook.svg' ), apply_filters( 'idg_svg_allowed_tags', [] ) ) );
				}
				?>
			</ul>
		<?php
	}
}

if ( ! function_exists( 'idg_base_theme_eyebrow' ) ) {
	/**
	 * Prints HTML with eyebrow using eyebrow logic.
	 */
	function idg_base_theme_eyebrow() {

			$eyebrow_info  = idg_base_theme_get_eyebrow( get_the_ID() );
			$eyebrow       = $eyebrow_info['eyebrow'];
			$eyebrow_style = $eyebrow_info['eyebrow_style'];

		if ( empty( $eyebrow ) ) {
			return;
		}

		?>
			<div class="entry-eyebrow entry-eyebrow--<?php echo esc_attr( $eyebrow_style ); ?>">
				<?php echo esc_html( $eyebrow ); ?>
			</div>
		<?php
	}
}

if ( ! function_exists( 'idg_base_theme_feed_eyebrow' ) ) {
	/**
	 * Prints HTML with eyebrow using feed eyebrow logic.
	 */
	function idg_base_theme_feed_eyebrow() {
		$eyebrow_info        = idg_base_theme_get_eyebrow( get_the_ID() );
		$eyebrow             = $eyebrow_info['eyebrow_feed_title'];
		$eyebrow_style       = $eyebrow_info['eyebrow_feed_style'];
		$eyebrow_sponsorship = $eyebrow_info['eyebrow_sponsorship'];

		if ( empty( $eyebrow ) ) {
			return;
		}

		if ( 'BrandPost' === $eyebrow ) {
			printf( '<span class="item-eyebrow item-eyebrow--%s">%s</span>', esc_attr( $eyebrow_style ), esc_html( $eyebrow . ' ' ) );
			if ( $eyebrow_sponsorship ) {
				printf( '<span class="item-eyebrow-sponsored-by-text">%s</span>', 'Sponsored by ' . esc_html( $eyebrow_sponsorship ) );
			}
		} elseif ( 'Sponsor Podcast' === $eyebrow ) {
			printf( '<span class="item-eyebrow item-eyebrow--%s">%s</span>', esc_attr( $eyebrow_style ), esc_html( $eyebrow . ' ' ) );
			if ( $eyebrow_sponsorship ) {
				printf( '<span class="item-eyebrow-sponsored-by-text">%s</span>', 'in partnership with ' . esc_html( $eyebrow_sponsorship ) );
			}
		} else {
			printf( '<span class="item-eyebrow item-eyebrow--%s">%s</span>', esc_attr( $eyebrow_style ), esc_html( $eyebrow . ' ' ) );
		}

	}
}

/**
 * Prints HTML with pagination. Uses button if page 1.
 *
 * @SuppressWarnings(PHPMD)
 */
if ( ! function_exists( 'idg_base_theme_pagination' ) ) {
	/**
	 * Prints HTML with pagination. Uses button if page 1.
	 */
	function idg_base_theme_pagination() {
		if ( ! is_paged() && get_next_posts_link() ) {
			printf(
				'
			<div class="articleFeed-button">
				<a href="%s" aria-label="View More" role="button" class="btn">
					%s
				</a>
			</div>',
				esc_url( get_next_posts_page_link() ),
				esc_html__( 'More Stories', 'idg-base-theme' )
			);
		} else {
			?>
			<div class="pagination">
				<?php
				global $wp_query;
				$big = PHP_INT_MAX;
				echo wp_kses_post(
					paginate_links(
						[
							'base'      => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
							'format'    => '?paged=%#%',
							'current'   => max( 1, get_query_var( 'paged' ) ),
							'total'     => $wp_query->max_num_pages,
							'prev_next' => true,
							'end_size'  => 1,
							'mid_size'  => 1,
						]
					)
				);
				?>
			</div>
			<?php
		}
	}
}

/**
 * HTML for Sharing Icons.
 *
 * @SuppressWarnings(PHPMD)
 */
if ( ! function_exists( 'idg_base_theme_share_icons' ) ) {
	/**
	 * HTML for Sharing Icons.
	 */
	function idg_base_theme_share_icons() {
			$share_icons = apply_filters( 'idg_post_share_options', [] );
		if ( is_page_template( 'templates/nativo-sponsored.php' ) ) {
			array_splice( $share_icons, 3 );
		}    
		?>
			<div class="share-icons--wrapper">
				<ul class="share-icons">
					<?php
					foreach ( $share_icons as $share_icon ) {
						$extra_attributes = [];

						if (
						! empty( $share_icon['extra_attributes'] ) &&
						is_array( $share_icon['extra_attributes'] )
						) {
							foreach ( $share_icon['extra_attributes'] as $attribute => $value ) {
								$extra_attributes[] = sprintf( '%s=%s ', $attribute, esc_attr( $value ) );
							}
						}
						printf(
							'<li
								class="share-icon share-icons--%s"
								%s
							>
								<a
								href="%s"
								title="%s"
								target="_blank"
								rel="noopener"
								> %s </a>
							</li>',
							esc_attr( $share_icon['icon-name'] ),
							wp_kses_post( implode( ' ', $extra_attributes ) ),
							esc_attr( $share_icon['icon-url'] ),
							esc_attr( $share_icon['icon-text'] ),
							wp_kses( get_idg_asset( '/icons/' . $share_icon['icon-file'] ), apply_filters( 'idg_svg_allowed_tags', [] ) )
						);
					}
					?>
				</ul>
			</div><!-- .share-icons -->
		<?php
	}
}

/**
 * Prints HTML with footer using footer logic.
 *
 * @SuppressWarnings(PHPMD)
 */
if ( ! function_exists( 'idg_base_theme_podcast_footer' ) ) {
	/**
	 * Prints HTML with footer using footer logic.
	 */
	function idg_base_theme_podcast_footer() {

		$podcast_series = get_the_terms( get_the_ID(), 'podcast_series' );

		if ( empty( $podcast_series ) ) {
			return;
		}

		$podcast_series_id = $podcast_series[0]->term_id;
		$apple_podcast_url = get_term_meta( $podcast_series_id, 'apple_podcast_url', true );
		$google_play_url   = get_term_meta( $podcast_series_id, 'google_play_url', true );
		$rss_url           = get_term_meta( $podcast_series_id, 'rss_url', true );
		$archive_url       = get_term_link( $podcast_series_id );
		$podcast_name      = $podcast_series[0]->name;

		if ( empty( $podcast_series ) || empty( $apple_podcast_url ) && empty( $google_play_url ) && empty( $rss_url ) ) {
			return;
		}

		?>
			<div class="entry-footer entry-footer--podcast_series">
				<h3><?php echo esc_html__( 'How do I listen to this podcast ? ', 'idg - base - theme' ); ?></h3>
				<p class="series-blurb">

				<?php
				echo esc_html__(
					'You can listen to this podcast episode right now using the player at the top of this page.
					And you can subscribe to this podcast series from your favourite podcast app on your mobile device to listen
					to any time, so you won\'t miss an episode.
					Just click the desired podcast app\'s button below to subscribe.',
					'idg-base-theme'
				)
				?>
				</p>

				<ul class="url-items-list">
				<span class="subscribe-title">Subscribe:</span>
				<?php
				if ( ! empty( $apple_podcast_url ) ) {
					printf( '<li> <button class="%s"> <a href="%s">Apple Podcasts</a> </button> </li>', 'series-url-button', esc_url( $apple_podcast_url ) );
				}
				if ( ! empty( $google_play_url ) ) {
					printf( '<li> <button class="%s"> <a href="%s">Google Play</a> </button> </li>', 'series-url-button', esc_url( $google_play_url ) );
				}

				if ( ! empty( $rss_url ) ) {
					printf( ' <li> <button class="%s"> <a href="%s">RSS</a> </button> </li>', 'series-url-button', esc_url( $rss_url ) );
				}
				?>
				</ul>

				<?php

				if ( ! empty( $archive_url ) && ! empty( $podcast_name ) ) {
					printf( '<a href="%s" class="%s">Listen to all %s episodes</a>', esc_url( $archive_url ), 'all-tech-episodes', esc_html( $podcast_name ) );
				}
				?>
			</div>
				<?php
	}
}

/**
 * Prints HTML with sponsorship logic.
 *
 * @SuppressWarnings(PHPMD)
 */
if ( ! function_exists( 'idg_base_theme_sponsorship_tooltip' ) ) {
	/**
	 * Prints HTML with sponsorship logic.
	 */
	function idg_base_theme_sponsorship_tooltip() {

		$post_id     = get_the_ID();
		$sponsorship = idg_base_theme_get_sponsorship( $post_id );

		if ( $sponsorship['tooltip'] ) {
			?>
			<div class="tooltip">
			<a
			<?php if ( is_amp() ) : ?>
				on="tap:AMP.setState({isTooltipBoxOpen: true})"
			<?php endif; ?>
				class='tooltip-learn-more'
				role="tooltip"
				aria-describedby="tooltip-p"
			>
				<?php esc_html_e( 'Learn More', 'idg-base-theme' ); ?>
			</a>
			<div
			<?php if ( is_amp() ) : ?>
				[class]="'tooltip-box ' + (isTooltipBoxOpen ? 'is-open' : '')"
			<?php endif; ?>
				class="tooltip-box"
				id='tooltip-box'
			>
				<a
				<?php if ( is_amp() ) : ?>
					on="tap:AMP.setState({isTooltipBoxOpen: false})"
				<?php endif; ?>
					class='tooltip-close'
					role="tooltip"
				>
					<?php idg_asset( '/icons/times-2.svg' ); ?>
				</a>
				<div class='tooltip-text'>
					<p id='tooltip-p'>
						<?php printf( esc_attr( $sponsorship['tooltip'] ) ); ?>
					</p>
				</div>
			</div>
		</div>
			<?php
		}
	}
}

/**
 * Prints HTML with byline using byline logic.
 *
 * @SuppressWarnings(PHPMD)
 */
if ( ! function_exists( 'idg_base_theme_byline' ) ) {
	/**
	 * Prints HTML with byline using byline logic.
	 */
	function idg_base_theme_byline() {

		$post_ID    = get_the_ID();
		$story_type = get_the_terms( $post_ID, 'story_types' );

		if ( $story_type ) {
			$story_type_name = $story_type[0]->name;
		}

		$sponsored_post = get_the_terms( $post_ID, 'sponsorships' );

		if ( has_term( [], 'blogs', get_the_ID() ) ) {

			// Blog.
			idg_base_theme_author_image();
			?>
			<div class="meta-text">
				<div class="meta-text-top">
					<?php idg_base_theme_get_blog_title(); ?>
				</div>

				<div class="meta-text-bottom">
					<?php idg_base_theme_posted_by(); ?>
					<?php idg_base_theme_sponsorship_tooltip(); ?>
					<?php idg_base_theme_posted_on(); ?>
				</div>
			</div>
			<?php
		} elseif ( has_term( [], 'podcast_series', get_the_ID() ) && $sponsored_post ) {

			// Sponsor Podcast.
			$eyebrow_info = idg_base_theme_get_eyebrow( get_the_ID() );
			echo '<div class="meta-text no-flex">';
				printf( '<span class="item-eyebrow item-eyebrow--%s">%s</span>', esc_attr( $eyebrow_info['eyebrow_feed_style'] ), esc_html( $eyebrow_info['eyebrow_feed_title'] . ' ' ) );
			if ( $eyebrow_info['eyebrow_sponsorship'] ) {
				printf( '<span class="item-eyebrow-sponsored-by-text">%s</span>', 'In Partnership with ' . esc_html( $eyebrow_info['eyebrow_sponsorship'] ) );
			}
				idg_base_theme_sponsorship_tooltip();
				idg_base_theme_posted_on();
			echo '</div>';

		} elseif ( has_term( [], 'podcast_series', get_the_ID() ) ) {

			// Podcast.
			idg_base_theme_author_image();
			?>
			<div class="meta-text">
				<div class="meta-text-top">
					<?php idg_base_theme_get_podcast_title(); ?>
				</div>

				<div class="meta-text-bottom">
					<?php idg_base_theme_posted_by(); ?>
					<?php idg_base_theme_sponsorship_tooltip(); ?>
					<?php idg_base_theme_posted_on(); ?>
				</div>
			</div>
			<?php
		} elseif ( 'BrandPost' === $story_type_name && $sponsored_post ) {

			// Sponsor BrandPost.
			$eyebrow_info = idg_base_theme_get_eyebrow( get_the_ID() );
			echo '<div class="meta-text no-flex">';
				printf( '<span class="item-eyebrow item-eyebrow--%s">%s</span>', esc_attr( $eyebrow_info['eyebrow_feed_style'] ), esc_html( $eyebrow_info['eyebrow_feed_title'] . ' ' ) );
			if ( $eyebrow_info['eyebrow_sponsorship'] ) {
				printf( '<span class="item-eyebrow-sponsored-by-text">%s</span>', 'Sponsored by ' . esc_html( $eyebrow_info['eyebrow_sponsorship'] ) );
			}
				idg_base_theme_sponsorship_tooltip();
				idg_base_theme_posted_on();
			echo '</div>';

		} elseif ( 'DealPost' === $story_type_name || 'BrandPost' === $story_type_name ) {

			// Dealpost or Brandpost.
			?>
			<div class="meta-text no-flex">
				<?php idg_base_theme_posted_by(); ?>
				<?php idg_base_theme_sponsorship_tooltip(); ?>
				<?php idg_base_theme_posted_on(); ?>
			</div>
			<?php
		} else {

			// Regular Post.
			idg_base_theme_author_image();
			?>
			<div class="meta-text">
				<div class="meta-text-top">
					<?php idg_base_theme_posted_by( false ); ?>
				</div>

				<div class="meta-text-bottom">
					<?php idg_base_theme_posted_by( true, false ); ?>
					<?php idg_base_theme_sponsorship_tooltip(); ?>
					<?php idg_base_theme_publication_site(); ?>
					<?php idg_base_theme_posted_on(); ?>
				</div>
			</div>
			<?php
		}
	}
}

/**
 * To generate theme related tags.
 *
 * @SuppressWarnings(PHPMD)
 */
if ( ! function_exists( 'idg_base_theme_related_tags' ) ) {
	/**
	 * To generate theme related tags.
	 */
	function idg_base_theme_related_tags() {

		$post_ID       = get_the_ID();
		$get_tag       = get_the_tags( $post_ID );
		$get_cat_order = get_post_meta( $post_ID, '_idg_post_categories', true );
		$get_cat       = get_terms(
			[
				'include'    => $get_cat_order,
				'hide_empty' => false,
				'orderby'    => 'include',
			]
		);

		if ( ! empty( $get_cat_order ) || ! empty( $get_tag ) ) :
			?>
			<ul class='tag-list'>
				<a class='related-cat-title'>Related: </a>
					<?php

					if ( ! empty( $get_cat_order ) ) :

						foreach ( $get_cat as $cat_id ) {
							printf(
								'<li><a class="tag-button">%s</a></li>',
								esc_html( $cat_id->name )
							);
						}
					endif;

					if ( ! empty( $get_tag ) ) :

						foreach ( $get_tag as $tag_id ) {
							printf(
								'<li><a class="tag-button">%s</a></li>',
								esc_html( $tag_id->name )
							);
						}

					endif;
					?>
			</ul>
			<?php
		endif;

	}
}

if ( ! function_exists( 'idg_base_theme_breadcrumbs' ) ) {
	/**
	 * Prints HTML for the site breadcrumb.
	 *
	 * @param string $article_type the article type.
	 */
	function idg_base_theme_breadcrumbs( $article_type ) {
		$breadcrumbs = idg_get_breadcrumbs( $article_type );
		?>
			<div class="<?php echo esc_attr( $article_type ); ?>-breadcrumb">
				<?php
				foreach ( $breadcrumbs as $index => $breadcrumb ) {

					printf(
						'<a href=%s>%s</a>',
						esc_url( $breadcrumb['url'] ),
						count( $breadcrumbs ) - 1 === $index ? esc_html( $breadcrumb['label'] ) : esc_html( $breadcrumb['label'] ) . ' / '
					);
				}
				?>
			</div>
			<?php
	}
}

/**
 * Price comparison block.
 *
 * @SuppressWarnings(PHPMD)
 */
if ( ! function_exists( 'idg_base_theme_end_content_price_comparison_block' ) ) {
	/**
	 * Price comparison block.
	 */
	function idg_base_theme_end_content_price_comparison_block() {
		$content_post = get_post( get_the_ID() );
		$content      = $content_post->post_content;

		$blocks      = parse_blocks( $content );
		$count       = count( $blocks ) - 1;
		$last_block  = $blocks[ $count ];
		$block_names = [ 'idg-base-theme/review-block', 'idg-base-theme/price-comparison-block' ];

		if ( in_array( $last_block['blockName'], $block_names, true ) ) {
			return '';
		}

		foreach ( $blocks as $block ) {
			if ( 'idg-base-theme/review-block' === $block['blockName'] ) {
				$attributes = $block['attrs'];

				if ( isset( $attributes['primaryProductId'] ) ) {
					$comparison_element = idg_render_price_comparison(
						[
							'productId'     => $attributes['primaryProductId'],
							'linksInNewTab' => true,
							'footerText'    => __( 'Price comparison from over 24,000 stores worldwide', 'idg-base-theme' ),
							'position_id'   => '002',
							'position'      => 'Price Comparison Bottom',
							'title'         => $attributes['bestPricingTitle'] ?: __( 'Best Prices Today', 'idg-base-theme' ),
						]
					);

					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					printf( apply_filters( 'idg_linkwrapping', $comparison_element ) );
				}
			}
		}
	}
}

if ( ! function_exists( 'idg_base_theme_affiliate_disclaimer' ) ) {
	/**
	 * Print affliate disclaimer.
	 */
	function idg_base_theme_affiliate_disclaimer() {
		$post_ID         = get_the_ID();
		$story_type      = get_the_terms( $post_ID, 'story_types' );
		$story_type_name = '';
		if ( $story_type ) {
			$story_type_name = $story_type[0]->name;
		}
		if ( 'brandpost' !== strtolower( $story_type_name ) ) {
			$affiliate_content = cf_get_value( 'global_settings', 'affiliate_disclaimer', 'aff_desc' )['aff_desc_text'];
			if ( $affiliate_content ) :
				printf(
					'<div class="affiliate-disclaimer">%s</div>',
					wp_kses_post(
						$affiliate_content
					)
				);
			endif;
		}
	}
}

/**
 * Gets the posts review score if available.
 *
 * @SuppressWarnings(PHPMD)
 */
if ( ! function_exists( 'idg_base_theme_review_score' ) ) {
	/**
	 * Gets the posts review score if available.
	 *
	 * @param int $post_ID id of post, if not defined uses current post.
	 */
	function idg_base_theme_review_score( $post_ID = null ) {

		$id = $post_ID ? $post_ID : get_the_ID();

		$content_post = get_post( $id );
		$content      = $content_post->post_content;

		$blocks = parse_blocks( $content );
		foreach ( $blocks as $block ) {
			if ( 'idg-base-theme/review-block' === $block['blockName'] ) {
				$attributes = $block['attrs'];

				foreach ( $attributes as $key => $attr ) {
					if ( 'rating' === $key && $attr ) {
						return $attr;
					}
				}
			}
		}
	}
}

/**
 * Function for AMP toc.
 *
 * @SuppressWarnings(PHPMD)
 */
if ( ! function_exists( 'idg_base_theme_amp_toc' ) ) {
	/**
	 * Function for AMP toc.
	 */
	function idg_base_theme_amp_toc() {

		$toc_list = [];
		$dom      = new DOMDocument();
		$content  = get_the_content();
		$dom->loadHTML( $content );
		$finder    = new DomXPath( $dom );
		$classname = 'toc';
		$nodes     = $finder->query( "//*[contains(@class, '$classname')]" );

		foreach ( $nodes as $node ) {
			$toc_list[] = $node->textContent;
		}
		if ( $toc_list ) {
			?>
			<div class='toc-wrapper-amp'>
				<ul>
				<li class="toc-title-amp">
						<?php esc_html_e( 'Table of Contents', 'idg-base-theme' ); ?>
					</li>
					<?php
					$counter = 0;
					foreach ( $toc_list as $toc_item ) {
						$counter++;
						$toc_classes = $counter <= 5 ? 'toc-item-amp is-open' : 'toc-item-amp';

						?>
						<li [class]="visible ? 'toc-item-amp is-open' : 'toc-item-amp'"
						class="<?php echo esc_attr( $toc_classes ); ?>">
						<a href="#<?php echo esc_attr( sanitize_title_with_dashes( $toc_item ) ); ?>">
						<?php echo esc_attr( $toc_item ); ?> </a></li>
						<?php
					}

					if ( count( $toc_list ) > 5 ) {
						?>

						<li [class]="visible ? 'toc-show-more-amp hidden' : 'toc-show-more-amp'"
						class='toc-show-more-amp' on="tap:AMP.setState({visible: !visible})">...</li>
						<?php
					}
					?>
				</ul>
			</div>
						<?php
		} else {
			return;
		}
	}

	add_filter( 'the_content', 'idg_base_theme_add_id_to_header' );
	/**
	 * Add id to the header.
	 *
	 * @param string $content the article.
	 */
	function idg_base_theme_add_id_to_header( $content ) {

		$pattern = '#(?P<full_tag><(?P<tag_name>h\d)(?P<tag_extra>[^>]*)>(?P<tag_contents>[^<]*)</h\d>)#i';
		if ( preg_match_all( $pattern, $content, $matches, PREG_SET_ORDER ) ) {
			foreach ( $matches as $match ) {
				$find[]    = $match['full_tag'];
				$tag_id    = sanitize_title( $match['tag_contents'] );
				$id_attr   = sprintf( ' id="%s"', $tag_id );
				$replace[] = sprintf( '<%1$s%2$s%3$s>%4$s</%1$s>', $match['tag_name'], $match['tag_extra'], $id_attr, $match['tag_contents'] );
			}
			$content = str_replace( $find, $replace, $content );
		}
		return $content;
	}
}
