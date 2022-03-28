<?php
/**
 * Contains required functions
 *
 * @package IDG-Golden-Taxonomy
 */

namespace IDG\Golden_Taxonomy;

/**
 * Recursively flatten categories with their parents.
 *
 * @param array $categorys - The categories to flatten.
 * @param array $list - The outputted list.
 * @return array
 */
function flatten_cat_with_parents( array $categorys, array &$list = [] ) : array {
	foreach ( $categorys as $category ) {
		$list[ $category['id'] ] = $category;

		if ( isset( $category['parent'] ) && is_array( $category['parent'] ) ) {
			if ( ! isset( $list[ $category['parent']['id'] ] ) ) {
				$list[ $category['parent']['id'] ] = $category['parent'];
			}

			flatten_cat_with_parents( [ $category['parent'] ], $list );
		}
	}

	return $list;
}

/**
 * Get categories transformed into something easier to work with.
 *
 * @param string|null $ids - The category ids to retrieve.
 * @return array
 */
function get_transformed_categories( array $ids = null ) : array {
	if ( ! $ids ) {
		return [];
	}

	$categories = get_terms(
		[
			'taxonomy'   => 'category',
			'include'    => $ids,
			'orderby'    => 'include', // IMPORTANT - retains order, the first item is the primary cateogory.
			'hide_empty' => false,
		]
	);

	if ( empty( $categories ) ) {
		return [];
	}

	return array_map(
		function( $category ) {
			$parent = 0 !== $category->parent ? get_transformed_categories( [ "{$category->parent}" ] )[0] : false;

			$term_meta = get_term_meta( $category->term_id, 'golden_id', true );
			$term_meta = is_array( $term_meta ) ? $term_meta[0] : $term_meta;

			return [
				'name'      => $category->name,
				'slug'      => $category->slug,
				'id'        => $category->term_id,
				'parent'    => $parent,
				'golden_id' => (int) $term_meta,
			];
		},
		$categories
	);
}

/**
 * Does a transformed cat contain a golden id?
 *
 * @param array $cat - The transformed category.
 * @return bool
 */
function has_golden_id( array $cat ) : bool {
	return isset( $cat['golden_id'] ) && $cat['golden_id'];
}

/**
 * Get all category data related to an article.
 *
 * @param integer $post_id - The article id to get the categories.

 * @return array
 */
function get_category_data( int $post_id = null ) : array {
	if ( ! $post_id ) {
		return [];
	}

	$category_ids = get_post_meta( $post_id, '_idg_post_categories', true );

	if ( empty( $category_ids ) ) {
		return [];
	}

	$categories = get_transformed_categories( $category_ids );


	if ( empty( $categories ) ) {
		return [];
	}

	$all_categories = flatten_cat_with_parents( $categories );

	$golden_categories = array_filter(
		$all_categories,
		__NAMESPACE__ . '\\has_golden_id'
	);

	$primary_cat = $categories[0];

	$primary_categories = flatten_cat_with_parents( [ $primary_cat ] );

	$primary_category_is_golden = has_golden_id( $primary_cat );

	$primary_golden_category = $primary_category_is_golden ? $primary_cat : null;

	$primary_golden_categories = array_filter(
		$primary_category_is_golden ? $primary_categories : [],
		__NAMESPACE__ . '\\has_golden_id'
	);

	return [
		// Categories exluding their ancestors.
		'user_selected_categories'  => $categories,
		// All user selected categories including their ancestors.
		'all_categories'            => $all_categories,
		// All categories with ancestors that are golden.
		'golden_categories'         => $golden_categories,
		// The primary category.
		'primary_category'          => $primary_cat,
		// The "first" category and it's ancestors.
		'primary_categories'        => $primary_categories,
		// The "first" category, if it is golden.
		'primary_golden_category'   => $primary_golden_category,
		// The "first" category, if it is golden with it's golden ancestors.
		'primary_golden_categories' => $primary_golden_categories,
	];
}
