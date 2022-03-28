<?php

namespace IDG\Publishing_Flow\API;

use IDG\Publishing_Flow\API\Request;
use IDG\Publishing_Flow\API\Endpoints\Author;
use IDG\Publishing_Flow\API\Endpoints\Post;
use IDG\Publishing_Flow\API\Endpoints\Taxonomy;

/**
 * Manages the routes required for Publication Flow.
 */
class Routes {
	const HOOK_ROUTE_CLASSES = 'idg_publication_flow_route_classes';
	/**
	 * Registers any routes that are provided.
	 *
	 * @return void
	 */
	public function register_routes() {
		$defaults       = [
			Author::class,
			Post::class,
			Taxonomy::class,
		];
		$routes_classes = apply_filters( self::HOOK_ROUTE_CLASSES, $defaults );

		foreach ( $routes_classes as $route_class ) {
			$this->register_route_class( $route_class );
		}
	}

	/**
	 * Register the route from it's class.
	 *
	 * @param mixed $route_class The route class to register.
	 * @throws \ErrorException Throws when route class is not extended from Request.
	 * @return void
	 */
	public function register_route_class( $route_class ) {
		$request_class = Request::class;
		if ( ! is_subclass_of( $route_class, $request_class ) ) {
			throw new \ErrorException( "Route class $route_class must extend $request_class." );
		}

		$routes = $route_class::REST_ROUTES;

		foreach ( $routes as $action => $route ) {
			register_rest_route(
				'idg/v1',
				$route,
				[
					'methods'             => 'POST',
					'callback'            => [ ( new $route_class() ), $action ],
					'permission_callback' => [ $this, 'authenticate' ],
				]
			);
		}
	}

	/**
	 * Checks the authentication headers for oAuth2 tokens.
	 *
	 * @return boolean
	 */
	public function authenticate() : bool {
		if ( class_exists( '\Automattic\VIP\Search\Search', false ) ) {
			$search_cache_instance = \Automattic\VIP\Search\Search::instance()->cache;
			remove_action( 'pre_get_posts', [ $search_cache_instance, 'disable_apc_for_ep_enabled_requests' ], 0 );
		}

		$token = \WP\OAuth2\Authentication\attempt_authentication();

		if ( $token ) {
			return true;
		}

		return false;
	}
}
