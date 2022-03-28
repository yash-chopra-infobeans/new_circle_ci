<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName

use function \IDG\Base_Theme\Utils\is_amp;

/**
 * Class responsible for building the custom menu walker.
 */
class Menu_With_Chevrons extends Walker_Nav_Menu {
	/**
	 * Class responsible for building the custom menu walker.
	 *
	 * @param string $output The menu item's starting HTML output.
	 * @param object $item Menu item data object.
	 * @param int    $depth Depth of menu item. Used for padding.
	 * @param array  $args An object of wp_nav_menu() arguments.
	 * @param int    $id The id of the item.
	 * @SuppressWarnings(PHPMD)
	 */
	public function start_el( &$output, $item, $depth = 0, $args = [], $id = 0 ) { // phpcs:ignore

		$menu_item_key = "menu-item-{$item->ID}-{$args->menu_id}";

		if ( in_array( 'menu-item-has-children', $item->classes, true ) && 0 === $depth ) {
				$svg_icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M151.5 347.8L3.5 201c-4.7-4.7-4.7-12.3 0-17l19.8-19.8c4.7-4.7 12.3-4.7 17 0L160 282.7l119.7-118.5c4.7-4.7 12.3-4.7 17 0l19.8 19.8c4.7 4.7 4.7 12.3 0 17l-148 146.8c-4.7 4.7-12.3 4.7-17 0z"/></svg>';
			if ( is_amp() ) {
				$arrow = sprintf(
					'<button class="sub-menu-open-button" on="tap:%s.toggleClass(class=\'subMenu--is-open\')">%s</button>',
					$menu_item_key,
					$svg_icon
				);
			} else {
				$arrow = sprintf( '<button class="sub-menu-open-button" aria-label="open-close">%s</button>', $svg_icon );
			}
		} else {
			$arrow = '';
		}

		$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

		$class_names = '';
		$value       = '';

		$classes = empty( $item->classes ) ? [] : (array) $item->classes;

		$class_names = implode( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item ) );
		$class_names = ' class="' . esc_attr( $class_names ) . ' inactive"';

		$output .= $indent . '<li id="' . $menu_item_key . '"' . $value . $class_names . '>';

		$attributes  = ! empty( $item->attr_title ) ? ' title="' . esc_attr( $item->attr_title ) . '"' : '';
		$attributes .= ! empty( $item->target ) ? ' target="' . esc_attr( $item->target ) . '"' : '';
		$attributes .= ! empty( $item->xfn ) ? ' rel="' . esc_attr( $item->xfn ) . '"' : '';
		$attributes .= ! empty( $item->url ) ? ' href="' . esc_url( $item->url ) . '"' : '';

		$item_output  = $args->before;
		$item_output .= '<a' . $attributes . '>';
		$item_output .= $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;
		$item_output .= '</a>';
		$item_output .= $arrow;
		$item_output .= $args->after;

		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	}
}
