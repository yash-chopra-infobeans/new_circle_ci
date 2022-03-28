<?php

/**
 * IMPORTANT
 * *********
 * This file exists for the sole purpose of what should be considered temporary workarounds between key and
 * major release phases. Everything within this file should not be considered a permanent solution for
 * any problems that they attempt to solve and may be removed at any point. DO NOT use any features,
 * functions, etc within anywhere else outside of this file.
 *
 * Any entries within this file should be clearly documented for future reference to understand a handful
 * of key factors and labelled as such;
 * 1) "PROBLEM" - A thorough outline of what the problem is that led to the work around in the file.
 * 2) "SOLUTION" - What is happening within the working around.
 * 3) "FUTURE" - What the current (at time of writing) plans are for removing the "solution" and in which
 *    phase of work that was expected to happen.
 */

/**
 * Instantiated on `admin_init` from the `setup.php` file.
 *
 * PROBLEM: The specification for the Publishing Flow and syndication of articles required that post id's from
 * the Content Hub are transferred to the target delivery site. While WordPress caters for this by allowing
 * the assignment of IDs when creating a post using the `import_id` value for the `wp_insert_post` args, this
 * causes an additional problem. When this happens, the `auto_increment` value of the `wp_posts` table then
 * matches `import_id`+1 ready for the next manual entry. While this will not cause issues if the posts are
 * all coming from the Content Hub, it can provide some potential for an additional post type to claim that
 * auto_increment id on the Delivery Site. Take the following example:
 *       1) Content Hub Post with ID 123 is sent to the Delivery Site
 *       2) Delivery Site auto_increment becomes 124.
 *       3) Delivery Site Page is created on Delivery Site assuming the automatic id 124.
 *       4) Content Hub Post with ID 124 is sent to the Delivery Site.
 *       5) Delivery Site Page with ID 124 is overwritten by Post with ID 124.
 *
 * This can obviously cause an unintended loss of data.
 *
 * SOLUTION: To get around this we need to ensure that WordPress cannot over-write any posts that have been
 * created organically, rather than via requests coming from the Content Hub. This solution here achieves this
 * by padding the Auto Increment value to a large number. WordPress' `wp_post.ID` schema type is an Unsigned BigInt
 * which gives it a maximum of 18,446,744,073,709,551,615. As it stands at the moment, the current (21st Feb) import sets
 * the Auto Increment value to ~558,500. To give more than enough buffer for posing being created on the Content Hub
 * organic entries been created on a Delivery Site, we can pad out the `wp_posts.ID` Auto Increment value to a high
 * number. To pad this out, all we need to do is create an article using standard WordPress `import_id` with a
 * large `wp_posts.ID` number that will allow for enough room for posts being imported without getting near pages.
 * This is achieved by checking that current Auto Increment value of the `wp_posts` table and creating that entry
 * if it is below the padded number.  The code below uses the number 100,000,000 providing a difference of
 * ~99,441,500 entries that can be created before this becomes another problem - however, the below future plans
 * around content management will prevent this from becoming an issue. That number can be changed by altering the
 * value of the `$padding` variable.
 *
 * FUTURE: One of the features presented for development was for all content to be managed on the Content Hub, this
 * includes Pages and ALL assets/attachments. Once this can occur, ID's can be inherited from the Content Hub and
 * used on the Delivery Site exactly how they are at the moment for posts and attachments. This will mean Syndication
 * for pages will require development - this was expected immediately post-launch of CIO.
 *
 * @return void
 */
function idg_wp_posts_increment_padding() {
	global $wpdb;

	if ( ! class_exists( '\IDG\Publishing_Flow\Sites' ) ) {
		return;
	}

	if ( \IDG\Publishing_Flow\Sites::is_origin() ) {
		return;
	}

	/**
	 * We can get the actual increment data from the table using a direct
	 * query. Normally we wouldn't want to do this but it's the most efficient
	 * way to do it.
	 */
	$results = $wpdb->get_results( "SHOW TABLE STATUS LIKE '$wpdb->posts'" );

	$padding = 100000000;

	if ( intval( $results[0]->Auto_increment ) >= $padding ) {
		return;
	}

	$value = bin2hex( random_bytes( 10 ) ) . '-padding-entry';

	wp_insert_post(
		[
			'import_id'    => $padding,
			'post_title'   => $value,
			'post_content' => $value,
			'post_name'    => $value,
			'post_status'  => 'trash',
		]
	);
	wp_delete_post( $padding, true );
}

/**
 * Instantiated on `pre_wp_unique_post_slug` from the `setup.php` file.
 *
 * PROBLEM: Due to the migration of attachments and products, we've landed with a situation where
 * some `post_name` are not only duplicated but are required elsewhere. What this has led to is the
 * `post_name` of pages requiring to be handled by WordPress and served a unique `post_name` as expected.
 * For example, after the migration we have ended up with the following situation for the `post_name`
 * where the slug `apple-tv` was applied to both an attachment AND the `Apple TV` product. This should
 * not have occurred in the first place and at least one of these should have had a degree of uniqueness.
 * Whilst these are the least of the worries in terms of uniqueness, the problem actually comes in when
 * attempting to re-create pages with news around a product name, such as `Apple TV`. This is example is
 * a required page for the site and because the required slug of `apple-tv` exists as both a `product` and
 * as an `attachment`, and when the page is create the uniqueness kicks in, updating the slug `apple-tv-{num}`
 * where `{num}` is an incremented value.
 *
 * SOLUTION: To get around this we need to remove that uniqueness requirement from pages. This is simply
 * done by checking that a post, page, etc does not exist with the requested `post_name`/slug and
 * simultaneously removes `products` and `attachments` from the query, allowing slug checks to ignore
 * posts in both of these post types. If a page or post with the chosen `page_name`/slug does exist,
 * uniqueness is forced and the slug process continues as expected.
 *
 * FUTURE: There are a number of possiblities that might need to be taken into account to allow for this in
 * the future;
 *     1) Live checking of conflicting slugs - so end users knows BEFORE save when a slug is not unique and
 *        will have a number appended.
 *     2) Changing all `product` and `attachments` to default with a prepended `{post_type}-{post_name}` slug
 *        structure to allow for avoiding of these clashes. It will have to be retroactively applied to content.
 *     3) Allow for a page or post to superseed any clashes by giving the user an option to define a rewrite rule
 *        that would allow WordPress to target the given page over the attachment/product using the same slug.
 *
 * @param string $override_slug Short-circuit return value.
 * @param string $slug The desired slug (post_name).
 * @param int    $post_id The post ID.
 * @param string $post_status The post status.
 * @param string $post_type The post type.
 * @return string
 */
function idg_bypass_page_slug_uniqueness( $override_slug, $slug, $post_id, $post_status, $post_type ) {
	// Only apply to pages.
	if ( 'page' !== $post_type ) {
		return $override_slug;
	}

	// Slug may have already been overridden so use that version.
	if ( $override_slug ) {
		$slug = $override_slug;
	}

	// Get a list of post types except for the ones we don't want to be unique against.
	$post_types = array_diff( array_keys( get_post_types() ), [ 'product', 'attachment' ] );

	// Get posts against the slug and post types.
	$existing = new WP_Query(
		[
			'name'      => $slug,
			'post_type' => $post_types,
		]
	);

	// If there are none, continue with slug creation.
	if ( $existing->post_count <= 0 ) {
		return $slug;
	}

	// If we're updating the same id, allow the current to continue using the defined.
	if ( $existing->post->ID === $post_id ) {
		return $slug;
	}

	return $override_slug;
}
