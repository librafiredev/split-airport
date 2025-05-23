<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<p class="frm6 frm_form_field">
	<label>
		<?php esc_html_e( 'Minimum Repeater Rows', 'formidable-pro' ); ?>
	</label>
	<input type="number" class="frm_repeat_min" name="field_options[repeat_min_<?php echo absint( $field['id'] ); ?>]" value="<?php echo esc_attr( $field['repeat_min'] ); ?>" size="3" min="0" step="1" max="999" />
</p>
<p class="frm6 frm_form_field">
	<label>
		<?php esc_html_e( 'Maximum Repeater Rows', 'formidable-pro' ); ?>
		<?php FrmProAppHelper::tooltip_icon( __( 'The maximum number of times the end user is allowed to duplicate this section of fields in one entry', 'formidable-pro' ), array( 'data-placement' => 'right' ) ); ?>
	</label>
	<input type="number" class="frm_repeat_limit" name="field_options[repeat_limit_<?php echo absint( $field['id'] ); ?>]" value="<?php echo esc_attr( $field['repeat_limit'] ); ?>" size="3" min="2" step="1" max="999" />
</p>
