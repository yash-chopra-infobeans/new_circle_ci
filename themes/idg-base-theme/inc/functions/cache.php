<?php

if ( ! function_exists( 'idg_cache_get' ) ) {
	/**
	 * Get items from the cache. If item does not exist
	 * in the registry it will be added.
	 *
	 * @param mixed  $hash The value to use in the hash uid.
	 * @param string $name The name of the cache to retrieve.
	 * @param string $group The cache group name.
	 * @return mixed
	 */
	function idg_cache_get( $hash, string $name, string $group = 'idg-base-theme' ) {
		$hash  = md5( json_encode( $hash ) );
		$cache = wpcom_vip_cache_get( sprintf( '%s_%s', $name, $hash ), $group );

		if ( ! $cache ) {
			return $cache;
		}

		if ( ! idg_cache_registry_exists( $name, $hash ) ) {
			idg_update_cache_registry( $name, $hash );
		}

		return $cache;
	}
}

if ( ! function_exists( 'idg_cache_set' ) ) {
	/**
	 * Sets the cache utilising the standard VIP methods
	 * and registers the name in the cache registry to allow
	 * for easier seek and manipulation at a later date.
	 *
	 * @param mixed    $hash The value to use in the hash uid.
	 * @param string   $name The name of the cache.
	 * @param mixed    $value The value to be cached.
	 * @param string   $group The group domain required for caching.
	 * @param int|null $expiry The length before cached object expires.
	 * @return void
	 */
	function idg_cache_set( $hash, string $name, $value, string $group = 'idg-base-theme', $expiry = null ) : void {
		$hash = md5( json_encode( $hash ) );
		wpcom_vip_cache_set( sprintf( '%s_%s', $name, $hash ), $value, $group, $expiry );
		idg_update_cache_registry( $hash, $name );
	}
}

if ( ! function_exists( 'idg_cache_registry_exists' ) ) {
	/**
	 * Checks whether the cache item exists in the
	 * cache regitry.
	 *
	 * @param string $name The name of the cache to retrieve.
	 * @return bool
	 */
	function idg_cache_registry_exists( string $name, string $hash ) : bool {
		global $post;

		$post_id = isset( $post->ID ) ? "id-$post->ID" : null;

		$cached_keys = wpcom_vip_cache_get( 'keys', 'idg-cache-registry' );

		if ( $post_id ) {
			if ( isset( $cached_keys[ $post_id ] ) ) {
				// Check by post but IS set, so allow
				// further look up for this specific entry.
				$cached_keys = $cached_keys[ $post_id ];
			} else {
				// Check by post id but not set.
				return false;
			}
		} else {
			// Not looking up by post id, so filter out
			// items with post id.
			$values = array_filter(
				$cached_keys,
				function ( $key ) {
					return is_numeric( $key );
				},
				ARRAY_FILTER_USE_KEY
			);

			$cached_keys = [];

			// Reduce the array items down one level.
			array_walk_recursive(
				$values,
				function( $name, $hash ) use ( &$cached_keys ) {
					$cached_keys[ $hash ] = $name;
				}
			);
		}

		foreach ( $cached_keys as $cached_hash => $names ) {
			// Working with an actual post.
			if ( $name === $names && $hash === $cached_hash ) {
				return true;
			}
		}

		return false;
	}
}

if ( ! function_exists( 'idg_update_cache_registry' ) ) {
	/**
	 * Updates the cache registry with the provided keys.
	 *
	 * @param string     $hash Hash to be used in the registry.
	 * @param string     $cache_name The of the cache to be used.
	 * @param string|int $post_id The current post id
	 *                 - increments key list if not provided.
	 * @return void
	 */
	function idg_update_cache_registry( string $hash, string $cache_name, $post_id = null ) : void {
		global $post;

		$cached_keys = wpcom_vip_cache_get( 'keys', 'idg-cache-registry' );

		// Priority order: argument > global ID > next key.
		$post_id = $post_id ?: ( isset( $post->ID ) ? "id-$post->ID" : count( $cached_keys ) );

		$cached_keys[ $post_id ][ $hash ] = $cache_name;
		wpcom_vip_cache_set( 'keys', $cached_keys, 'idg-cache-registry' );
	}
}

if ( ! function_exists( 'idg_clear_caches' ) ) {
	/**
	 * Clears the cache based on the cache name.
	 *
	 * @param string $cache_name The name of the cache entry to use.
	 * @return void
	 */
	function idg_clear_caches( string $cache_name ) : void {
		$cached_keys = wpcom_vip_cache_get( 'keys', 'idg-cache-registry' );

		foreach ( $cached_keys as $post_id => $names ) {
			if ( ! in_array( $cache_name, $names, true ) ) {
				continue;
			}

			foreach ( $names as $name => $hash ) {
				if ( $name !== $cache_name ) {
					continue;
				}

				wpcom_vip_cache_delete( sprintf( '%s_%s', $cache_name, $hash ), 'idg-base-theme' );
				unset( $names[ $hash ] );
				$cached_keys[ $post_id ] = $names;

				if ( count( $cached_keys[ $post_id ] ) <= 0 ) {
					unset( $cached_keys[ $post_id ] );
				}
			}
		}

		wpcom_vip_cache_set( 'keys', $cached_keys, 'idg-cache-registry' );
	}
}

