<?php
/**
 * Add various Filters
 *
 * @package idg-base-theme
 */

use function \IDG\Base_Theme\Utils\is_amp;
use function \IDG\Base_Theme\Utils\get_sponsored_posts;
use function IDG\Base_Theme\Utils\map_by_key;
use function IDG\Base_Theme\Utils\days_since;
use function IDG\Base_Theme\Utils\get_platform;
use function IDG\Base_Theme\Templates\article;
use function IDG\Base_Theme\Templates\get_content_type;
use function IDG\Base_Theme\Templates\get_display_type;
use function IDG\Base_Theme\Templates\get_page_type;
use function IDG\Base_Theme\Templates\get_page_number;
use function IDG\Base_Theme\Templates\get_source;
use function IDG\Base_Theme\Templates\get_blog_id;
use function IDG\Base_Theme\Templates\fireplace;

add_filter( 'get_canonical_url', '__return_null' );

if ( ! function_exists( 'idg_base_theme_create_robotstxt' ) ) {
	/**
	 * Adds rules to VIP robots.txt implementation.
	 *
	 * @param string $output robot content.
	 * @return string
	 */
	function idg_base_theme_create_robotstxt( $output ) {
		if ( class_exists( 'Jetpack_Sitemap_Finder' ) ) {
			$finder      = new Jetpack_Sitemap_Finder();
			$sitemap_url = $finder->construct_sitemap_url( 'sitemap-index-1.xml' );

			$output .= 'Sitemap: ' . esc_url( $sitemap_url ) . PHP_EOL;
		}

		$output .= 'Disallow: /search*' . PHP_EOL;

		return $output;
	}
}

add_filter( 'robots_txt', 'idg_base_theme_create_robotstxt' ); // phpcs:ignore -- ignore flush the robots.txt cache warning.

// Not using the default `sitemap.xml` as the base target so we need to disable Jetpack from generating it.
add_filter( 'jetpack_sitemap_include_in_robotstxt', '__return_false' );

if ( ! function_exists( 'idg_base_theme_clean_legacy_images' ) ) {
	/**
	 * Cleans up legacy image output so that width and heights are
	 * removed to prevent images from overflowing the content container.
	 *
	 * @param string $content The post content to parse.
	 * @return string
	 */
	function idg_base_theme_clean_legacy_images( $content ) {
		$dom = new DOMDocument();
		@$dom->loadHTML( mb_convert_encoding( $content, 'HTML-ENTITIES', 'UTF-8' ) ); // phpcs:ignore

		$elements = $dom->getElementsByTagName( 'img' );

		for ( $i = $elements->length - 1; $i >= 0; $i -- ) {
			$node_element = $elements->item( $i );

			if ( empty( $node_element->getAttribute( 'data-imageid' ) ) ) {
				continue;
			}

			$node_element->removeAttribute( 'width' );
			$node_element->removeAttribute( 'height' );
			$node_element->removeAttribute( 'border' );
		}

		return $dom->saveHTML();
	}
}
// phpcs:ignore -- valid commented code warning.
// add_filter( 'the_content', 'idg_base_theme_clean_legacy_images' ); 

if ( ! function_exists( 'idg_base_theme_add_lazy_load_images' ) ) {
	/**
	 * Added lazy loading on legacy image.
	 *
	 * @param string $content The post content to parse.
	 * @return string
	 */
	function idg_base_theme_add_lazy_load_images( $content ) {
		$dom = new DOMDocument();
		$dom->loadHTML( mb_convert_encoding( $content, 'HTML-ENTITIES', 'UTF-8' ) );
		$elements = $dom->getElementsByTagName( 'img' );
		for ( $i = $elements->length - 1; $i >= 0; $i -- ) {
			$node_element = $elements->item( $i );

			if ( empty( $node_element->getAttribute( 'loading' ) ) ) {
				$node_element->setAttribute( 'loading', 'lazy' );
			}
		}
		return $dom->saveHTML();
	}
}

add_filter( 'the_content', 'idg_base_theme_add_lazy_load_images' );
// phpcs:ignore -- valid commented code warning.
// add_filter( 'the_content', 'idg_base_theme_clean_legacy_images' );

add_filter(
	'idg_posttypes_allowed_copyright_info',
	function ( $post_types = [] ) {
		return array_merge( $post_types, [ 'post' ] );
	}
);

