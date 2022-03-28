<?php
/*
Plugin Name: OAuth Route Filter
Plugin URI: https://bigbite.net
Description: Adds a way to filter some requests from goin through the OAuth plugin
Author: Big Bite
Version: 1.0.0
Author URI: https://bigbite.net
*/
add_action( 'plugins_loaded', function() {
    remove_filter( 'determine_current_user', 'WP\OAuth2\Authentication\attempt_authentication', 11 );
    add_filter( 'determine_current_user', function( $user = null ) {
        if ( ! empty( $user ) ) {
            return $user;
		}

		$request_uri = filter_input( INPUT_SERVER, 'REQUEST_URI' );

		if ( ! isset( $request_uri ) ) {
			return $user;
		}

        $path = str_replace( "/wp-json/", '',  $request_uri );
		$excluded_routes = apply_filters( 'oauth_route_filter', [] );
        $match = 0;
        foreach ( $excluded_routes as $route ) {
            $match = preg_match( '@^' . $route . '$@i', $path, $matches );
            if ( ! $match ) {
                continue;
            }
		}

        if ( ! $match ) {
            return WP\OAuth2\Authentication\attempt_authentication();
		}

        return $user;
    } );
} );
