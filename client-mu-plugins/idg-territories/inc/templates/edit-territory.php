<?php
/**
 * Territory taxonomy template.
 *
 * Add or edit additional fields for territory taxonomy
 *
 * @see src/entrypoints/edit-territory.js
 * @see src/styles/edit-territory.scss
 */

use IDG\Territories\Territory_Taxonomy;

?>

<div class="form-field term-wrap-country">
	<label for="country-selector">
		<?php echo esc_html( __( 'Choose a Country', 'idg' ) ); ?>
	</label>
	<select class="country-selector" id="country-selector" name="country">
		<?php
		foreach ( $countries as $country ) {
			$country_code = strtolower( $country['iso_3166_1_alpha2'] );
			//phpcs:ignore
			$is_disabled = term_exists( $country['iso_3166_1_alpha2'], 'territory' ) && $country_code !== $term->slug;
			$is_selected = ( isset( $term->slug ) && $country_code === $term->slug );

			printf(
				'<option value="%1$s" %3$s %4$s>%2$s</option>',
				esc_attr( $country['iso_3166_1_alpha2'] ),
				esc_attr( $country['name']['common'] ),
				esc_attr( $is_disabled ? 'disabled' : '' ),
				esc_attr( $is_selected ? 'selected="selected"' : '' )
			);
		}
		?>
	</select>

	<?php foreach ( $countries as $country_key => $country ) : ?>
		<fieldset
			class="currency-selector-wrapper <?php echo esc_attr( $country['iso_3166_1_alpha2'] ); ?>"
			style="display: none;"
			aria-hidden="true"
			role="radiogroup"
		>
			<legend>
				<?php echo esc_html( __( 'Currencies for ', 'idg' ) . $country['name']['common'] ); ?>
			</legend>
			<?php

			$currencies = empty( $country['currency'] )
				? Territory_Taxonomy::DEFAULT_CURRENCIES
				: $country['currency'];

			foreach ( $currencies as $currency_code => $currency ) {
				printf(
					'<div class="currency-input-wrapper">
						<input type="radio" name="currency" value="%1$s" />
						<label for="%1$s">%2$s</label>
					</div>',
					esc_attr( $currency_code ),
					esc_html( $currency['iso_4217_name'] )
				);
			}
			?>
		</fieldset>
	<?php endforeach; ?>

	<p><?php echo esc_html( __( 'Select a country and currency to associate to this territory.', 'idg' ) ); ?></p>
</div>
