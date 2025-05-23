<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
.<?php echo esc_html( $settings['style_class'] ); ?> {
<?php
if ( ! empty( $vars ) && is_callable( 'FrmStylesHelper::output_vars' ) ) {
	FrmStylesHelper::output_vars( $settings, $defaults, $vars );
}

if ( isset( $settings['progress_border_color'] ) && $settings['progress_border_color'] !== $defaults['progress_border_color'] ) {
	?>
	--progress-border-color-b: <?php echo esc_html( FrmStylesHelper::adjust_brightness( $settings['progress_border_color'], -10 ) ); ?>;
<?php } ?>
}

/* Prefix */

<?php if ( empty( $settings['remove_box_shadow'] ) ) { ?>
.<?php echo esc_html( $settings['style_class'] ); ?> .frm_inline_box {
	box-shadow:0 1px 1px rgba(0, 0, 0, 0.075) inset;
}
<?php } ?>

<?php if ( strpos( trim( $settings['field_border_width'] ), '0' ) === 0 ) { ?>
.<?php echo esc_html( $settings['style_class'] ); ?> .frm_inline_box{
	background: var(--bg-color)<?php esc_html( $important ); ?>;
	color:var(--text-color)<?php esc_html( $important ); ?>;
}
<?php } ?>

<?php if ( ! empty( $important ) ) { ?>
.<?php echo esc_html( $settings['style_class'] ); ?> .frm_input_group > input,
.<?php echo esc_html( $settings['style_class'] ); ?> .frm_form_field.frm_total .frm_input_group input,
.<?php echo esc_html( $settings['style_class'] ); ?> .frm_form_field.frm_total_big .frm_input_group input {
	width: 1% !important;
}

.<?php echo esc_html( $settings['style_class'] ); ?> .frm_input_group.frm_with_pre > select,
.<?php echo esc_html( $settings['style_class'] ); ?> .frm_input_group.frm_with_pre > input {
	border-top-left-radius: 0 !important;
	border-bottom-left-radius: 0 !important;
}

.<?php echo esc_html( $settings['style_class'] ); ?> .frm_input_group.frm_with_post > select,
.<?php echo esc_html( $settings['style_class'] ); ?> .frm_input_group.frm_with_post > input {
	border-top-right-radius: 0 !important;
	border-bottom-right-radius: 0 !important;
}

.<?php echo esc_html( $settings['style_class'] ); ?> .frm_total input,
.<?php echo esc_html( $settings['style_class'] ); ?> .frm_total_big input {
	box-shadow: none !important;
}

.frm_form_field.frm_total_big .frm_total_formatted,
.frm_form_field.frm_total_big input[type=text],
.frm_form_field.frm_total_big input[type=number],
.frm_form_field.frm_total_big input,
.frm_form_field.frm_total_big textarea{
	font-size: 32px !important;
	line-height: 44px !important;
}
<?php } ?>

<?php if ( FrmProAppHelper::use_chosen_js() ) { ?>
/* Start Chosen */

.<?php echo esc_html( $settings['style_class'] ); ?> .chosen-container-single .chosen-single{
	padding-top:0 <?php esc_html( $important ); ?>;
	<?php if ( $settings['field_height'] !== 'auto' && $settings['field_height'] != '' ) { ?>
	height:<?php echo esc_html( $settings['field_height'] . $important ); ?>;
	line-height:<?php echo esc_html( $settings['field_height'] . $important ); ?>;
	padding-top: 0<?php echo esc_html( $important ); ?>;
	padding-bottom: 0<?php echo esc_html( $important ); ?>;
	<?php } ?>
}

	<?php if ( is_numeric( $top_margin ) && $pad_unit === 'px' ) { ?>
.<?php echo esc_html( $settings['style_class'] ); ?> .chosen-container-single .chosen-single abbr{
	top:<?php echo esc_attr( 6 + (int) $top_margin ); ?>px <?php esc_html( $important ); ?>;
}

.<?php echo esc_html( $settings['style_class'] ); ?> .chosen-container-single .chosen-single div{
	top:<?php echo esc_html( $top_margin . $pad_unit . $important ); ?>;
}
	<?php } ?>

.<?php echo esc_html( $settings['style_class'] ); ?> .chosen-container-single .chosen-search input[type="text"]{
	height:<?php echo esc_html( $settings['field_height'] === 'auto' || $settings['field_height'] == '' ? 'auto' : $settings['field_height'] ); ?>;
}

.<?php echo esc_html( $settings['style_class'] ); ?> .chosen-container-multi .chosen-choices li.search-field input[type="text"]{
	height:15px<?php echo esc_html( $important ); ?>;
}
/* End Chosen */
<?php } ?>

