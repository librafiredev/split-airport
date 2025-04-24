<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<tr>
    <td>
		<label for="ajax_submit">
			<input type="checkbox" name="options[ajax_submit]" id="ajax_submit" value="1" <?php checked( $values['ajax_submit'], 1 ); ?> />
			<?php esc_html_e( 'Submit this form with AJAX', 'formidable-pro' ); ?>
			<?php FrmProAppHelper::tooltip_icon( __( 'Submit the form without refreshing the page.', 'formidable-pro' ) ); ?>
		</label>
    </td>
</tr>
