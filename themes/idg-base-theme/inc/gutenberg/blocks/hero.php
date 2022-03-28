<?php
if ( ! function_exists( 'idg_hero_process_query' ) ) {
	/**
	 * Processes each post/page to return the the correct data format for our render function.
	 *
	 * @param WP_Query $query Current WP_Query.
	 * @param array    $attributes Current Block attributes.
	 * @return array|bool
	 */
	function idg_hero_process_query( $query, $attributes ) {
		$posts = false;

		if ( $query->have_posts() ) {
			$posts = [];

			while ( $query->have_posts() ) {
				$query->the_post();

				$eyebrow_info  = idg_base_theme_get_eyebrow( $query->post->ID );
				$eyebrow       = $eyebrow_info['eyebrow'];
				$eyebrow_style = $eyebrow_info['eyebrow_style'];

				$author_id = get_the_author_meta( 'ID' );
				$author    = idg_base_theme_get_author_name( intval( $author_id ), intval( $query->post->ID ) );

				// Overide for `byline` meta on legacy data.
				$legacy_byline = get_post_meta( $query->post->ID, 'byline', true );
				if ( ! empty( $legacy_byline ) && is_string( $legacy_byline ) ) {
					$author = $legacy_byline;
				}

				$multi_title = get_post_meta( $query->post->ID, 'multi_title', true );
				if ( $multi_title ) {
					$multi_title = json_decode( $multi_title );
					$short_title = $multi_title->titles->headline->additional->short_title;
				}

				$posts[] = [
					'title'           => $short_title ?: $query->post->post_title,
					'link'            => get_permalink( $query->post->ID ),
					'eyebrow'         => $eyebrow ? $eyebrow : false,
					'eyebrow_style'   => $eyebrow_style ? $eyebrow_style : false,
					'featured_image'  => get_post_thumbnail_id(),
					'excerpt'         => $query->post->post_excerpt,
					'date'            => $query->post->post_date,
					'author'          => $author,
					'displayEyebrows' => isset( $attributes['displayEyebrows'] ) ? false : true,
					'displayBylines'  => isset( $attributes['displayBylines'] ) ? false : true,
				];
			}

			wp_reset_postdata();
		}

		return $posts;
	}
}

if ( ! function_exists( 'idg_hero_process_category' ) ) {
	/**
	 * Process the attributes for the current block for the category type.
	 *
	 * @param array $attributes Current Block attributes.
	 * @return array|bool
	 */
	function idg_hero_process_category( $attributes ) {
		$args  = idg_block_feed_args( $attributes );
		$query = idg_wp_query_cache( $args, 'idg_hero_feed' );

		return idg_hero_process_query( $query, $attributes );
	}
}

if ( ! function_exists( 'idg_hero_process_select' ) ) {
	/**
	 * Process the attributes for the current block for the object selection type.
	 *
	 * @param array $attributes Current Block attributes.
	 * @return array|bool
	 */
	function idg_hero_process_select( $attributes ) {
		if ( empty( $attributes ) || ! isset( $attributes['selectedPosts'] ) || ! $attributes['selectedPosts'] ) {
			return false;
		}

		$post_types = get_post_types(
			[
				'public' => true,
			]
		);

		$query = new WP_Query(
			[
				'post__in'      => $attributes['selectedPosts'],
				'post_type'     => $post_types,
				'no_found_rows' => true,
				'orderby'       => 'post__in',
				'post_status'   => [ 'publish', 'updated' ],
			]
		);

		return idg_hero_process_query( $query, null, $attributes );
	}
}

if ( ! function_exists( 'idg_hero_process_content' ) ) {
	/**
	 * Process the current attributes by calling the specific function dependant on block type.
	 *
	 * @param array $attributes Current block attributes.
	 * @return array|bool
	 */
	function idg_hero_process_content( $attributes ) {
		if ( empty( $attributes['type'] ) ) {
			return idg_hero_process_category( $attributes );
		}

		switch ( $attributes['type'] ) {
			case 'category':
				return idg_hero_process_category( $attributes );
			case 'select':
				return idg_hero_process_select( $attributes );
			default:
				return idg_hero_process_category( $attributes );
		}
	}
}