<?php if ( isset( $settings['progress_color'] ) ) { ?>
/* Progress Bars */

.<?php echo esc_html( $settings['style_class'] ); ?> .frm_page_bar .frm_current_page input[type="button"]{
	background-color: <?php echo esc_html( $settings['progress_bg_color'] . $important ); ?>;
	border-color: <?php echo esc_html( $settings['progress_border_color'] . $important ); ?>;
	opacity:1<?php echo esc_html( $important ); ?>;
}

.<?php echo esc_html( $settings['style_class'] ); ?> .frm_progress_line input,
.<?php echo esc_html( $settings['style_class'] ); ?> .frm_progress_line input:disabled {
	border-color: <?php echo esc_html( $settings['progress_border_color'] . $important ); ?>;
}

.<?php echo esc_html( $settings['style_class'] ); ?> .frm_progress_line.frm_show_lines input {
	border-left-color: <?php echo esc_html( FrmStylesHelper::adjust_brightness( $settings['progress_border_color'], -20 ) . $important ); ?>;
	border-right-color: <?php echo esc_html( FrmStylesHelper::adjust_brightness( $settings['progress_border_color'], -20 ) . $important ); ?>;
	border-left-width: 1px <?php echo esc_html( $important ); ?>;
	border-right-width: 1px <?php echo esc_html( $important ); ?>;
}

.<?php echo esc_html( $settings['style_class'] ); ?> .frm_progress_line li:first-of-type input {
	border-left-color: <?php echo esc_html( $settings['progress_active_bg_color'] . $important ); ?>;
}

.<?php echo esc_html( $settings['style_class'] ); ?> .frm_progress_line li:last-of-type input {
	border-right-color: <?php echo esc_html( $settings['progress_active_bg_color'] . $important ); ?>;
}

.<?php echo esc_html( $settings['style_class'] ); ?> .frm_progress_line li:last-of-type input.frm_page_skip {
	border-right-color: <?php echo esc_html( $settings['progress_border_color'] . $important ); ?>;
}

.<?php echo esc_html( $settings['style_class'] ); ?> .frm_progress_line .frm_current_page input[type="button"] {
	border-left-color: <?php echo esc_html( $settings['progress_border_color'] . $important ); ?>;
}

.<?php echo esc_html( $settings['style_class'] ); ?> .frm_progress_line.frm_show_lines .frm_current_page input[type="button"] {
	border-right-color: <?php echo esc_html( FrmStylesHelper::adjust_brightness( $settings['progress_border_color'], -20 ) . $important ); ?>;
}

.<?php echo esc_html( $settings['style_class'] ); ?> .frm_progress_line input.frm_page_back {
	border-color: <?php echo esc_html( $settings['progress_active_bg_color'] . $important ); ?>;
}

.<?php echo esc_html( $settings['style_class'] ); ?> .frm_progress_line.frm_show_lines input.frm_page_back{
	border-left-color: <?php echo esc_html( $settings['progress_active_bg_color'] . $important ); ?>;
	border-right-color: <?php echo esc_html( FrmStylesHelper::adjust_brightness( $settings['progress_border_color'], -20 ) . $important ); ?>;
}

.<?php echo esc_html( $settings['style_class'] ); ?> .frm_rootline input,
.<?php echo esc_html( $settings['style_class'] ); ?> .frm_rootline input:hover {
	width: <?php echo esc_html( $settings['progress_size'] . $important ); ?>;
	height: <?php echo esc_html( $settings['progress_size'] . $important ); ?>;
	border-radius: <?php echo esc_html( $settings['progress_size'] . $important ); ?>;
	padding: 0<?php echo esc_html( $important ); ?>;
}

.<?php echo esc_html( $settings['style_class'] ); ?> .frm_rootline .frm_current_page input[type="button"],
.<?php echo esc_html( $settings['style_class'] ); ?> .frm_page_bar input.frm_rootline_show_more_btn.active {
	border-color: <?php echo esc_html( FrmStylesHelper::adjust_brightness( $settings['progress_active_bg_color'], -20 ) ); ?> !important;
	background-color: <?php echo esc_html( $settings['progress_active_bg_color'] ); ?> !important;
	color: <?php echo esc_html( $settings['progress_active_color'] ); ?> !important;
}

.<?php echo esc_html( $settings['style_class'] ); ?> .frm_rootline.frm_no_numbers input.frm_rootline_show_more_btn.active {
	border-color: <?php echo esc_html( FrmStylesHelper::adjust_brightness( $settings['progress_active_bg_color'], -20 ) ); ?>;
	background-color: <?php echo esc_html( $settings['progress_active_bg_color'] ); ?>;
	color: <?php echo esc_html( $settings['progress_active_color'] . '!important' ); ?>;
}

.<?php echo esc_html( $settings['style_class'] ); ?> ul.frm_rootline_hidden_steps li:hover .frm_rootline_title {
	color: <?php echo esc_html( $settings['progress_active_bg_color'] ); ?>;
}

<?php } // End if ( isset( $settings['progress_color'] ) ) { ?>

