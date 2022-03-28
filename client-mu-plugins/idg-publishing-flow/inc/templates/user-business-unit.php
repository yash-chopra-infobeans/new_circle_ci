<tr class="user-publication-header-wrap">
	<th colspan="2"><h2>Publication Options</h2></th>
</tr>
<tr class="user-business-unit-wrap">
	<th>
		<label for="business_unit">Business Unit</label>
	</th>
	<td>
	<?php if ( $business_unit_terms ) : ?>
		<select name="business_unit">
			<option value="">Unassigned</option>
			<?php foreach ( $business_unit_terms as $bu_term ) : ?>
				<option
					value="<?php echo esc_attr( $bu_term->term_id ); ?>"
					<?php echo $business_unit_id === $bu_term->term_id ? esc_attr( 'selected' ) : ''; ?>
				>
					<?php echo esc_html( $bu_term->name ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php
	else :
		esc_html( $business_unit_name );
	endif;
	?>
	</td>
</tr>
<?php do_action( 'idg_publishing_flow_publication_options' ); ?>
