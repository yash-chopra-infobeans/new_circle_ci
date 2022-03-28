<?php
	/**
	 * Contains function which are helpful to display Article feeds
	 *
	 * @package Idg-base-theme
	 */

if ( ! function_exists( 'idg_article_feed_process_query' ) ) {
	/**
	 * Processes each post/page to return the the correct data format for our render function.
	 *
	 * @param WP_Query $query Current WP_Query.
	 * @param array    $attributes Current Block attributes.
	 * @return array|bool
	 */
	function idg_article_feed_process_query( $query, $attributes ) {
		$posts   = false;
		$excerpt = false;

		if ( $query->have_posts() ) {
			$posts = [];

			while ( $query->have_posts() ) {
				$query->the_post();

				$eyebrow_info        = idg_base_theme_get_eyebrow( $query->post->ID );
				$eyebrow             = $eyebrow_info['eyebrow'];
				$eyebrow_style       = $eyebrow_info['eyebrow_style'];
				$eyebrow_sponsorship = $eyebrow_info['eyebrow_sponsorship'];
				$eyebrow_feed_title  = $eyebrow_info['eyebrow_feed_title'];
				$eyebrow_feed_style  = $eyebrow_info['eyebrow_feed_style'];

				$author_id = get_the_author_meta( 'ID' );
				$author    = idg_base_theme_get_author_name( intval( $author_id ), intval( $query->post->ID ) );

				// Overide for `byline` meta on legacy data.
				$legacy_byline = get_post_meta( $query->post->ID, 'byline', true );
				if ( ! empty( $legacy_byline ) && is_string( $legacy_byline ) ) {
					$author = $legacy_byline;
				}

				$timezone = wp_timezone();
				$datetime = new \DateTime();
				$datetime->setTimeZone( $timezone );
				$wp_timestamp_now = strtotime( $datetime->format( 'Y-m-d H:i:s' ) );

				$posts[] = [
					'title'               => $query->post->post_title,
					'link'                => get_permalink( $query->post->ID ),
					'eyebrow'             => $eyebrow ? $eyebrow : false,
					'eyebrow_style'       => $eyebrow_style ? $eyebrow_style : false,
					'eyebrow_sponsorship' => $eyebrow_sponsorship ? $eyebrow_sponsorship : false,
					'eyebrow_feed_title'  => $eyebrow_feed_title ? $eyebrow_feed_title : false,
					'eyebrow_feed_style'  => $eyebrow_feed_style ? $eyebrow_feed_style : false,
					'featured_image'      => get_post_thumbnail_id(),
					'excerpt'             => idg_base_theme_get_the_excerpt(),
					'date'                => get_the_date( 'j M Y' ),
					'time'                => human_time_diff( get_post_time(), $wp_timestamp_now ),
					'score'               => idg_base_theme_review_score(),
					'author'              => $author,
					'displayEyebrows'     => isset( $attributes['displayEyebrows'] ) ? false : true,
					'displayExcerpt'      => isset( $attributes['displayExcerpt'] ) ? false : true,
					'displayBylines'      => isset( $attributes['displayBylines'] ) ? false : true,
					'displayDate'         => isset( $attributes['displayDate'] ) ? false : true,
					'displayScore'        => isset( $attributes['displayScore'] ) ? false : true,
					'excludeSponsored'    => isset( $attributes['excludeSponsored'] ) ? false : true,
					'last_post'           => $query->current_post + 1 == $query->post_count ? true : false,
					'max_pages'           => $query->max_num_pages,
					'video_class'         => has_term( 'video', 'article_type' ) ? ' item-image--video' : '',
				];
			}

			wp_reset_postdata();
		}

		return $posts;
	}
}

if ( ! function_exists( 'idg_article_feed_process_category' ) ) {
	/**
	 * Process the attributes for the current block for the category type.
	 *
	 * @param array $attributes Current Block attributes.
	 * @return array|bool
	 */
	function idg_article_feed_process_category( $attributes ) {
		$args  = idg_block_feed_args( $attributes );
		$query = idg_wp_query_cache( $args, 'idg_article_feed' );

		return idg_article_feed_process_query( $query, $attributes );
	}
}

