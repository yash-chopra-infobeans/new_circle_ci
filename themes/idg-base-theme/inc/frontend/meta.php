<?php
/**
 * File contain the method that are setting meta tags
 *
 * @package frontend
 */

if ( ! function_exists( 'idg_base_theme_meta_title' ) ) {
	/**
	 * Allow for the title to use either multi-title SEO title
	 * or fallback to the standard title/headline.
	 *
	 * @return string
	 */
	function idg_base_theme_meta_title() {

		global $wp;
		$post_id     = get_the_ID();
		$multititle  = json_decode( get_post_meta( $post_id, 'multi_title', true ), true );
		$author_info = ( get_query_var( 'author_name' ) ) ? get_user_by( 'slug', get_query_var( 'author_name' ) ) : get_userdata( get_query_var( 'author' ) );
		
		if ( 'browse' === $wp->request ) {
			$paged = 1;
		} else {
			$paged = get_query_var( 'paged', '1' );
		}
		
		if ( isset( $paged )
				&& ( 
					$paged > 0
					|| 'browse' === $wp->request
				)
			) {
				return sprintf(
				/* translators: %1$s: Site title, %2$s: Page number */
					__( 'More don\'t-miss stories from %1$s – Page %2$u', 'idg-base-them' ),
					get_bloginfo( 'name' ),
					$paged
				);
		} elseif ( ! empty( $author_info ) ) {
			return sprintf( 
				/* translators: %1$s: Author's firstname, %2$s: Author's lastname */
				__( '%1$s %2$s – Author', 'idg-base-them' ),
				$author_info->first_name,
				$author_info->last_name
			);
		} elseif ( 'feed' === $wp->request ) {
			$get_site_name = [ 'http://', 'http://www.', 'www.', 'https://', 'https://www.' ];
			$site_url      = str_replace( $get_site_name, '', get_bloginfo( 'wpurl' ) );
			return ucfirst( $site_url );
		} elseif (
			isset( $multititle['titles']['seo'] )
			&& isset( $multititle['titles']['seo']['value'] )
		) {
			$title_tag = $multititle['titles']['seo']['value'];
			// Check to see if article meta title already has site name appended through SEO field on post.
			if ( is_single() && 'post' === get_post_type() && ( ! strpos( $title_tag, ' - ' . get_bloginfo( 'name' ) ) && ! strpos( $title_tag, ' | ' . get_bloginfo( 'name' ) ) ) ) {
				$title_tag = $title_tag . ' | ' . get_bloginfo( 'name' );
			}
			return $title_tag;
		}

		return get_the_title( $post_id );
	}
}
add_action( 'pre_get_document_title', 'idg_base_theme_meta_title' );

if ( ! function_exists( 'idg_base_theme_meta_list_string' ) ) {
	/**
	 * Create the required string format for the header meta of terms.
	 *
	 * @param array  $terms List of the terms to be used.
	 * @param string $key Name of the property key to be added.
	 * @return string
	 */
	function idg_base_theme_meta_list_string( $terms, string $key = 'name' ) : string {
		// Get all values from $key only as array.
		$result = array_column( $terms, $key );
		// Combine into comma seperated string.
		return implode( ', ', $result );
	}
}

if ( ! function_exists( 'idg_base_theme_cat_meta' ) ) {
	/**
	 * Adds categories to header meta.
	 */
	function idg_base_theme_cat_meta() {
		$categories = get_the_category( get_the_ID() );
		if ( ! empty( $categories ) ) {
			$cat_list = idg_base_theme_meta_list_string( $categories );
			printf( '<meta name="category" content="%s">' . "\n", esc_attr( $cat_list ) );
		}
	}
}