<?php
$thumb_color = $settings['slider_color'];
$thumb       = 'border: 2px solid ' . esc_html( $thumb_color ) . $important . ';';
$thumb      .= 'color:' . esc_html( $settings['progress_active_color'] . $important ) . ';';
?>

/* Start Range slider */
.<?php echo esc_html( $settings['style_class'] ); ?> input[type=range]::-webkit-slider-thumb {
	<?php echo esc_html( $thumb ); ?>
}

.<?php echo esc_html( $settings['style_class'] ); ?> input[type=range]::-moz-range-thumb {
	<?php echo esc_html( $thumb ); ?>
}

.<?php echo esc_html( $settings['style_class'] ); ?> input[type=range]::-ms-thumb {
	<?php echo esc_html( $thumb ); ?>
}
/* End Range Slider */

/* Start other fields */
.<?php echo esc_html( $settings['style_class'] ); ?> input.frm_other_input:not(.frm_other_full){
	width:auto <?php echo esc_html( $important ); ?>;
}

.<?php echo esc_html( $settings['style_class'] ); ?> .frm_checkbox input.frm_other_input:not(.frm_other_full),
.<?php echo esc_html( $settings['style_class'] ); ?> .frm_radio input.frm_other_input:not(.frm_other_full){
	margin-left:5px;
}

.<?php echo esc_html( $settings['style_class'] ); ?> .horizontal_radio input.frm_other_input:not(.frm_other_full):not(.frm_pos_none) {
	display:inline-block<?php echo esc_html( $important ); ?>;
}

.<?php echo esc_html( $settings['style_class'] ); ?> .frm_full input.frm_other_input:not(.frm_other_full){
	margin-left:0 <?php echo esc_html( $important ); ?>;
	margin-top:8px;
}
/* End other */

/* Start Password field */
.<?php echo esc_html( $settings['style_class'] ); ?> span.frm-pass-verified .frmsvg {
	color:<?php echo esc_html( $settings['success_text_color'] . $important ); ?>;
}

.<?php echo esc_html( $settings['style_class'] ); ?> span.frm-pass-req .frmsvg {
	color:<?php echo esc_html( $settings['error_text'] . $important ); ?>;
}
/* End Password field */

/* Sections */
.<?php echo esc_html( $settings['style_class'] ); ?> .frm-show-form  .frm_section_heading h3[class*="frm_pos_"]{
	border<?php echo esc_html( $settings['section_border_loc'] ); ?>:<?php echo esc_html( $settings['section_border_width'] . ' ' . $settings['section_border_style'] . ' ' . $settings['section_border_color'] . $important ); ?>;
}

.<?php echo esc_html( $settings['style_class'] ); ?> h3 .frm_<?php echo esc_html( $settings['collapse_pos'] ); ?>_collapse{
	display:inline;
}
.<?php echo esc_html( $settings['style_class'] ); ?> h3 .frm_<?php echo 'after' === $settings['collapse_pos'] ? 'before' : 'after'; ?>_collapse{
	display:none;
}

.menu-edit #post-body-content .<?php echo esc_html( $settings['style_class'] ); ?> .frm_section_heading h3[class*="frm_pos_"]{
	margin:0;
}

.<?php echo esc_html( $settings['style_class'] ); ?> .frm_section_heading{
	margin-top:<?php echo esc_html( $settings['section_mar_top'] . $important ); ?>;
}

.<?php echo esc_html( $settings['style_class'] ); ?>  .frm-show-form .frm_section_heading .frm_section_spacing,
.menu-edit #post-body-content .<?php echo esc_html( $settings['style_class'] ); ?>  .frm-show-form .frm_section_heading .frm_section_spacing{
	margin-bottom:<?php echo esc_html( $settings['section_mar_bottom'] . $important ); ?>;
}

/* End Sections */

