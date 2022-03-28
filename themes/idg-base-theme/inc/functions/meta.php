<?php
if ( ! function_exists( 'idg_base_theme_get_the_excerpt' ) ) {
	/**
	 * Gets excerpt of a post
	 *
	 * @param string $post_ID ID of the post.
	 */
	function idg_base_theme_get_the_excerpt( $post_ID = '' ) {
		$post_ID = $post_ID ?: get_the_ID();

		$post_meta   = get_post_meta( $post_ID, 'multi_title' );
		$multi_title = json_decode( $post_meta[0] );
		$headline    = $multi_title->titles->headline;
		if ( isset( $headline->additional ) ) {
			$description = $headline->additional->headline_desc ?: '';
		} else {
			$description = '';
		}

		return $description;
	}
}

if ( ! function_exists( 'idg_base_theme_is_no_author' ) ) {
	function idg_base_theme_is_no_author( $author_id ) {

		$author_login = get_the_author_meta( 'user_login', $author_id );

		return ( 'no-author' === $author_login );
	}
}

if ( ! function_exists( 'idg_base_theme_get_author_name' ) ) {
	/**
	 * Gets the author display name of a post
	 *
	 * @param int $author_id id of the author.
	 */
	function idg_base_theme_get_author_name( int $author_id, $post_id = null ) {
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}

		if ( idg_base_theme_is_no_author( $author_id ) ) {
			if ( has_term( 'dealpost', 'story_types', $post_id ) ) {
				return __( 'DealPost Team', 'idg-base-theme' );
			} else {
				return __( 'Macworld Staff', 'idg-base-theme' );
			}
		}

		$author_firstname = get_the_author_meta( 'first_name', $author_id );
		if ( ! $author_firstname ) {
			return;
		}

		$author_lastname = get_the_author_meta( 'last_name', $author_id );
		if ( ! $author_lastname ) {
			$author = $author_firstname;
		} else {
			$author = $author_firstname . ' ' . $author_lastname;
		}

		return $author;
	}
}
