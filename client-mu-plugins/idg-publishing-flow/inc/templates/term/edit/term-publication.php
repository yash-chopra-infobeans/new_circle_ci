<?php
/**
 * Add or edit additional fields for publication taxonomy template.
 */
?>

<tr class="form-field">
	<th><h2>Publishing Settings</h2></th>
	<td></td>
</tr>
<tr class="form-field term-host">
	<th>
		<label for="host">
			<?php esc_html_e( 'Host', 'idg-publishing-flow' ); ?>
		</label>
	</th>
	<td>
		<input name="host" id="host" type="text" value="<?php echo esc_attr( $host_value ); ?>" size="40" aria-required="true" />
		<p class="description"><?php esc_html_e( 'The host without http of the publication.', 'idg-publishing-flow' ); ?></p>
	</td>
</tr>
<tr class="form-field term-host">
	<th>
		<label for="client">
			<?php esc_html_e( 'Client Key', 'idg-publishing-flow' ); ?>
		</label>
	</th>
	<td>
		<input name="client" id="client" type="text" value="<?php echo esc_attr( $client_value ); ?>" size="40" aria-required="true" />
		<p class="description"><?php esc_html_e( 'The Client Key as created by the Delivery Site Authorise settings.', 'idg-publishing-flow' ); ?></p>
	</td>
</tr>
<?php if ( $auth_url && $client_value ) : ?>
<tr class="form-field">
	<th></th>
	<td>
		<?php if ( $access_token_value ) : ?>
			<a href="<?php echo esc_url( $auth_url, 'idg-publishing-flow' ); ?>" class="button button-secondary">Re-Authorize</a>
		<?php else : ?>
			<a href="<?php echo esc_url( $auth_url, 'idg-publishing-flow' ); ?>" class="button button-primary">Authorize</a>
		<?php endif; ?>
		<p class="description"><?php esc_html_e( 'Authorizing a publication will allow the Content Hub to publish and update articles.', 'idg-publishing-flow' ); ?></p>
		<p class="description"><em><?php esc_html_e( 'Note: Articles assigned to unauthoized publications will still retain all other functionality.', 'idg-publishing-flow' ); ?></p>
	</td>
</tr>
<?php endif; ?>