add_filter(
	'idg_svg_allowed_tags',
	function ( $tags = [] ) {
		$tags = [
			'svg'  => [
				'xmlns'   => [],
				'viewbox' => [],
			],
			'path' => [
				'd' => [],
			],
		];
		return $tags;
	}
);

add_filter(
	'idg_post_share_options',
	function ( $share_icons = [] ) {
		$permalink                   = rawurlencode( get_the_permalink() );
		$title                       = rawurlencode( get_the_title() );
		$print_icon_extra_attributes = is_amp() ?
			[
				'on'     => 'tap:AMP.print()',
				'target' => '_blank',
			] : [];

		return array_merge(
			$share_icons,
			[
				[
					'icon-name' => 'facebook',
					'icon-file' => 'facebook.svg',
					'icon-text' => 'Share on Facebook',
					'icon-url'  => 'https://www.facebook.com/sharer/sharer.php?u=' . $permalink,
				],
				[
					'icon-name' => 'twitter',
					'icon-file' => 'twitter.svg',
					'icon-text' => 'Share on Twitter',
					'icon-url'  => 'https://twitter.com/intent/tweet?url=' . $permalink . '&text=' . $title,
				],
				[
					'icon-name' => 'linkedin',
					'icon-file' => 'linkedin-in.svg',
					'icon-text' => 'Share on LinkedIn',
					'icon-url'  => 'https://www.linkedin.com/shareArticle?mini=true&url=' . $permalink . '&title=' . $title,
				],
				[
					'icon-name' => 'reddit',
					'icon-file' => 'reddit-alien.svg',
					'icon-text' => 'Share on Reddit',
					'icon-url'  => 'https://www.reddit.com/submit?url=' . $permalink . '&title=' . $title,
				],
				[
					'icon-name' => 'email',
					'icon-file' => 'envelope.svg',
					'icon-text' => 'Share via Email',
					'icon-url'  => 'mailto:?Subject=' . $title . '&amp;Body=' . rawurlencode( __( 'Check out this article from', 'idg-base-theme' ) ) . rawurlencode( get_bloginfo( 'name' ) ) . '%20' . $permalink,
				],
				[
					'icon-name'        => 'print',
					'icon-file'        => 'print.svg',
					'icon-text'        => 'Print',
					'icon-url'         => 'javascript:window.print();',
					'extra_attributes' => $print_icon_extra_attributes,
				],
			]
		);
	}
);

/**
 * Removes taxonomy name from term title.
 */
add_filter(
	'get_the_archive_title',
	function ( $title ) {
		if ( is_category() ) {
			$title = single_cat_title( '', false );
		} elseif ( is_tag() ) {
			$title = single_tag_title( '', false );
		} elseif ( is_author() ) {
			$title = '<span class="vcard">' . get_the_author() . '</span>';
		} elseif ( is_tax() ) {
			$title = sprintf( '%1$s', single_term_title( '', false ) );
		} elseif ( is_post_type_archive() ) {
			$title = post_type_archive_title( '', false );
		}
		return $title;
	}
);

add_filter(
	'idg_article_paragraph',
	function( $content, $count ) {
		$n = (int) cf_get_value( 'third_party', 'jw_player', 'config.insert_after_p' );

		if ( empty( $n ) ) {
			$n = 4;
		}

		$playlist = cf_get_value( 'third_party', 'jw_player', 'config.floating_player_playlist_id' );


		if ( $count !== $n || ! $playlist || ! idg_can_display_floating_video( get_the_ID() ) ) {
			return $content;
		}

		ob_start();

		$player = cf_get_value( 'third_party', 'jw_player', 'config.amp_player_library_id' );

		if ( empty( $player ) ) {
			$player = 'wySF9V4I';
		}

		?>

		<?php
		if ( is_amp() ) {
			?>
				<amp-jwplayer layout="responsive" width="16" height="9" data-player-id="<?php echo esc_attr( $player ); ?>" data-playlist-id="<?php echo esc_attr( $playlist ); ?>"/>
			<?php
		} else {
			?>

			<div class="jwPlayer--floatingContainer">
				<div id="jwplayer--floatingVideo" class="jwplayer" data-media-id="<?php echo esc_attr( $playlist ); ?>">
				</div>
			</div>
			<?php
		}

		return $content . ob_get_clean();
	},
	10,
	2
);

