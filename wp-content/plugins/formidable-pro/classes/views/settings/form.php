<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<p class="frm_grid_container">
	<label for="frm_date_format" class="frm4 frm_form_field">
		<?php esc_html_e( 'Date Format', 'formidable-pro' ); ?>
		<?php FrmProAppHelper::tooltip_icon( __( 'Change the format of the date used in the date field.', 'formidable-pro' ) ); ?>
	</label>
	<?php $formats = array_keys( FrmProAppHelper::display_to_datepicker_format() ); ?>
	<select id="frm_date_format" name="frm_date_format" class="frm8 frm_form_field">
		<?php foreach ( $formats as $f ) { ?>
			<option value="<?php echo esc_attr($f); ?>" <?php selected($frmpro_settings->date_format, $f); ?>>
				<?php echo esc_html( $f . ' &nbsp; &nbsp; ' . gmdate( $f ) ); ?>
			</option>
		<?php } ?>
	</select>
</p>

<?php if ( ! class_exists( 'FrmStrpLiteConnectHelper', false ) ) { ?>
	<?php
	/**
	 * Include the global currency setting for backward compatibility when Lite is not up to date.
	 * As of version 6.5 the global currency setting is now in Lite.
	 */
	?>
	<p class="frm_grid_container">
		<label for="frm_currency" class="frm4 frm_form_field frm_help" title="<?php esc_attr_e( 'Select the currency to be used by Formidable globally.', 'formidable-pro' ); ?>">
			<?php esc_html_e( 'Currency', 'formidable-pro' ); ?>
		</label>
		<select id="frm_currency" name="frm_currency" class="frm8 frm_form_field">
			<?php
				$c = empty( $frmpro_settings->currency ) ? 'USD' : strtoupper( $frmpro_settings->currency );
				foreach ( FrmProCurrencyHelper::get_currencies() as $code => $currency ) {
				?>
					<option value="<?php echo esc_attr( $code ); ?>"<?php selected( $c, strtoupper( $code ) ); ?>>
				<?php echo esc_html( $currency['name'] . ' (' . $code . ')' ); ?>
					</option>
			<?php } ?>
		</select>
	</p>
<?php } ?>
