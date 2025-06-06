<?php
global $sitepress;

$xliff_newlines = (int) $sitepress->get_setting( 'xliff_newlines' );
if ( ! $xliff_newlines ) {
	$xliff_newlines = WPML_XLIFF_TM_NEWLINES_ORIGINAL;
}

$new_line_labels = array(
	WPML_XLIFF_TM_NEWLINES_ORIGINAL => __(
		'Do nothing - all new line characters will stay untouched.',
		'wpml-translation-management'
	),
	WPML_XLIFF_TM_NEWLINES_REPLACE  => sprintf(
		__( 'All new lines should be replaced by HTML element %s. Use this option if translation tool used by translator does not support new lines characters (for example Virtaal software)', 'wpml-translation-management' ),
		'<br class="xliff-newline" />'
	),
);
?>

<div class="wpml-section" id="ml-content-setup-sec-5-1">

	<div class="wpml-section-header">
		<h3><?php esc_html_e( 'XLIFF file options', 'wpml-translation-management' ); ?></h3>
	</div>

	<div class="wpml-section-content">

		<form name="icl_xliff_options_form" id="icl_xliff_options_form" action="">
			<?php wp_nonce_field( 'icl_xliff_options_form_nonce', '_icl_nonce' ); ?>

			<div class="wpml-section-content-inner">

				<h4><?php esc_html_e( 'XLIFF version', 'wpml-translation-management' ); ?></h4>

				<p>
					<?php esc_html_e( 'Choose default format for XLIFF file:', 'wpml-translation-management' ); ?>

					<select name="icl_xliff_version">
						<option value="false"><?php echo esc_html__( 'Please choose', 'wpml-translation-management' ); ?></option>
						<?php
						$xliff_instance           = setup_xliff_frontend();
						$available_xliff_versions = $xliff_instance->get_available_xliff_versions();
						foreach ( $available_xliff_versions as $value => $version ) {
							$selected = '';
							if ( $sitepress->get_setting( 'tm_xliff_version' ) === $value ) {
								$selected = 'selected="selected"';
							}
							$version_label = sprintf( __( 'XLIFF %s', 'wpml-translation-management' ), $version );
							echo sprintf( '<option value="%1$s" %2$s >%3$s</option>', esc_attr( $value ), $selected, esc_html( $version_label ) );
						}
						?>
					</select>
				</p>
			</div>

			<div class="wpml-section-content-inner">

				<h4><?php esc_html_e( 'New lines character', 'wpml-translation-management' ); ?></h4>
				<p>
					<?php esc_html_e( 'How new lines characters in XLIFF files should be handled?', 'wpml-translation-management' ); ?>
				</p>

				<?php foreach ( $new_line_labels as $mode => $label ) { ?>
					<p>
						<label>
							<input class="wpml-radio-native" type="radio" name="icl_xliff_newlines"
								   value="<?php echo esc_attr( (string) $mode ); ?>"<?php checked( $xliff_newlines, $mode ); ?>/>
							<?php echo esc_html( $label ); ?>
						</label>
					</p>
				<?php } ?>

			</div>
			<p class="buttons-wrap">
				<span class="icl_ajx_response" id="icl_ajx_response"></span>
				<input type="submit" class="button-primary wpml-button base-btn"
					   value="<?php esc_attr_e( 'Save', 'wpml-translation-management' ); ?>"/>
			</p>
		</form>
	</div> <!-- .wpml-section-content -->

</div>