if ( ! function_exists( 'idg_base_theme_tag_meta' ) ) {
	/**
	 * Adds tags to header meta.
	 */
	function idg_base_theme_tag_meta() {
		$tags = get_the_tags( get_the_ID() );
		if ( ! empty( $tags ) ) {
			$tag_list = idg_base_theme_meta_list_string( $tags );
			printf( '<meta name="tags" content="%s">' . "\n", esc_attr( $tag_list ) );
		}
	}
}
/**
 * Handles the general theme emta.
 *
 * @SuppressWarnings(PHPMD)
*/  
if ( ! function_exists( 'idg_base_theme_meta' ) ) {
	/**
	 * Handles the general theme emta.
	 *
	 * @return void
	 */
	function idg_base_theme_meta() {
		global $wp;
		idg_base_theme_meta_html( 'name', 'displaytype', 'article' );
		$post_id              = get_the_ID();
		$multititle           = json_decode( get_post_meta( $post_id, 'multi_title', true ), true );
		$html_meta_supression = json_decode( get_post_meta( $post_id, 'suppress_html_meta', true ), true );
		$author_info          = ( get_query_var( 'author_name' ) ) ? get_user_by( 'slug', get_query_var( 'author_name' ) ) : get_userdata( get_query_var( 'author' ) );

		if (
			isset( $multititle['titles']['seo'] )
			&& isset( $multititle['titles']['seo']['additional']['seo_canonical_url'] )
			&& ! empty( $multititle['titles']['seo']['additional']['seo_canonical_url'] )
		) {
			$url = $multititle['titles']['seo']['additional']['seo_canonical_url'];
		} elseif ( ! empty( $author_info ) ) {
			$author_page_url = get_author_posts_url( $author_info->ID );
			$url             = $author_page_url ?: get_the_permalink();
		} else {
			$url = get_the_permalink();
		}

		if ( ! isset( $html_meta_supression['source'] ) ) {
			idg_base_theme_meta_html( 'name', 'source', $url );
		}

		if ( ! isset( $html_meta_supression['canonical_url'] ) ) {
			printf( '<link rel="canonical" href="%s" />' . "\n", esc_url( $url ) );
		}

		if ( is_single() && ! isset( $html_meta_supression['date'] ) ) {
			idg_base_theme_meta_html( 'name', 'date', get_the_date() );
		}

		if ( 'browse' === $wp->request ) {
			$paged = 1;
		} else {
			$paged = get_query_var( 'paged', '1' );
		}

		if ( isset( $paged )
			&& ( 
				$paged > 0
				|| 'browse' === $wp->request
			)
		) {
			$page_description = sprintf(
				/* translators: %1$s: Site title, %2$s: Page number */
				__( 'Latest articles from %1$s - news, analysis, reviews, deals and buying advice, tips and more on all things Apple-related - page %2$u', 'idg-base-them' ),
				get_bloginfo( 'name' ),
				$paged
			);
		} elseif ( ! empty( $author_info ) ) {
			$get_site_name    = [ 'http://', 'http://www.', 'www.', 'https://', 'https://www.' ];
			$site_url         = str_replace( $get_site_name, '', get_bloginfo( 'wpurl' ) );
			$page_description = sprintf(
				/* translators: %1$s: Author's firstname, %2$s: Author's lastname */
				__( 'Read expert opinions by %1$s %2$s at %3$s', 'idg-base-them' ),
				$author_info->first_name,
				$author_info->last_name,
				ucfirst( $site_url )
			);
		} elseif (
			isset( $multititle['titles']['social'] )
			&& isset( $multititle['titles']['social']['additional']['social_desc'] )
		) {
			$page_description = $multititle['titles']['social']['additional']['social_desc'];
		} elseif (
			isset( $multititle['titles']['headline'] )
			&& isset( $multititle['titles']['headline']['additional']['headline_desc'] )
		) {
			$page_description = $multititle['titles']['headline']['additional']['headline_desc'];
		} elseif ( ! is_single() ) {
			$page_description = cf_get_value( 'global_settings', 'page_meta', 'general.description' );
		}

		if ( ! isset( $html_meta_supression['description'] ) ) {
			idg_base_theme_meta_html( 'name', 'description', $page_description );
		}

		$fb_app_id = cf_get_value( 'global_settings', 'page_meta', 'search.fb_app' );
		idg_base_theme_meta_html( 'name', 'fb:app_id', $fb_app_id );

		$twitter_app_id = cf_get_value( 'global_settings', 'page_meta', 'search.twitter_id' );
		idg_base_theme_meta_html( 'name', 'twitter:account_id', $twitter_app_id );

		$google_id = cf_get_value( 'global_settings', 'page_meta', 'search.google' );
		idg_base_theme_meta_html( 'name', 'google-site-validation', $google_id );

		$bing_id = cf_get_value( 'global_settings', 'page_meta', 'search.bing' );
		idg_base_theme_meta_html( 'name', 'msvalidate.01', $bing_id );
	}
}
add_action( 'wp_head', 'idg_base_theme_meta' );
/**
 * Handles the opengraph meta.
 *
 * @SuppressWarnings(PHPMD)
*/
if ( ! function_exists( 'idg_base_theme_opengraph_meta' ) ) {
	/**
	 * Handles the opengraph meta.
	 *
	 * @SuppressWarnings(PHPMD)
	 * @return void
	 */
	function idg_base_theme_opengraph_meta() {
		global $wp;
		$post_id              = get_the_ID();
		$html_meta_supression = json_decode( get_post_meta( $post_id, 'suppress_html_meta', true ), true );
		$author_info          = ( get_query_var( 'author_name' ) ) ? get_user_by( 'slug', get_query_var( 'author_name' ) ) : get_userdata( get_query_var( 'author' ) );

		if ( isset( $html_meta_supression['open_graph'] ) ) {
			return;
		}

		$multititle = json_decode( get_post_meta( $post_id, 'multi_title', true ), true );

		if ( ! empty( $author_info ) ) {
			$author_page_url = get_author_posts_url( $author_info->ID );
			$url             = $author_page_url ?: get_the_permalink();
		} else {
			$url = get_the_permalink();
		}

		idg_base_theme_meta_html( 'property', 'og:type', is_single() ? 'article' : 'website' );
		idg_base_theme_meta_html( 'property', 'og:url', $url );
		idg_base_theme_meta_html( 'property', 'og:site_name', get_bloginfo( 'name' ) );

		if ( 'browse' === $wp->request ) {
			$paged = 1;
		} else {
			$paged = get_query_var( 'paged', '1' );
		}

		if ( isset( $paged )
			&& ( 
				$paged > 0
				|| 'browse' === $wp->request
			)
		) {
			$title = sprintf(
				/* translators: %1$s: Site title, %2$s: Page number */
				__( 'More don\'t-miss stories from %1$s – Page %2$u', 'idg-base-them' ),
				get_bloginfo( 'name' ),
				$paged
			);
		} elseif ( ! empty( $author_info ) ) {
			$title = sprintf(
				/* translators: %1$s: Author's firstname, %2$s: Author's lastname */
				__( '%1$s %2$s – Author', 'idg-base-them' ),
				$author_info->first_name,
				$author_info->last_name
			);
		} elseif (
			isset( $multititle['titles']['social'] )
			&& isset( $multititle['titles']['social']['value'] )
		) {
			$title = $multititle['titles']['social']['value'];
		} elseif ( is_single() ) {
			$title = get_the_title();
		} else {
			$title = cf_get_value( 'global_settings', 'page_meta', 'og.title' );
		}

		idg_base_theme_meta_html( 'property', 'og:title', $title );

		if ( isset( $paged )
			&& ( 
				$paged > 0
				|| 'browse' === $wp->request
			)
		) {
			$description = sprintf(
				/* translators: %1$s: Site title, %2$s: Page number */
				__( 'Latest articles from %1$s - news, analysis, reviews, deals and buying advice, tips and more on all things Apple-related - page %2$u', 'idg-base-them' ),
				get_bloginfo( 'name' ),
				$paged
			);
		} elseif ( ! empty( $author_info ) ) {
			$get_site_name = [ 'http://', 'http://www.', 'www.', 'https://', 'https://www.' ];
			$site_url      = str_replace( $get_site_name, '', get_bloginfo( 'wpurl' ) );
			$description   = sprintf(
				/* translators: %1$s: Author's firstname, %2$s: Author's lastname, %3$s: site name */
				__( 'Read expert opinions by %1$s %2$s at %3$s', 'idg-base-them' ),
				$author_info->first_name,
				$author_info->last_name,
				ucfirst( $site_url )
			);
		} elseif (
			isset( $multititle['titles']['social'] )
			&& isset( $multititle['titles']['social']['additional']['social_desc'] )
		) {
			$description = $multititle['titles']['social']['additional']['social_desc'];
		} elseif (
			isset( $multititle['titles']['headline'] )
			&& isset( $multititle['titles']['headline']['additional']['headline_desc'] )
		) {
			$description = $multititle['titles']['headline']['additional']['headline_desc'];
		} else {
			$description = cf_get_value( 'global_settings', 'page_meta', 'og.description' );
		}

		idg_base_theme_meta_html( 'property', 'og:description', $description );

		$image = wp_get_attachment_image_src( get_post_thumbnail_id(), 'original' );

		if ( $image
			&& empty( $author_info )
		) {
			idg_base_theme_meta_html( 'property', 'og:image', $image[0] );
			idg_base_theme_meta_html( 'property', 'og:image:width', $image[1] );
			idg_base_theme_meta_html( 'property', 'og:image:height', $image[2] );
		} elseif ( ! empty( $author_info ) ) {
			$author_profile_image = get_user_meta( $author_info->ID, 'profile-photo', true );
			if ( empty( $author_profile_image ) ) {
				$author_profile_image = get_stylesheet_directory_uri() . '/dist/static/img/default-featured-image.png';
				idg_base_theme_meta_html( 'property', 'og:image', $author_profile_image );
			} else {
				idg_base_theme_meta_html( 'property', 'og:image', $author_profile_image[ full ] );
			}
		} else {
			$image = '/dist/static/img/default-featured-image.png';
			if ( file_exists( get_stylesheet_directory() . $image ) ) {
				idg_base_theme_meta_html( 'property', 'og:image', get_stylesheet_directory_uri() . $image );
			}
		}
	}
}
add_action( 'wp_head', 'idg_base_theme_opengraph_meta' );
/**
 * Handles the twitter card meta.
 * 
 * @SuppressWarnings(PHPMD)
*/
if ( ! function_exists( 'idg_base_theme_twitter_meta' ) ) {
	/**
	 * Handles the twitter card meta.
	 *
	 * @return void
	 */
	function idg_base_theme_twitter_meta() {
		global $wp;
		$post_id              = get_the_ID();
		$html_meta_supression = json_decode( get_post_meta( $post_id, 'suppress_html_meta', true ), true );
		$author_info          = ( get_query_var( 'author_name' ) ) ? get_user_by( 'slug', get_query_var( 'author_name' ) ) : get_userdata( get_query_var( 'author' ) );

		if ( isset( $html_meta_supression['twitter'] ) ) {
			return;
		}

		$multititle = json_decode( get_post_meta( $post_id, 'multi_title', true ), true );

		if ( ! empty( $author_info ) ) {
			$author_page_url = get_author_posts_url( $author_info->ID );
			$url             = $author_page_url ?: get_the_permalink();
		} else {
			$url = get_the_permalink();
		}

		idg_base_theme_meta_html( 'property', 'twitter:card', 'summary_large_image' );
		idg_base_theme_meta_html( 'property', 'twitter:url', $url );
		idg_base_theme_meta_html( 'property', 'twitter:site', get_bloginfo( 'name' ) );

		if ( 'browse' === $wp->request ) {
			$paged = 1;
		} else {
			$paged = get_query_var( 'paged', '1' );
		}

		if ( isset( $paged )
			&& ( 
				$paged > 0
				|| 'browse' === $wp->request
			)
		) {
			$title = sprintf(
				/* translators: %1$s: Site title, %2$s: Page number */
				__( 'More don\'t-miss stories from %1$s – Page %2$u', 'idg-base-them' ),
				get_bloginfo( 'name' ),
				$paged
			);
		} elseif ( ! empty( $author_info ) ) {
			$title = sprintf( 
				/* translators: %1$s: Author's firstname, %2$s: Author's lastname */
				__( '%1$s %2$s – Author', 'idg-base-them' ),
				$author_info->first_name,
				$author_info->last_name
			);
		} elseif (
		isset( $multititle['titles']['social'] )
		&& isset( $multititle['titles']['social']['value'] )
		) {
			$title = $multititle['titles']['social']['value'];
		} elseif ( is_single() ) {
			$title = get_the_title();
		} else {
			$title = cf_get_value( 'global_settings', 'page_meta', 'og.title' );
		}

		idg_base_theme_meta_html( 'property', 'twitter:title', $title );

		if ( isset( $paged )
			&& ( 
				$paged > 0
				|| 'browse' === $wp->request
			)
		) {
			$description = sprintf(
				/* translators: %1$s: Site title, %2$s: Page number */
				__( 'Latest articles from %1$s - news, analysis, reviews, deals and buying advice, tips and more on all things Apple-related - page %2$u', 'idg-base-them' ),
				get_bloginfo( 'name' ),
				$paged
			);
		} elseif ( ! empty( $author_info ) ) {
			$get_site_name = [ 'http://', 'http://www.', 'www.', 'https://', 'https://www.' ];
			$site_url      = str_replace( $get_site_name, '', get_bloginfo( 'wpurl' ) );
			$description   = sprintf(
				/* translators: %1$s: Author's firstname, %2$s: Author's lastname, %3$s: site name */
				__( 'Read expert opinions by %1$s %2$s at %3$s', 'idg-base-them' ),
				$author_info->first_name,
				$author_info->last_name,
				ucfirst( $site_url )
			);
		} elseif (
			isset( $multititle['titles']['social'] )
			&& isset( $multititle['titles']['social']['additional']['social_desc'] )
		) {
			$description = $multititle['titles']['social']['additional']['social_desc'];
		} elseif (
			isset( $multititle['titles']['headline'] )
			&& isset( $multititle['titles']['headline']['additional']['headline_desc'] )
		) {
			$description = $multititle['titles']['headline']['additional']['headline_desc'];
		} else {
			$description = cf_get_value( 'global_settings', 'page_meta', 'twitter.description' );
		}

		idg_base_theme_meta_html( 'property', 'twitter:description', $description );

		$image = wp_get_attachment_image_src( get_post_thumbnail_id(), 'original' );

		if ( $image 
			&& empty( $author_info ) 
		) {
			idg_base_theme_meta_html( 'property', 'twitter:image', $image[0] );
		} elseif ( ! empty( $author_info ) ) {
			$author_profile_image = get_user_meta( $author_info->ID, 'profile-photo', true );
			if ( empty( $author_profile_image ) ) {
				$author_profile_image = get_stylesheet_directory_uri() . '/dist/static/img/default-featured-image.png';
				idg_base_theme_meta_html( 'property', 'twitter:image', $author_profile_image );
			} else {
				idg_base_theme_meta_html( 'property', 'twitter:image', $author_profile_image[ full ] );
			}
		} else {
			$image = '/dist/static/img/default-featured-image.png';
			if ( file_exists( get_stylesheet_directory() . $image ) ) {
				idg_base_theme_meta_html( 'property', 'twitter:image', get_stylesheet_directory_uri() . $image );
			}
		}
	}
}
add_action( 'wp_head', 'idg_base_theme_twitter_meta' );

