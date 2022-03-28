<?php

use IDG\Sponsored_Links\Sponsored_Link_Post_Type;

if ( ! function_exists( 'idg_base_theme_show_sponsored_links' ) ) {
	/**
	 * Prints the sponsored links section.
	 *
	 * @return void
	 */
	function idg_base_theme_show_sponsored_links() {

		$links = idg_base_theme_get_active_links();

		if ( empty( $links ) ) {
			return;
		}

		$links = array_slice( $links, 0, 10 );

		?>
		<div class="sponsored-links">
			<p class="sponsored-links__title" ><?php esc_html_e( 'Sponsored Links', 'idg-sponsored-links' ); ?></p>
			<ul class="sponsored-links__list">
			<?php foreach ( $links as $link ) : ?>
				<li>
					<?php echo wp_kses_post( $link['campaign_details']['impression_pixel_tag'] ?? '' ); ?>
					<a class="sponsored-links__link" href="<?php echo esc_url( $link['campaign_details']['click_url'] ); ?>"><?php echo esc_html( $link['campaign_details']['link_text'] ); ?></a>
				</li>
			<?php endforeach; ?>
			</ul>
		</div>

		<?php
	}
}

if ( ! function_exists( 'idg_base_theme_get_active_links' ) ) {
	/**
	 * Gets the list of active sponsor links.
	 *
	 * @return array
	 */
	function idg_base_theme_get_active_links() {

		$links = idg_base_theme_get_sponsored_link_posts();

		if ( empty( $links ) ) {
			return [];
		}

		$active_links = [];

		foreach ( $links as $link ) {
			$meta = get_post_meta( $link, 'sponsored_link_campaign', true );

			if ( empty( $meta ) ) {
				continue;
			}

			$meta = json_decode( $meta, true );

			if ( idg_base_theme_is_sponsored_link_active( $meta ) ) {
				$active_links[] = $meta;
			}
		}

		return $active_links;
	}
}

if ( ! function_exists( 'idg_base_theme_is_sponsored_link_active' ) ) {
	/**
	 * Checks whether the link is active or not.
	 *
	 * @param array $meta An array of Sponsor links post meta.
	 *
	 * @return boolean
	 */
	function idg_base_theme_is_sponsored_link_active( $meta ) {

		if ( empty( $meta['campaign_details']['enable'] ) || true !== $meta['campaign_details']['enable'] ) {
			return false;
		}

		$current_date = gmdate( 'Y-m-d' );
		$start_date   = ! empty( $meta['campaign_duration_details']['start_date'] ) ? gmdate( 'Y-m-d', strtotime( $meta['campaign_duration_details']['start_date'] ) ) : false;
		$end_date     = ! empty( $meta['campaign_duration_details']['end_date'] ) ? gmdate( 'Y-m-d', strtotime( $meta['campaign_duration_details']['end_date'] ) ) : false;

		if ( false === $start_date && false === $end_date ) {
			return true;
		}

		// Start date is present and end date is not then validate using current date.
		if ( false !== $start_date && false === $end_date ) {
			// Current date should be greater then start date.
			if ( $current_date > $start_date ) {
				return true;
			} else {
				return false;
			}
		}

		// End date is present and start date is not then validate using current date.
		if ( false !== $end_date && false === $start_date ) {
			// End date should be greater then current date.
			if ( $current_date < $end_date ) {
				return true;
			} else {
				return false;
			}
		}

		// Return true if current date is between range of start date and end date.
		return $current_date >= $start_date && $current_date <= $end_date;
	}
}

if ( ! function_exists( 'idg_base_theme_get_sponsored_link_posts' ) ) {
	/**
	 * Gets the list of sponsored link post IDs.
	 *
	 * @return array An array of sponsored link post IDs.
	 */
	function idg_base_theme_get_sponsored_link_posts() {

		$args = [
			'post_type'           => Sponsored_Link_Post_Type::POST_TYPE_SLUG,
			'fields'              => 'ids',
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		];

		$query = new \WP_Query( $args );

		return $query->posts;
	}
}
