<?php

namespace IDG\Golden_Taxonomy;

use IDG\Third_Party\Base_Data_Layer;
use function IDG\Golden_Taxonomy\Utils\map_by_index;
use function IDG\Base_Theme\Templates\article;

/**
 * Add category data to the datalayer.
 */
class Data_Layer {
	/**
	 * Add actions.
	 */
	public function __construct() {
		add_filter( Base_Data_Layer::FILTER, [ $this, 'add_category_data' ] );
	}

	/**
	 * Add category data to the data layer.
	 *
	 * @param array - $data - The data layer.
	 *
	 * @return array
	 */
	public function add_category_data( array $data ) : array {
		// Category data is only really added to article so we check if current page is an article ('post' post type).
		$post = article() ? get_post() : null;

		if ( ! isset( $post->ID ) ) {
			return array_merge(
				$data,
				[
					'categories'                       => '',
					'categoryIds'                      => '',
					'categoriesSlugs'                  => '',
					'channel'                          => '',
					'primaryCategory'                  => '',
					'primaryAncestorCategoryList'      => '',
					'primaryAncestorCategoryListSlugs' => '',
					'ancestorGoldenCategories'         => '',
					'goldenTaxonomyIdPrimary'          => '',
					'gtaxPrimaryIdsList'               => '',
					'gtaxPrimarySlugsList'             => '',
					'gtaxIdList'                       => '',
					'gtaxList'                         => '',
				]
			);
		}

		$categories = get_category_data( $post->ID );

		return array_merge(
			$data,
			[
				'categories'                       => implode( ', ', map_by_index( 'name', $categories['all_categories'] ?: [] ) ),
				'categoryIds'                      => implode( ', ', map_by_index( 'id', $categories['all_categories'] ?: [] ) ),
				'categoriesSlugs'                  => implode( ', ', map_by_index( 'slug', $categories['all_categories'] ?: [] ) ),
				'channel'                          => $categories['primary_category']['slug'] ?: '',
				'primaryCategory'                  => $categories['primary_category']['name'] ?: '',
				'primaryAncestorCategoryList'      => implode( ', ', map_by_index( 'name', $categories['primary_categories'] ?: [] ) ),
				'primaryAncestorCategoryListSlugs' => implode( ', ', map_by_index( 'slug', $categories['primary_categories'] ?: [] ) ),
				'ancestorGoldenCategories'         => implode( ', ', map_by_index( 'slug', $categories['golden_categories'] ?: [] ) ),
				'goldenTaxonomyIdPrimary'          => (string) $categories['primary_golden_category']['golden_id'] ?: '',
				'gtaxPrimaryIdsList'               => implode( ', ', map_by_index( 'golden_id', $categories['primary_golden_categories'] ?: [] ) ),
				'gtaxPrimarySlugsList'             => implode( ', ', map_by_index( 'slug', $categories['primary_golden_categories'] ?: [] ) ),
				'gtaxIdList'                       => implode( ', ', map_by_index( 'golden_id', $categories['golden_categories'] ?: [] ) ),
				'gtaxList'                         => implode( ', ', map_by_index( 'name', $categories['golden_categories'] ?: [] ) ),
			]
		);
	}
}