if ( ! function_exists( 'idg_base_theme_next_prev_pagination' ) ) {
	/**
	 * Add next and prev relative links to meta.
	 *
	 * @return void
	 */
	function idg_base_theme_next_prev_pagination() {
		global $paged;
		$post_id              = get_the_ID();
		$html_meta_supression = json_decode( get_post_meta( $post_id, 'suppress_html_meta', true ), true );

		if ( isset( $html_meta_supression['pagination'] ) ) {
			return;
		}

		if ( get_previous_posts_link() ) {
			printf( '<link rel="prev" href="%s">' . "\n", esc_attr( get_pagenum_link( $paged - 1 ) ) );
		}

		if ( get_next_posts_link() ) {
			printf( '<link rel="next" href="%s">' . "\n", esc_attr( get_pagenum_link( $paged + 1 ) ) );
		}
	}
}
add_action( 'wp_head', 'idg_base_theme_next_prev_pagination' );

if ( ! function_exists( 'idg_base_theme_meta_html' ) ) {
	/**
	 * Prints the given meta element with the chosen property declaration.
	 *
	 * @param string $attr The property name.
	 * @param string $attr_value The property value.
	 * @param string $content_value The content value.
	 * @return void
	 */
	function idg_base_theme_meta_html( $attr, $attr_value, $content_value ) {
		if ( ! empty( $content_value ) ) {
			printf( '<meta %s="%s" content="%s" />' . "\n", esc_attr( $attr ), esc_attr( $attr_value ), esc_attr( $content_value ) );
		}
	}
}

if ( ! function_exists( 'idg_base_theme_no_index_meta' ) ) {
	/**
	 * Adds no index meta to the posts/pages
	 */
	function idg_base_theme_no_index_meta() {
		$post_id       = get_the_ID();
		$prevent_index = get_post_meta( $post_id, 'prevent_index', true );
		
		if ( $prevent_index ) {
			printf( '<meta name="robots" content="noindex,nofollow">' );
		}
	}
}
add_action( 'wp_head', 'idg_base_theme_no_index_meta', 1 );