if ( ! function_exists( 'idg_render_hero_item' ) ) {
	/**
	 * Render the current item.
	 *
	 * @param array $data Item data.
	 * @param int   $index Item index.
	 * @param int   $total Total amount of items.
	 * @return void
	 */
	function idg_render_hero_item( $data, $index, $total ) {
		spaceless();
		$title = isset( $data['title'] ) ? $data['title'] : '';

		if ( $data['featured_image'] ) {
			$small_featured_image        = wp_get_attachment_image_url( $data['featured_image'], '300-r3:2' );
			$small_featured_image_srcset = wp_get_attachment_image_srcset( $data['featured_image'], '300-r3:2' );
			$large_featured_image        = wp_get_attachment_image_url( $data['featured_image'], '1240-r3:2' );
		}
		?>

		<?php if ( ( 4 === $total || 3 === $total ) && 1 === $index ) : ?>
			<div class="hero-col hero-col-1">
		<?php elseif ( 4 === $total && 2 === $index ) : ?>
			<div class="hero-col hero-col-3">
		<?php elseif ( 3 === $total && 2 === $index ) : ?>
			<div class="hero-col hero-col-2">
		<?php endif; ?>

		<article class="item" role="article" aria-label="Article: <?php echo esc_attr( format_for_aria_label( $title ) ); ?>">
		<?php if ( ! empty( $data['link'] ) ) : ?>
			<a class="floating-anchor" href="<?php echo esc_url( $data['link'] ); ?>">
		<?php endif; ?>

			<?php if ( 4 === $total && 1 < $index ) : ?>

			<div class="item-inner">
				<div class="item-image">
					<?php
					if ( ! empty( $small_featured_image ) ) {
						printf( '<img src="%s" srcset="%s"/>', esc_url( $small_featured_image ), esc_html( $small_featured_image_srcset ) );
					}
					?>
				</div>
				<div class="item-text">
					<div class="item-text-inner">

					<?php
					if ( $data['displayEyebrows'] && ! empty( $data['eyebrow'] ) ) {
						printf( '<span class="item-eyebrow item-eyebrow--%s">%s</span>', esc_attr( $data['eyebrow_style'] ), esc_html( $data['eyebrow'] ) );
					}
					?>

					<?php if ( $title ) : ?>
						<h3>
						<?php
							printf( '%s', esc_html( $title ) );
						?>
						</h3>
					<?php endif; ?>
					</div>
				</div>
			</div>

			<?php else : ?>

			<div class="item-inner" style="background-image: url(<?php echo esc_url( $large_featured_image ); ?>)">
				<div class="item-text">
					<div class="item-text-inner">
					<?php
					if ( $data['displayEyebrows'] && ! empty( $data['eyebrow'] ) ) {
						printf( '<span class="item-eyebrow item-eyebrow--%s">%s</span>', esc_attr( $data['eyebrow_style'] ), esc_html( $data['eyebrow'] ) );
					}
					?>

					<?php if ( $title ) : ?>
						<h3>
						<?php
							printf( '%s', esc_html( $title ) );
						?>
						</h3>
					<?php endif; ?>

					<?php if ( $data['displayBylines'] && ! empty( $data['author'] ) ) : ?>
						<span class="item-byline">
							<?php echo 'By ' . esc_html( $data['author'] ); ?>
						</span>
					<?php endif; ?>
					</div>
				</div>
			</div>

			<?php endif; ?>

		<?php if ( ! empty( $data['link'] ) ) : ?>
			</a>
		<?php endif; ?>
		</article>

		<?php if ( ( 4 === $total || 3 === $total ) && ( 1 === $index ) ) : ?>
			</div>
		<?php elseif ( $total > 2 && $total === $index ) : ?>
			</div>
		<?php endif; ?>
		<?php
		endspaceless();
	}
}

if ( ! function_exists( 'idg_render_hero' ) ) {
	/**
	 * Render the list item block.
	 *
	 * @param array $attributes Current block attributes.
	 * @return string
	 */
	function idg_render_hero( $attributes ) {
		// Prevent a bug in the admin panel where the editor
		// shows a different post if the list item is selected
		// using one of the selection methods.
		if ( is_admin() ) {
			return '';
		}

		if ( doing_filter( 'get_the_excerpt' ) ) {
			return false;
		}

		$data = idg_hero_process_content( $attributes );

		if ( ! $data ) {
			return '';
		}

		$total = count( $data );

		ob_start();
		printf( '<div class="hero"><div class="hero-inner hero-%s">', esc_attr( $total ) );
		foreach ( $data as $index => $item ) {
			// Increment index by 1 as $total starts from 1, not 0.
			idg_render_hero_item( $item, $index + 1, $total );
		}
		printf( '</div>' );

		printf( '</div>' );

		return ob_get_clean();
	}
}