/**
 * Adds sponsored wrapper to `core/html` block if sponsored.
 *
 * @SuppressWarnings(PHPMD)
 */

if ( ! function_exists( 'idg_sponsored_eyebrow_render' ) ) {
	/**
	 * Extend core/html block to add sponsored embed - if sponsored.
	 *
	 * @param string $block_content the block content.
	 * @param string $block - check the block name to add sponsored embed wrapper.
	 *
	 * @return mixed
	 */
	function idg_sponsored_eyebrow_render( $block_content, $block ) {
		$attributes = $block['attrs'];
		if ( 'core/html' !== $block['blockName'] || true !== $attributes['sponsored'] ) {
			return $block_content;
		}
		$post_id      = get_the_ID();
		$eyebrow_info = idg_base_theme_get_eyebrow( $post_id );
		if ( ! isset( $eyebrow_info['eyebrow_sponsorship'] ) ) {
			return $block_content;
		}

		$eyebrow_sponsorship = $eyebrow_info['eyebrow_sponsorship'];
		$sponsorship         = idg_base_theme_get_sponsorship( $post_id );

		if ( ! $eyebrow_sponsorship ) {
			return $block_content;
		}

		ob_start();
		?>

		<div class="sponsored-embed">

			<span class="item-eyebrow-sponsored-by-text">
			<?php printf( '%s %s', esc_html__( 'Sponsored by', 'idg' ), esc_html( $eyebrow_sponsorship ) ); ?>
			</span>

			<?php if ( $sponsorship['tooltip'] ) : ?>

			<div class="tooltip">
				<a href="#" class="tooltip-learn-more" role="tooltip" aria-describedby="tooltip-p">
				<?php _e( 'Learn More', 'idg' ); ?>
				</a>
				<div class="tooltip-box" id="tooltip-box">
					<a href="#" class="tooltip-close" role="tooltip">
						<?php echo wp_kses( get_idg_asset( '/icons/times-2.svg' ), apply_filters( 'idg_svg_allowed_tags', [] ) ); ?>
					</a>
					<div class="tooltip-text">
						<p id="tooltip-p">
							<?php echo esc_html( $sponsorship['tooltip'] ); ?>
						</p>
					</div>
				</div>
			</div>

			<?php endif; ?>

			<?php echo wp_kses_post( $block_content ); ?>

		</div>

		<?php
		return ob_get_clean();
	}
}

add_filter( 'render_block', 'idg_sponsored_eyebrow_render', 10, 2 );



if ( ! function_exists( 'render_iframe' ) ) {
	/**
	 * Add iFrame to allowed wp_kses_post tags
	 *
	 * @param string $tags Allowed tags, attributes, and/or entities.
	 * @param string $context Context to judge allowed tags by. Allowed values are 'post'.
	 *
	 * @return mixed
	 */
	function render_iframe( $tags, $context ) {
		if ( 'post' === $context ) {
			$tags['iframe'] = [
				'allowfullscreen' => true,
				'frameborder'     => true,
				'height'          => true,
				'src'             => true,
				'style'           => true,
				'width'           => true,
			];
		}
		return $tags;

	}
}
add_filter( 'wp_kses_allowed_html', 'render_iframe', 10, 2 );

if ( ! function_exists( 'idg_base_theme_default_hidden_columns' ) ) {
	/**
	 * Sets the default hidden columns for the theme.
	 *
	 * @param string[] $hidden Array of IDs of columns hidden by default.
	 * @param object   $screen Object of the current screen.
	 */
	function idg_base_theme_default_hidden_columns( $hidden, $screen ) {
		if ( isset( $screen->id ) && 'edit-post' === $screen->id ) {
			$hidden[] = 'categories';
			$hidden[] = 'tags';
			$hidden[] = 'taxonomy-publication';
			$hidden[] = 'taxonomy-story_types';
			$hidden[] = 'taxonomy-article_type';
			$hidden[] = 'taxonomy-sponsorships';
			$hidden[] = 'taxonomy-blogs';
			$hidden[] = 'taxonomy-podcast_series';
		}
		return $hidden;
	}
}
add_filter( 'default_hidden_columns', 'idg_base_theme_default_hidden_columns', 10, 2 );


