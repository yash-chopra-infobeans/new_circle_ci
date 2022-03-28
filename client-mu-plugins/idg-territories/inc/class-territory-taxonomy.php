<?php

namespace IDG\Territories;

use WP_Error;
use Rinvex\Country\CountryLoader;
use Rinvex\Country\CountryLoaderException;
use IDG\Territories\Territory_Loader;

/**
 * Management of the territory taxonomy
 */
class Territory_Taxonomy {
	const SCRIPT_NAME = 'idg-territories-script';

	const STYLE_NAME = 'idg-territories-style';

	const DEFAULT_CURRENCIES = [
		'USD' => [
			'iso_4217_code'       => 'USD',
			'iso_4217_numeric'    => 840,
			'iso_4217_name'       => 'US Dollar',
			'iso_4217_minor_unit' => 2,
		],
	];

	const TAXONOMY_SLUG = 'territory';

	/**
	 * Register actions|filters needed for territory taxonomy functionality.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'register_territory_taxonomy' ], 0 );
		add_action( self::TAXONOMY_SLUG . '_pre_add_form', [ $this, 'enqueue_assets' ] );
		add_action( self::TAXONOMY_SLUG . '_pre_edit_form', [ $this, 'enqueue_assets' ] );
		add_action( self::TAXONOMY_SLUG . '_add_form_fields', [ $this, 'edit_territory_template' ], 10, 2 );
		add_action( self::TAXONOMY_SLUG . '_edit_form_fields', [ $this, 'edit_territory_template' ], 10, 2 );
		add_action( 'create_' . self::TAXONOMY_SLUG, [ $this, 'update_territory_meta' ], 10, 2 );
		add_action( 'edited_' . self::TAXONOMY_SLUG, [ $this, 'update_territory_meta' ], 10, 2 );
		add_filter( 'manage_edit-' . self::TAXONOMY_SLUG . '_columns', [ $this, 'manage_columns' ] );
		add_filter( 'manage_' . self::TAXONOMY_SLUG . '_custom_column', [ $this, 'add_column_content' ], 10, 3 );
		add_filter( 'pre_insert_term', [ $this, 'validate' ], 20, 2 );
		add_filter( self::TAXONOMY_SLUG . '_row_actions', [ $this, 'disable_quick_edit' ] );
	}

	/**
	 * Register the territory taxonomy.
	 *
	 * @return void
	 */
	public function register_territory_taxonomy() : void {
		register_taxonomy(
			self::TAXONOMY_SLUG,
			[ 'product', 'post' ],
			[
				'labels'            => create_territory_taxonomy_labels( 'Territories', 'Territory' ),
				'hierarchical'      => false,
				'query_var'         => true,
				'rewrite'           => true,
				'show_ui'           => true,
				'show_in_nav_menus' => true,
				'show_tagcloud'     => false,
				'show_admin_column' => false,
				'public'            => false,
				'show_in_menu'      => true,
				'show_in_rest'      => false,
				'capabilities'      => [
					'manage_terms' => 'manage_territory',
					'edit_terms'   => 'edit_territory',
					'delete_terms' => 'delete_territory',
					'assign_terms' => 'assign_territory',
				],
				'show_in_rest'      => true,
			]
		);
	}

	/**
	 * Render additional term fields.
	 *
	 * @param object|string $term - The term.
	 *
	 * @return void
	 */
	public function edit_territory_template( $term ) : void {
		$countries = countries( true );

		usort(
			$countries,
			function ( $a, $b ) {
				return $a['name']['common'] > $b['name']['common'];
			}
		);

		require_once IDG_TERRITORIES_DIR . '/inc/templates/edit-territory.php';
	}

	/**
	 * Remove the description and count columns, add custom columns.
	 *
	 * @param array $columns - The unmodified columns.
	 *
	 * @return array
	 */
	public function manage_columns( array $columns ) : array {
		if ( isset( $columns['description'] ) ) {
			unset( $columns['description'] );
		}

		if ( isset( $columns['posts'] ) ) {
			unset( $columns['posts'] );
		}

		$columns['country']  = __( 'Country', 'idg' );
		$columns['currency'] = __( 'Currency', 'idg' );

		return $columns;
	}

	/**
	 * Update custom columns with country data.
	 *
	 * @param string         $content - The column content.
	 * @param string         $column_name - The column name.
	 * @param string|integer $term_id - The term id.
	 *
	 * @return string
	 */
	public function add_column_content( string $content, string $column_name, $term_id ) : string {
		$territory = Territory_Loader::territory( get_term( $term_id ) );

		if ( 'currency' === $column_name ) {
			$currency = $territory->get_default_currency();
			$content .= "{$currency['iso_4217_name']}";
		}

		if ( 'country' === $column_name ) {
			$content .= "{$territory->getOfficialName()} {$territory->getEmoji()}";
		}

		return $content;
	}

	/**
	 * Update territory meta.
	 *
	 * @param string|integer $term_id - The term id.
	 */
	public function update_territory_meta( $term_id ) {
		$currency = filter_input( INPUT_POST, 'currency', FILTER_SANITIZE_STRING );

		if ( $currency ) {
			update_term_meta( $term_id, 'default_currency', $currency );
		}
	}

	/**
	 * Disable quick edit to prevent editing slug.
	 *
	 * @param array $actions - The unmodified actions.
	 * @return array
	 */
	public function disable_quick_edit( $actions ) {
		unset( $actions['inline hide-if-no-js'] );
		return $actions;
	}

	/**
	 * Ensure the slug is a valid country code before adding.
	 *
	 * @param object $term - The term.
	 * @param string $taxonomy - The taxonomy.
	 */
	public function validate( $term, $taxonomy ) {
		if ( 'territory' !== $taxonomy ) {
			return $term;
		}

		if ( constant( 'VIP_GO_APP_ENVIRONMENT' ) === 'cypress' ) {
			return $term;
		}

		$country_code = filter_input( INPUT_POST, 'slug', FILTER_SANITIZE_STRING );

		try {
			CountryLoader::country( $country_code );
		} catch ( CountryLoaderException $e ) {
			return new WP_Error( 'invalid_term', __( 'Term slug is not a valid country code.', 'idg' ) );
		}

		return $term;
	}

	/**
	 * Enqueue assets to be loaded only when adding or updating a territory.
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		$plugin_name = basename( IDG_TERRITORIES_DIR );
		$plugin_dir  = WPCOM_VIP_CLIENT_MU_PLUGIN_DIR . '/' . $plugin_name;

		wp_enqueue_script(
			self::SCRIPT_NAME,
			plugins_url( "{$plugin_name}/dist/scripts/" . IDG_TERRITORIES_EDIT_TERRITORY_JS, $plugin_dir ),
			[ 'wp-i18n', 'wp-element', 'wp-components', 'wp-api-fetch' ],
			filemtime( IDG_TERRITORIES_DIR . '/dist/scripts/' . IDG_TERRITORIES_EDIT_TERRITORY_JS ),
			true
		);

		wp_enqueue_style(
			self::STYLE_NAME,
			plugins_url( "{$plugin_name}/dist/styles/" . IDG_TERRITORIES_EDIT_TERRITORY_CSS, $plugin_dir ),
			[],
			filemtime( IDG_TERRITORIES_DIR . '/dist/styles/' . IDG_TERRITORIES_EDIT_TERRITORY_CSS )
		);
	}
}
