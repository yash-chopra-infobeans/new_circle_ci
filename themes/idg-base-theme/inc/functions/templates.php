<?php

namespace IDG\Base_Theme\Templates;

use function IDG\Base_Theme\Utils\map_by_key;

/**
 * Is home or front page?
 *
 * @return bool
 */
function home() {
	return is_home() || is_front_page();
}

/**
 * Is an article?
 *
 * @return bool
 */
function article() {
	return is_single() && 'post' === get_post_type();
}

/**
 * Is an index?
 *
 * @return bool
 */
function index() {
	return is_page();
}

/**
 * Is an archive?
 *
 * @return bool
 */
function archive() {
	if ( is_archive() ) {
		return true;
	}

	$post_id = get_the_ID();

	if ( ! $post_id ) {
		return false;
	}

	$content_type = get_post_meta( $post_id, 'content_type', true );

	if ( ! empty( $content_type ) && 'archive' === $content_type ) {
		return true;
	}

	return false;
}

/**
 * Is it a fireplace?
 *
 * @return bool
 */
function fireplace() {
	return home() || archive();
}

/**
 * Get content type.
 *
 * @return string
 */
function get_content_type() : string {
	global $post;

	if ( home() ) {
		return 'home page';
	}

	if ( archive() ) {
		return 'category index';
	}

	if ( is_search() ) {
		return 'search results';
	}

	$story_types = ! $post->ID ?: map_by_key( 'name', get_the_terms( $post->ID, 'story_types' ) ?: [] );

	if ( article() && isset( $story_types[0] ) ) {
		return $story_types[0];
	}

	return $post->post_name . ' index';
}

/**
 * Get page type
 *
 * @return string
 */
function get_page_type() : string {
	if ( home() ) {
		return 'homepage';
	}

	if ( article() ) {
		return 'article';
	}

	return 'other';
}


/**
 * Get display type.
 *
 * @return string
 */
function get_display_type() : string {
	global $post;

	if ( home() ) {
		return 'home page';
	}

	if ( archive() ) {
		$title = '';

		if ( is_archive() ) {
			$title = post_type_archive_title( '', false );
		} else {
			$title = single_post_title( '', false );
		}

		return 'category index: ' . strtolower( $title );
	}

	if ( is_404() ) {
		return 'error - 404';
	}

	$article_types = $post->ID ? map_by_key( 'name', get_the_terms( $post->ID, 'article_type' ) ?: [] ) : [];

	if ( 'default' === strtolower( $article_types[0] ) ) {
		return 'article';
	}

	if ( article() && isset( $article_types[0] ) ) {
		return $article_types[0];
	}

	// Fallback to post name.
	return $post->post_name . ' index';
}

/**
 * Returns page number.
 *
 * @return int
 */
function get_page_number() {
	$page = 1;

	if ( ! empty( get_query_var( 'page' ) ) ) {
		return (int) get_query_var( 'page' );
	}

	if ( ! empty( get_query_var( 'paged' ) ) ) {
		return (int) get_query_var( 'paged' );
	}

	return $page;
}

/**
 * Get source - primary publication.
 *
 * @return string
 */
function get_source() : string {
	if ( ! article() ) {
		return '';
	}

	$primary_publication = get_post_meta( get_the_ID(), 'primary_publication_id', true );

	if ( empty( $primary_publication ) ) {
		return '';
	}

	$publication = idg_get_publication_by_id( $primary_publication );

	if ( ! $publication ) {
		return '';
	}

	return strtolower( $publication->name );
}

/**
 * Get blog id if article is attached to a blog or were viewing a blog taxonomy archive.
 *
 * @param int $post WP_Post object.
 * @return int|string
 */
function get_blog_id( $post = null ) {
	$blog_id = '';

	if ( isset( $post ) ) {
		$blog = get_the_terms( $post->ID, 'blogs' );

		if ( $blog ) {
			$blog_id = $blog[0]->term_id;
		}
	}

	if ( is_tax( 'blogs' ) ) {
		$blog = get_queried_object();

		$blog_id = $blog->term_id;
	}

	return $blog_id;
}