if ( ! function_exists( 'idg_article_feed_process_select' ) ) {
	/**
	 * Process the attributes for the current block for the object selection type.
	 *
	 * @param array $attributes Current Block attributes.
	 * @return array|bool
	 */
	function idg_article_feed_process_select( $attributes ) {
		if ( empty( $attributes ) || ! isset( $attributes['selectedPosts'] ) || ! $attributes['selectedPosts'] ) {
			return false;
		}

		$post_types = get_post_types(
			[
				'public' => true,
			]
		);

		$args = [
			'post__in'      => $attributes['selectedPosts'],
			'post_type'     => $post_types,
			'no_found_rows' => ! isset( $attributes['ajaxLoad'] ),
			'orderby'       => 'post__in',
			'post_status'   => [ 'publish', 'updated' ],
		];

		$query = idg_wp_query_cache( $args, 'idg_article_feed' );

		return idg_article_feed_process_query( $query, null, $attributes );
	}
}

if ( ! function_exists( 'idg_article_feed_process_content' ) ) {
	/**
	 * Process the current attributes by calling the specific function dependant on block type.
	 *
	 * @param array $attributes Current block attributes.
	 * @return array|bool
	 */
	function idg_article_feed_process_content( $attributes ) {
		if ( empty( $attributes['type'] ) ) {
			return idg_article_feed_process_category( $attributes );
		}

		switch ( $attributes['type'] ) {
			case 'category':
				return idg_article_feed_process_category( $attributes );
			case 'select':
				return idg_article_feed_process_select( $attributes );
			default:
				return idg_article_feed_process_category( $attributes );
		}
	}
}

