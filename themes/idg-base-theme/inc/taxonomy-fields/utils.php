<?php
if ( ! function_exists( 'idg_base_theme_register_taxonomy_fields' ) ) {
	/**
	 * Register a taxonomy meta field.
	 *
	 * @param string $taxonomy the taxonomy the field is to be added to.
	 * @param array  $args the settings of meta field.
	 *
	 * @var $args[field_name] string, required (text, textarea, dropdown, color, email, url, image)
	 * @var $args[meta_name] string, required
	 * @var $args[field_name] string, required
	 * @var $args[display_name] string
	 * @var $args[helper_text] string
	 * @var $args[options] array
	 * @var $args[image_preview_size] string (default: `150s-r1:1`)
	 */
	function idg_base_theme_register_taxonomy_fields( $taxonomy, $args ) {
		if ( class_exists( 'IDG_Tax_Form_Field' ) ) {
			$tax_field = new IDG_Tax_Form_Field( $taxonomy, $args );
			$tax_field->add_actions();
		}
	}
}
