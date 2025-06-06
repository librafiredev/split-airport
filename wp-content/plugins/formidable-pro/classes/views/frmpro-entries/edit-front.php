<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm_forms<?php echo FrmFormsHelper::get_form_style_class( $values ); ?>" id="frm_form_<?php echo (int) $form->id; ?>_container" <?php echo apply_filters( 'frm_form_div_attributes', '', $form ); ?>>
<?php
require FrmAppHelper::plugin_path() . '/classes/views/frm-entries/errors.php';

FrmProFormsHelper::maybe_init_antispam( $form->id );

if ( ! empty( $show_form ) ) {
	if ( ! empty( $errors ) && empty( $message ) ) {
	?>
<script type="text/javascript">window.onload=function(){location.href="#frm_errors";}</script>
<?php
	} elseif ( ! empty( $jump_to_form ) || ! empty( $message ) ) {
        FrmFormsHelper::get_scroll_js( $form->id );
    }
?>
<form enctype="multipart/form-data" method="post" class="frm-show-form <?php do_action('frm_form_classes', $form); ?>" id="form_<?php echo esc_attr( $form->form_key ); ?>" <?php echo apply_filters( 'frm_form_div_attributes', '', $form ); ?>>
<?php
    $form_action = 'update';
	require FrmAppHelper::plugin_path() . '/classes/views/frm-entries/form.php';
?>
</form>
<?php
}
?>
</div>