if ( ! function_exists( 'idg_clear_caches_by_id' ) ) {
	/**
	 * Clear all the caches assigned to a post ID.
	 *
	 * @param string|int $post_id The post ID of the caches to clear.
	 * @return void
	 */
	function idg_clear_caches_by_id( $post_id ) : void {
		$post_id     = "id-$post_id";
		$cached_keys = wpcom_vip_cache_get( 'keys', 'idg-cache-registry' );

		idg_set_error_report_meta(
			[
				'post_id'     => $post_id,
				'cached_keys' => $cached_keys,
			]
		);

		if ( ! isset( $cached_keys[ $post_id ] ) ) {
			return;
		}

		foreach ( $cached_keys[ $post_id ] as $cache_name => $hash ) {
			wpcom_vip_cache_delete( sprintf( '%s_%s', $cache_name, $hash ), 'idg-base-theme' );
		}

		unset( $cached_keys[ $post_id ] );

		wpcom_vip_cache_set( 'keys', $cached_keys, 'idg-cache-registry' );

		idg_set_error_report_meta(
			[
				'cleared_cached_keys' => $cached_keys,
			]
		);
	}
}

if ( ! function_exists( 'idg_wp_query_cache' ) ) {
	/**
	 * Creates a cache for the passed arguments that
	 * are to be used as a \WP_Query lookup. Will allow
	 * for bypassing in situations where cache requires
	 * updating.
	 *
	 * @param array  $args The arguments to be used in the WP_Query.
	 * @param string $cache_name The name of the cache to be created.
	 * @return \WP_Query
	 */
	function idg_wp_query_cache( array $args, string $cache_name ) {
		$bypass_cache = apply_filters( 'idg_bypass_query_cache', false, $args );

		if ( ! $bypass_cache ) {
			$cached = idg_cache_get( $args, $cache_name );

			if ( $cached ) {
				return $cached;
			}
		}

		$query = new WP_Query( $args );

		idg_cache_set( $args, $cache_name, $query, 'idg-base-theme', null );

		return $query;
	}
}

if ( ! function_exists( 'idg_bypass_query_cache' ) ) {
	/**
	 * Sets the bypass query cache to true.
	 * Used to avoid duplication functions on actions.
	 *
	 * @return void
	 */
	function idg_bypass_query_cache() {
		add_filter( 'idg_bypass_query_cache', '__return_true' );
	}
}

/**
 * Set cache bypass on save of post and page so it will
 * be cleared. Blocks are rendered at this point also,
 * so the cache will be updated.
 */
add_action( 'save_post', 'idg_clear_caches_by_id', 1, 1 );

if ( ! function_exists( 'idg_clear_caches_after_ingest' ) ) {
	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	function idg_clear_caches_after_ingest() {
		idg_notify_error( 'DeliverySite', 'Clearing Caches' );

		wp_cache_flush();
		idg_clear_caches( 'idg_hero_feed' );
		idg_clear_caches( 'idg_article_feed' );
		idg_clear_caches( 'idg_ajaxload_posts_offset' );
		idg_clear_caches( 'idg_ajaxload_posts' );
		idg_clear_caches( 'idg_sponsored_posts' );

		idg_notify_error( 'DeliverySite', 'Cleared Caches' );
	}
}

add_action( 'idg_publishing_flow_after_ingest', 'idg_clear_caches_after_ingest' );

/**
 * Sets the cache group `idg_base_theme_non_pers`
 * to be non persistent.
 */
wp_cache_add_non_persistent_groups( [ 'idg_base_theme_non_pers' ] );

if ( ! function_exists( 'idg_extend_purge_urls' ) ) {
	/**
	 * Extends the target purge url array with some additional
	 * targets such as pages. Also cleans up terms.
	 *
	 * @param array $urls The list urls to purge.
	 * @return array
	 */
	function idg_extend_purge_urls( $urls ) {
		if ( \IDG\Publishing_Flow\Sites::is_origin() ) {
			return $urls;
		}

		// IDG listings are added to a page,
		// so adding the pages will allow listings to be
		// purged.
		$pages = get_pages();

		$page_urls = array_map(
			function( $page ) {
				return get_permalink( $page->ID );
			},
			$pages
		);

		$urls = array_merge( $urls, $page_urls );

		// Because of the permastruct, we need to strip out additional
		// info that get's appended to some urls by default. This doesn't
		// happen outside of here.
		$urls = array_map(
			function( $url ) {
				return preg_replace( '/^(.+)\/article(\/[^0-9]+\/.+)$/', '$1$2', $url );
			},
			$urls
		);

		return $urls;
	}
}

add_action( 'wpcom_vip_cache_purge_post_post_urls', 'idg_extend_purge_urls' );