/**
 * Add theme specific variables to the datalayer.
 *
 * @SuppressWarnings(PHPMD)
 */

if ( ! function_exists( 'idg_base_theme_data_layer' ) ) {
	/**
	 * Add theme specific variables to the datalayer.
	 *
	 * @param array $data - The current datalayer.
	 * @return array
	 */
	function idg_base_theme_data_layer( array $data ) : array {
		// If 'page' or 'post' single page get_post otherwise return null and set empty values.
		$post           = is_page() || article() ? get_post() : null;
		$post_id        = isset( $post->ID ) ? $post->ID : '';
		$article_types  = map_by_key( 'name', get_the_terms( $post_id, 'article_type' ) ?: [] );
		$story_types    = map_by_key( 'name', get_the_terms( $post_id, 'story_types' ) ?: [] );
		$blogs          = map_by_key( 'name', get_the_terms( $post_id, 'blogs' ) ?: [] );
		$podcasts       = map_by_key( 'name', get_the_terms( $post_id, 'podcast_series' ) ?: [] );
		$sponsored_post = map_by_key( 'name', get_the_terms( $post_id, 'sponsorships' ) ?: [] );
		$page_number    = get_page_number();
		$legacy_id      = $post ? get_post_meta( $post->ID, 'old_id_in_onecms', true ) : false;
		$update_date    = get_post_meta( get_the_id(), '_idg_updated_date' )[0];

		if ( empty( $update_date ) ) {
			$update_date = isset( $post->post_modified ) ? mysql2date( 'Y-m-d', $post->post_modified ) : '';
		}

		$legacy_byline = get_post_meta( $post_id, 'byline', true );

		if ( ! empty( $legacy_byline ) && is_string( $legacy_byline ) ) {
			$amended_author = $legacy_byline;
		} else {
			$amended_author = isset( $post->post_author ) ? get_the_author_meta( 'display_name', $post->post_author ) : '';
		}

		return array_merge(
			$data,
			[
				'articleId'          => "{$post_id}",
				'articleTitle'       => isset( $post->post_title ) ? "{$post->post_title}" : '',
				'articleType'        => $article_types[0] ?? '',
				'author'             => $amended_author,
				'isBlog'             => empty( $blogs ) ? 'false' : 'true',
				'blogname'           => $blogs[0] ?? '',
				'blogId'             => get_blog_id(),
				'brandpost'          => is_array( $story_types ) && isset( $story_types[0] ) && 'brandpost' === strtolower( $story_types[0] ) ? 'true' : 'false',
				'content_type'       => get_content_type(),
				'datePublished'      => isset( $post->post_date ) ? mysql2date( 'Y-m-d', $post->post_date ) : '',
				'dateUpdate'         => $update_date,
				'dateTimePublished'  => isset( $post->post_date ) ? mysql2date( 'c', $post->post_date ) : '',
				'dateTimeUpdate'     => isset( $post->post_modified ) ? mysql2date( 'c', $post->post_modified ) : '',
				'daysSincePublished' => isset( $post->post_date ) ? days_since( $post->post_date ) : '',
				'daysSinceUpdated'   => isset( $post->post_modified ) ? days_since( $post->post_modified ) : '',
				'description'        => idg_base_theme_get_the_excerpt( $post->ID ),
				'displayType'        => get_display_type(),
				'fireplace'          => fireplace() ? 'true' : 'false',
				'pageNumber'         => "{$page_number}",
				'page_type'          => get_page_type(),
				'platform'           => get_platform(),
				'podcastSponsored'   => empty( $podcasts ) ? 'false' : 'true',
				'property'           => get_bloginfo( 'name' ),
				'source'             => get_source(),
				'sponsorName'        => $sponsored_post[0] ?? '',
				'tags'               => map_by_key( 'name', get_the_tags( $post_id ) ?: [] ),
				'legacyCmsId'        => ! empty( $legacy_id ) ? $legacy_id : '',
			]
		);
	}
}
add_filter( 'idg_data_layer', 'idg_base_theme_data_layer' );

