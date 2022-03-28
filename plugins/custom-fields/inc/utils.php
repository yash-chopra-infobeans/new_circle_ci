<?php

if ( ! function_exists( 'array_dot_set' ) ) {
    /**
     * Set an item in an array using "dot" notation.
     *
     * @param  array $array
     * @param  string $key
     * @param  mixed $default
     * @return mixed
     */
    function array_dot_set( &$array, $keys, $value = null ) {
        if ( is_null( $keys ) ) {
            return;
		}

        if ( isset( $array[$keys] ) ) {
			$array[$keys] = $value;
        }

		foreach ( explode( '.', $keys ) as $key ) {
			$array = &$array[ $key ];
		}

		$array = $value;
    }
}

if ( ! function_exists( 'array_dot_get' ) ) {
    /**
     * Get an item from an array using "dot" notation.
     *
     * @param  array $array
     * @param  string $key
     * @param  mixed $default
     * @return mixed
     */
    function array_dot_get( $array, $key, $default = null ) {
        if ( is_null( $key ) ) {
            return $array;
        }

        if ( isset( $array[$key] ) ) {
            return $array[$key];
        }

        foreach ( explode( '.', $key ) as $segment ) {
            if ( ! is_array( $array ) || ! array_key_exists( $segment, $array)  ) {
                return $default;
            }

            $array = $array[$segment];
        }

        return $array;
    }
}

if ( ! function_exists( 'is_autosave' ) ) {
    /**
	 * Determine if an autosave is occuring.
	 *
	 * @return boolean
	 */
	function is_autosave() {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return true;
		}

		return false;
	}
}