.<?php echo esc_html( $settings['style_class'] ); ?> .frm_single_product_label,
.<?php echo esc_html( $settings['style_class'] ); ?> .frm_total_formatted {
	font-size:<?php echo esc_html( $settings['font_size'] ); ?>;
	color:<?php echo esc_html( $settings['label_color'] . $important ); ?>;
}

<?php if ( ! empty( $bg_image_url ) ) { ?>
	.<?php echo esc_html( $settings['style_class'] ); ?> {
		--bg-image-url: url( '<?php echo esc_html( $bg_image_url ); ?>' );
	}

	<?php if ( ! empty( $bg_image_opacity ) || ( isset( $bg_image_opacity ) && 0.0 === $bg_image_opacity ) ) { ?>
		.<?php echo esc_html( $settings['style_class'] ); ?> .frm_form_fields > fieldset:before {
			opacity: <?php echo esc_html( $bg_image_opacity ); ?>;
		}
	<?php } ?>
<?php } ?>

/* Datepicker */
<?php if ( empty( $defaults['theme_css'] ) || 'ui-lightness' === $defaults['theme_css'] ) : ?>
.<?php echo esc_html( $settings['style_class'] ); ?> .ui-datepicker-title > select {
	color: <?php echo esc_html( $settings['text_color'] ); ?>;
	color: var(--text-color)<?php echo esc_html( $important ); ?>;
	background-color: var(--bg-color)<?php echo esc_html( $important ); ?>;
}
.<?php echo esc_html( $settings['style_class'] ); ?> .ui-datepicker-month,
.<?php echo esc_html( $settings['style_class'] ); ?> .ui-datepicker-year {
	color: var(--text-color)<?php echo esc_html( $important ); ?>;
}
.<?php echo esc_html( $settings['style_class'] ); ?> span.ui-datepicker-month,
.<?php echo esc_html( $settings['style_class'] ); ?> span.ui-datepicker-year {
	color: var(--date-head-color)<?php echo esc_html( $important ); ?>;
}
.<?php echo esc_html( $settings['style_class'] ); ?> .ui-widget-header,
.<?php echo esc_html( $settings['style_class'] ); ?> .ui-datepicker-header {
	color: var(--date-head-color) !important;
	background-color: var(--date-head-bg-color) !important;
}
.<?php echo esc_html( $settings['style_class'] ); ?> .ui-datepicker td, /* Sample form selector */
.<?php echo esc_html( $settings['style_class'] ); ?>.ui-datepicker td {
	border: 0;
	border-radius: var(--border-radius);
	overflow: hidden;
}
.<?php echo esc_html( $settings['style_class'] ); ?> td.ui-datepicker-today {
	background-color: var(--date-band-color) !important;
}
.<?php echo esc_html( $settings['style_class'] ); ?> td.ui-datepicker-today > a {
	color: var(--date-head-color)<?php echo esc_html( $important ); ?>;
}
.<?php echo esc_html( $settings['style_class'] ); ?> td.ui-datepicker-current-day,
.<?php echo esc_html( $settings['style_class'] ); ?> td.ui-datepicker-current-day .ui-state-hover {
	background-color: var(--border-color-active) !important;
}
.<?php echo esc_html( $settings['style_class'] ); ?> td.ui-datepicker-current-day .ui-state-default:not(.ui-state-hover) {
	color: #fff;
}
.<?php echo esc_html( $settings['style_class'] ); ?> td:not(.ui-datepicker-current-day) .ui-state-hover {
	color: var(--text-color)<?php echo esc_html( $important ); ?>;
	background: #F2F4F7 !important;
}
<?php endif; // end if empty( $defaults['theme_css'] ) || 'ui-lightness' === $defaults['theme_css'] ?>
/* End Datepicker */

/* Submit Buttons */
<?php if ( empty( $settings['submit_style'] ) ) : ?>
.<?php echo esc_html( $settings['style_class'] ); ?> input[type=submit][disabled],
.<?php echo esc_html( $settings['style_class'] ); ?> .frm_submit input[type=button][disabled],
.<?php echo esc_html( $settings['style_class'] ); ?> .frm_submit button[disabled] {
	opacity: 0.5;
	cursor: not-allowed;
}
.<?php echo esc_html( $settings['style_class'] ); ?> .frm_loading_prev button.frm_prev_page,
.<?php echo esc_html( $settings['style_class'] ); ?> .frm_loading_form button.frm_button_submit {
	opacity: 1;
}
<?php endif; ?>
/* End Submit Buttons */