if ( ! function_exists( 'idg_base_theme_rest_filter_sponsored_content' ) ) {
	/**
	 * Filters out sponsored posts from rest results if `exclude_sponsored` is true.
	 *
	 * @param array           $args Key value array of query var to query value.
	 * @param WP_REST_Request $request The request used.
	 *
	 * @see https://developer.wordpress.org/reference/hooks/rest_this-post_type_query/
	 */
	function idg_base_theme_rest_filter_sponsored_content( $args, $request ) {
		if ( 'true' === $request['exclude_sponsored'] ) {
			$excluded_posts       = array_merge( [ get_the_ID() ], get_sponsored_posts() );
			$args['post__not_in'] = $excluded_posts;
		}
		return $args;
	}
}
add_filter( 'rest_post_query', 'idg_base_theme_rest_filter_sponsored_content', 10, 3 );


/**
 * Add next and number
 *
 * @SuppressWarnings(PHPMD)
 */

if ( ! function_exists( 'add_next_and_number' ) ) {
	/**
	 * Add next and number
	 *
	 * @param array $args - Args.
	 * @return array
	 * @SuppressWarnings(PHPMD)
	 */
	function add_next_and_number( $args ) {
		if ( 'next_and_number' == $args['next_or_number'] ) {
			global $page, $numpages, $multipage, $more, $pagenow;
			$args['next_or_number'] = 'number';
			$prev                   = '';
			$next                   = '';
			if ( $multipage ) {
				if ( $more ) {
					$i     = $page - 1;
					$prev .= _wp_link_page( $i );
					$prev .= $args['link_before'] . $args['previouspagelink'] . $args['link_after'] . '</a>';
					if ( $numpages > 5 && $page !== $numpages && $page !== $numpages - 1 && $page !== $numpages - 2 ) {
						$next .= _wp_link_page( $numpages );
						$next .= $args['echo'] . '</a>';
					}
					$i     = $page + 1;
					$next .= _wp_link_page( $i );
					$next .= $args['link_before'] . $args['nextpagelink'] . $args['link_after'] . '</a>';
				}
			}
			$args['before'] = $args['before'] . $prev;
			$args['after']  = $next . $args['after'];
		}
		return $args;
	}
}
add_filter( 'wp_link_pages_args', 'add_next_and_number' );


/**
 * Funtion to check if post has external link, then return that link otherwise permalink
 *
 * @param string $permalink - Permalink.
 * @param object $post - Post Object.
 * @return string
 */
function idg_check_for_external_redirect( $permalink, $post ) {

	// Condition for Admin preview funcitonality.
	if ( function_exists( 'get_current_screen' ) ) {  
		
		$currentScreen = get_current_screen();

		if ( isset( $currentScreen->base ) && 'post' === $currentScreen->base ) { 
			return $permalink;
		}   
	}

	// Condition for Add/Edit post page in backend.
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return $permalink;
	}

	// Condition for Front-end.
	if ( 'post' === $post->post_type ) {

		$redirect_link = trim( get_post_meta( $post->ID, 'external_post_link', true ) );

		if ( ! empty( $redirect_link ) ) {
			
			$permalink = $redirect_link;
		
		}   
	}
	
	return $permalink;

}

add_filter( 'post_link', 'idg_check_for_external_redirect', 10, 2 );


/**
 * Check if post has external link then redirect it
 */
function idg_check_on_single_post_for_external_redirect() {

	global $post;

	if ( is_singular( 'post' ) && ! is_preview() ) {

		$redirect_link = trim( get_post_meta( $post->ID, 'external_post_link', true ) );
		
		if ( ! empty( $redirect_link ) ) {
		
			wp_redirect( esc_url( $redirect_link ), 301 ); // phpcs:ignore
		
			exit;
		
		}   
	}

}

add_action( 'template_redirect', 'idg_check_on_single_post_for_external_redirect' );

if ( ! function_exists( 'call_back_to_handle_schedule_update_post' ) ) {
	/**
	 * Stop publication flow if post status is transition from Publish to "Future" (scheduled post)
	 *
	 * @param boolean $flag Current flag value.
	 * @param string  $new_status New Post Transition status.
	 * @return boolean
	 */
	function call_back_to_handle_schedule_update_post( $flag, $new_status ) {

		if ( 'future' === $new_status ) {
			$flag = false;
		}

		return $flag;

	}
}

add_filter( 'idg_filter_handle_transition_from_publish', 'call_back_to_handle_schedule_update_post', 10, 2 );
