<?php
/**
 * Contains required functions for themes
 *
 * @package idg-base-theme
 */

/**
 * Temporary disable of concat.
 */
add_filter( 'css_do_concat', '__return_false' );

/**
 * Redirects to the WordPress admin. Does not apply in
 * the following situations;
 *     1. \IDG\Publishing_Flow\Sites:class does not exist.
 *     2. Is not the Content Hub.
 *     3. Is not an article when user is logged in.
 *
 * @return void
 */
function redirect_to_backend() : void {
	if ( ! class_exists( '\IDG\Publishing_Flow\Sites' ) ) {
		return;
	}
	$is_content_hub = \IDG\Publishing_Flow\Sites::is_origin();

	if ( ! $is_content_hub ) {
		return;
	}

	$current_user = wp_get_current_user();

	// Use current user as is_logged_in() not available at this point.
	if ( $current_user->exists() ) {
		return;
	}

	if ( ! is_admin() && ! is_preview() ) {
		wp_safe_redirect( site_url( 'wp-admin' ) );
		exit;
	}
}

add_action( 'template_redirect', 'redirect_to_backend' );

/**
 * Asset settings load.
 */
require get_template_directory() . '/inc/asset-settings.php';

/**
 * Frontend.
 */
require get_template_directory() . '/inc/frontend/loader.php';
require get_template_directory() . '/inc/frontend/theme-support.php';
require get_template_directory() . '/inc/frontend/walker.php';
require get_template_directory() . '/inc/frontend/meta.php';

/**
 * Gutenberg.
 */
require get_template_directory() . '/inc/gutenberg/loader.php';

/**
 * Admin.
 */
require get_template_directory() . '/inc/admin/loader.php';
require get_template_directory() . '/inc/admin/roles.php';
require get_template_directory() . '/inc/admin/permissions.php';

/**
 * AMP.
 */
require get_template_directory() . '/inc/amp/loader.php';

/**
 * Functions.
 */
require get_template_directory() . '/inc/functions/utils.php';
require get_template_directory() . '/inc/functions/templates.php';
require get_template_directory() . '/inc/functions/cache.php';
require get_template_directory() . '/inc/functions/assets.php';
require get_template_directory() . '/inc/functions/media.php';
require get_template_directory() . '/inc/functions/eyebrows.php';
require get_template_directory() . '/inc/functions/sponsorship.php';
require get_template_directory() . '/inc/functions/meta.php';
require get_template_directory() . '/inc/functions/products.php';
require get_template_directory() . '/inc/functions/sponsored-links.php';

/**
 * Ajax loading.
 */
require get_template_directory() . '/inc/ajax-load/posts.php';

/**
 * Filters.
 */
require get_template_directory() . '/inc/actions.php';
require get_template_directory() . '/inc/filters.php';
require get_template_directory() . '/inc/class-idg-block-parser.php';
require get_template_directory() . '/inc/class-idg-paragraph-filter.php';

/**
 * String manipulation helpers.
 */
require get_template_directory() . '/inc/string-manipulation-helpers.php';

/**
 * Profile fields.
 */
require get_template_directory() . '/inc/class-idg-profile-photo.php';
require get_template_directory() . '/inc/profile.php';

/**
 * `post_types` registration & settings.
 */
require get_template_directory() . '/inc/post-types.php';

/**
 * Register and get taxonomies.
 */
require get_template_directory() . '/inc/taxonomies.php';
require get_template_directory() . '/inc/taxonomy-fields/index.php';

/**
 * Register & display new post meta fields.
 */
require get_template_directory() . '/inc/post-meta.php';

/**
 * Add global options pages.
 */
require get_template_directory() . '/inc/options/global.php';

/**
 * Navigation.
 */
require get_template_directory() . '/inc/navigation.php';

/**
 * Templating.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Rest API.
 */
require get_template_directory() . '/inc/rest-api/custom-api-fields.php';
require get_template_directory() . '/inc/rest-api/class-idg-category-rest.php';
require get_template_directory() . '/inc/rest-api/class-idg-tag-rest.php';
require get_template_directory() . '/inc/rest-api/class-idg-story-types-rest.php';
require get_template_directory() . '/inc/rest-api/class-idg-article-subtag-lookup-rest.php';
require get_template_directory() . '/inc/rest-api/class-idg-article-type-rest.php';
require get_template_directory() . '/inc/rest-api/class-idg-sponsorships-rest.php';
require get_template_directory() . '/inc/rest-api/class-idg-blogs-rest.php';
require get_template_directory() . '/inc/rest-api/class-idg-podcast-series-rest.php';
require get_template_directory() . '/inc/rest-api/class-idg-amp-products-rest.php';


//git circle ci demo n sdas new branch 2 new test dd neww jj