if ( ! function_exists( 'idg_render_article_feed_item' ) ) {
	/**
	 * Render the current item.
	 *
	 * @param array $data Item data.
	 * @param int   $index Item index.
	 * @param int   $total Total amount of items.
	 * @param array $attributes Attributes of the article-feed block.
	 * @return void
	 */
	function idg_render_article_feed_item( $data, $index, $total, $attributes ) { // phpcs:ignore
		spaceless();
		$title      = isset( $data['title'] ) ? $data['title'] : '';
		$post_date  = $data['date'];
		$post_link  = $data['link'];
		$post_time  = $data['time'];
		$post_score = $data['score'];

		if ( $data['featured_image'] ) {
			$featured_image        = wp_get_attachment_image_url( $data['featured_image'], '300-r3:2' );
			$featured_image_srcset = wp_get_attachment_image_srcset( $data['featured_image'], '300-r3:2' );
		}
		?>

		<article class="item" role="article" aria-label="Article: <?php echo esc_attr( format_for_aria_label( $title ) ); ?>" tabindex="0">
			<div class="item-inner">
				<?php if ( $featured_image ) : ?>
					<div class="item-image<?php echo esc_attr( $data['video_class'] ); ?>">
					<?php
					if ( $post_link ) {
						printf(
							'<a href="%s" target="_blank" rel="noopener noreferrer"><img src="%s" alt="%s" srcset="%s" /></a>',
							esc_url( $post_link ),
							esc_url( $featured_image ),
							esc_attr( $title ),
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
					<?php
					if ( $data['displayEyebrows'] && ! empty( $data['eyebrow_feed_title'] ) ) {
						if ( 'BrandPost' === $data['eyebrow_feed_title'] ) {
							printf( '<span class="item-eyebrow item-eyebrow--%s">%s</span>', esc_attr( $data['eyebrow_feed_style'] ), esc_html( $data['eyebrow_feed_title'] . ' ' ) );
							if ( $data['eyebrow_sponsorship'] ) {
								printf( '<span class="item-eyebrow-sponsored-by-text">%s</span>', 'Sponsored by ' . esc_html( $data['eyebrow_sponsorship'] ) );
							}
						} elseif ( 'Sponsor Podcast' === $data['eyebrow_feed_title'] ) {
							printf( '<span class="item-eyebrow item-eyebrow--%s">%s</span>', esc_attr( $data['eyebrow_feed_style'] ), esc_html( $data['eyebrow_feed_title'] . ' ' ) );
							if ( $data['eyebrow_sponsorship'] ) {
								printf( '<span class="item-eyebrow-sponsored-by-text">%s</span>', 'in partnership with ' . esc_html( $data['eyebrow_sponsorship'] ) );
							}
						} else {
							printf( '<span class="item-eyebrow item-eyebrow--%s">%s</span>', esc_attr( $data['eyebrow_feed_style'] ), esc_html( $data['eyebrow_feed_title'] . ' ' ) );
						}
					}
					?>

					<?php if ( $title ) : ?>
						<h3>
						<?php
							printf( '<a href="%s">%s</a>', esc_url( $data['link'] ), esc_html( $title ) );
						?>
						</h3>
					<?php endif; ?>

					<?php if ( $data['displayExcerpt'] && ! empty( $data['excerpt'] ) ) : ?>
						<span class="item-excerpt">
							<?php
								printf( '%s', wp_kses_post( $data['excerpt'] ) );
							?>
						</span>
					<?php endif; ?>

					<div class="item-meta">
						<?php if ( $data['displayBylines'] && ! empty( $data['author'] ) && 'brandpost' !== strtolower( $data['eyebrow_feed_title'] ) ) : ?>
							<span class="item-byline"><?php printf( 'By %s', esc_html( $data['author'] ) ); ?></span>
						<?php endif; ?>
						<?php
						if ( $data['displayDate'] && ! empty( $post_time ) ) :
							?>
						<span class="item-date">
							<?php
							printf( '%s ago', esc_html( $post_time ) );
							?>
						</span>
						<?php endif; ?>
						<?php
						if ( $data['displayScore'] && ! empty( $post_score ) ) :
							?>
						<span class="item-score">
							<?php
							printf(
								'<div class="starRating" style="--rating: %s;" aria-label="%s"></div>',
								esc_attr( $post_score ),
								esc_attr( __( 'Rating of this product is' ) . $post_score . __( 'out of 5.' ) )
							);
							?>
						</span>
						<?php endif; ?>
					</div>

					</div>
				</div>
			</div>
		</article>

		<?php
		do_action( 'idg_render_article_feed_item', $index, 0 );

		if ( $attributes['displayButton'] ) {

			$button_text = $attributes['buttonText'] ?: esc_html__( 'More stories', 'idg-base-theme' );
			$button_link = $attributes['buttonLink'] ?: '#';

			if ( $attributes['ajaxLoad'] && $data['last_post'] ) {
				if ( $data['max_pages'] > 1 ) {
					$data_filters = isset( $attributes['filters'] ) ? str_replace( '"', "'", $attributes['filters'] ) : '';
					$data_perpage = isset( $attributes['amount'] ) ? $attributes['amount'] : 1;
					$data_offset  = isset( $attributes['offset'] ) ? $attributes['offset'] : 0;
					$data_exclude = $attributes['displayButton'] ? 1 : 0;

					printf(
						'<div class="articleFeed-button">
							<a href="#" aria-label="View More" role="button" class="btn ajax-load" data-filters="%s" data-perpage="%s" data-offset="%s" data-exclude="%s">
							%s
							</a>
						</div>',
						esc_attr( $data_filters ),
						esc_attr( $data_perpage ),
						esc_attr( $data_offset ),
						esc_attr( $data_exclude ),
						esc_html( $button_text )
					);
				}
			} elseif ( $data['last_post'] ) {
				printf(
					'<div class="articleFeed-button">
						<a href="%s" aria-label="View More" role="button" class="btn">
						%s
						</a>
					</div>',
					esc_url( $button_link ),
					esc_html( $button_text )
				);
			}
		}

		endspaceless();
	}
}

if ( ! function_exists( 'idg_render_article_feed' ) ) {
	/**
	 * Render the article_feed item block.
	 *
	 * @param array $attributes Current block attributes.
	 * @return string
	 */
	function idg_render_article_feed( $attributes ) {
		// Prevent a bug in the admin panel where the editor
		// shows a different post if the article_feed item is selected
		// using one of the selection methods.
		if ( is_admin() ) {
			return '';
		}

		if ( doing_filter( 'get_the_excerpt' ) ) {
			return false;
		}

		$data = idg_article_feed_process_content( $attributes );

		if ( ! $data ) {
			return '';
		}

		if ( ! isset( $attributes['style'] ) ) {
			$style = 'list';
		} else {
			$style = $attributes['style'];
		}

		ob_start();

		printf( '<div class="articleFeed articleFeed--%s">', esc_attr( $style ) );
		$index = 0;
		$total = count( $data );
		printf( '<div class="articleFeed-inner articleFeed-%s">', esc_attr( $total ) );
		foreach ( $data as $item ) {
			idg_render_article_feed_item( $item, $index, $total, $attributes );
			$index++;
		}
		print '</div>';
		print '</div>';

		return ob_get_clean();
	}
}
