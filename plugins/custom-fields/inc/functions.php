<?php

use Custom_Fields\Fields\Post_Type;
use Custom_Fields\Fields\Options_Page;

if ( ! function_exists( 'cf_register_post_type' ) ) {
    /**
     * Register custom fields to a post type.
     *
	 * @param object $config - The config object
 	 * @param string $post_type - The post type to attach fields to
	 * @param boolean $lock_template - Should other blocks be allowed?
	 *
	 * @return Custom_Fields\Fields\Post_Type
     */
    function cf_register_post_type( object $config, string $post_type = 'post', $lock_template = false ) : Post_Type {
		if ( ! post_type_exists( $post_type ) ) {
			throw new Exception( "Post type doesn't exist." );
		}

		return new Post_Type( $config, $post_type, $lock_template );
    }
}

if ( ! function_exists( 'cf_register_options_page' ) ) {
    /**
     * Register custom fields to a options page.
     *
	 * @param string $config - The post type to attach fields to
	 * @param string $key - The post type to attach fields to
	 * @param string $title - The id of the options page.
	 *
	 * @return Custom_Fields\Fields\Options_Page
     */
    function cf_register_options_page( object $config, string $key, string $title ) : Options_Page {
		return new Options_Page( $config, $key, $title );
    }
}

if ( ! function_exists( 'cf_get_value' ) ) {
    /**
	 * Retrive a value from a registered custom field.
	 *
	 * @param mixed $id - The id of the entity.
	 * @param string $prop - The entity prop.
	 * @param string $key - Key value in dot notation - can be blank.
	 * @param string $kind - The entity kind.
	 * @return mixed
	 */
    function cf_get_value( $id, string $prop = null, string $key = '', string $kind = '' ) {
		$data = "";

		switch ( $kind )  {
			case 'postType':
				$data = get_post_meta( $id, $prop, true );
				break;
			default:
				$options = get_option( $id ) ;

				if ( $prop && isset( $options[$prop] ) ) {
					$data = $options[$prop];
				} else {
					$data = $options;
				}
		}

		if ( is_array( $data ) ) {
			$data = array_map( function( $item ) {
				return json_decode( $item, true );
			}, $data );
		} else {
			$data = json_decode( $data, true );
		}

		if ( isset( $key ) && $key ) {
			return array_dot_get( $data, $key );
		}

		return $data;
    }
}
