<?php

if ( ! function_exists( 'idg_tab_navigation_parse_url' ) ) {
	/**
	 * Parse the URL for tab navigation
	 *
	 * @param string $url Url to be parsed.
	 * @return string
	 */
	function idg_tab_navigation_parse_url( $url ) {
		$url = trim( $url );

		if ( '#' === substr( $url, 0, 1 ) ) {
			return $url;
		}

		if ( ! preg_match( '(http[s]?://)', $url ) ) {
			return "http://$url";
		}

		return $url;
	}
}

if ( ! function_exists( 'idg_render_tab_navigation' ) ) {
	/**
	 * Render the tab navigation
	 *
	 * @param array $attributes Current block attributes.
	 * @return string
	 */
	function idg_render_tab_navigation( $attributes ) {
		if ( ! isset( $attributes['items'] ) || empty( $attributes['items'] ) ) {
			return '';
		}

		$items = $attributes['items'];

		ob_start();
		printf( '<div class="tab-navigation">' );
		printf( '<ul class="tab-items">' );

		foreach ( $items as $item ) {
			$button_string = $item['makeButton'] ? 'tab-item-btn' : '';
			$target_string = $item['opensInNewTab'] ? '_blank' : '_self';

			$title             = trim( $item['title'] );
			$url               = idg_tab_navigation_parse_url( $item['url'] );
			$domain            = wp_parse_url( get_site_url(), PHP_URL_HOST );
			$external_link_svg = '';

			// check to see if the URL matches the domain - if not add the external icon.
			if ( strpos( $url, $domain ) === false ) {
				$external_link_svg = get_idg_asset( '/icons/external-link.svg' );
			}


			if (
				( ! empty( $title ) || ! empty( $url ) )
				&& '#' !== $url
			) {
				printf(
					'<li class="tab-item %s "><a href="%s" target="%s">%s %s</a></li>',
					esc_attr( $button_string ),
					esc_attr( $url ),
					esc_attr( $target_string ),
					esc_html( $title ),
					wp_kses_post( $external_link_svg )
				);
			}
		}

		printf( '</ul>' );
		printf( '<div class="tab-group"><span>More <button class="tab-more-open-button"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M151.5 347.8L3.5 201c-4.7-4.7-4.7-12.3 0-17l19.8-19.8c4.7-4.7 12.3-4.7 17 0L160 282.7l119.7-118.5c4.7-4.7 12.3-4.7 17 0l19.8 19.8c4.7 4.7 4.7 12.3 0 17l-148 146.8c-4.7 4.7-12.3 4.7-17 0z"/></svg></button></span><div class="tab-group-items-wrapper"><ul class="tab-group-items"></ul></div></div>' );
		printf( '</div>' );
		return ob_get_clean();
	}
}

add_filter(
	'wp_kses_allowed_html',
	function ( $allowed = [] ) {
		// phpcs:ignore
		$svg_tags     = [ 'svg', 'g', 'circle', 'ellipse', 'line', 'path', 'polygon', 'polyline', 'rect', 'text', 'textPath' ];
			$svg_atts = [
				'xmlns'             => true,
				'id'                => true,
				'class'             => true,
				'width'             => true,
				'height'            => true,
				'fill'              => true,
				'transform'         => true,
				'opacity'           => true,
				'data-name'         => true,
				'stroke'            => true,
				'stroke-miterlimit' => true,
				'stroke-width'      => true,
				'style'             => true,
				'points'            => true,
				'viewbox'           => true,
				'd'                 => true,
				'x'                 => true,
				'y'                 => true,
				'rx '               => true,
				'ry'                => true,
				'cx '               => true,
				'cy'                => true,
				'r'                 => true,
				'fill-rule'         => true,
				'clip-rule'         => true,
			];
			foreach ( $svg_tags as $svg_tag ) {
				$allowed[ $svg_tag ] = $svg_atts;
			}
				return $allowed;
	},
	10,
	2
);
