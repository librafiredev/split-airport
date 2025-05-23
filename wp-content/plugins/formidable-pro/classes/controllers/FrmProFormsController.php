<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmProFormsController {

	public static function admin_js() {
		$version = FrmProDb::$plug_version;
		$action  = FrmAppHelper::simple_get( 'frm_action', 'sanitize_title' );

		wp_register_style( 'formidable-pro-form-settings', FrmProAppHelper::plugin_url() . '/css/settings/form-settings.css', array(), $version );

		$frm_settings = FrmAppHelper::get_settings();
		$unread_count = is_callable( 'FrmEntriesHelper::get_visible_unread_inbox_count' ) ? FrmEntriesHelper::get_visible_unread_inbox_count() : 0;

		add_filter( 'manage_' . sanitize_title( $frm_settings->menu ) . ( $unread_count ? '-' . $unread_count : '' ) . '_page_formidable-entries_columns', 'FrmProEntriesController::manage_columns', 25 );

		wp_register_style( 'formidable-dropzone', FrmProAppHelper::plugin_url() . '/css/dropzone.css', array(), $version );
		wp_register_style( 'formidable-pro-fields', admin_url( 'admin-ajax.php?action=pro_fields_css' ), array(), $version );

		if ( FrmAppHelper::is_admin_page() ) {
			wp_enqueue_style( 'formidable-pro-fields' );
		}

		if ( FrmAppHelper::is_admin_page( 'formidable' ) ) {
			if ( 'edit' === $action ) {
				// For image selector in form builder.
				wp_enqueue_media();
			} elseif ( 'settings' === $action ) {
				wp_enqueue_style( 'formidable-pro-form-settings' );
			}
		}

		if ( ! FrmAppHelper::is_admin_page( 'formidable-entries' ) ) {
			return;
		}

		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_style( 'formidable-pro-fields' );

		$theme_css = FrmStylesController::get_style_val( 'theme_css' );
		if ( $theme_css == -1 ) {
			return;
		}

		wp_enqueue_style( $theme_css, FrmProStylesController::jquery_css_url( $theme_css ), array(), FrmAppHelper::plugin_version() );
	}

	/**
	 * @return void
	 */
	public static function enqueue_footer_js() {
		global $frm_vars, $frm_input_masks;

		if ( empty( $frm_vars['forms_loaded'] ) ) {
			return;
		}

		FrmProAppController::register_scripts();

		if ( ! FrmAppHelper::doing_ajax() ) {
			wp_enqueue_script( 'intl-tel-input' );
			wp_enqueue_script( 'intl-tel-input-utils' );
			wp_enqueue_script( 'formidable' );
			wp_enqueue_script( 'formidablepro' );
			FrmAppHelper::localize_script( 'front' );
		}

		if ( ! empty( $frm_vars['tinymce_loaded'] ) ) {
			_WP_Editors::enqueue_scripts();
		}

		if ( ! empty( $frm_vars['datepicker_loaded'] ) ) {
			if ( is_array( $frm_vars['datepicker_loaded'] ) ) {
				foreach ( $frm_vars['datepicker_loaded'] as $fid => $o ) {
					if ( ! $o ) {
						unset( $frm_vars['datepicker_loaded'][ $fid ] );
					}
					unset( $fid, $o );
				}
			}

			if ( ! empty( $frm_vars['datepicker_loaded'] ) ) {
				wp_enqueue_script( 'jquery-ui-datepicker' );
				FrmProStylesController::enqueue_jquery_css();
			}
		}

		if ( ! empty( $frm_vars['autocomplete_loaded'] ) ) {
			if ( FrmProAppHelper::use_chosen_js() ) {
				wp_enqueue_script( 'jquery-chosen' );
			} else {
				wp_enqueue_script( 'slimselect' );
			}
		}

		if ( ! empty( $frm_vars['dropzone_loaded'] ) ) {
			wp_enqueue_script( 'dropzone' );
		}

		$frm_input_masks = apply_filters( 'frm_input_masks', $frm_input_masks, $frm_vars['forms_loaded'] );
		foreach ( (array) $frm_input_masks as $fid => $o ) {
			if ( ! $o ) {
				unset( $frm_input_masks[ $fid ] );
			}
			unset( $fid, $o );
		}

		if ( ! empty( $frm_input_masks ) ) {
			wp_enqueue_script( 'jquery-maskedinput' );
		}

		if ( ! empty( $frm_vars['google_graphs'] ) ) {
			wp_enqueue_script( 'google_jsapi', 'https://www.gstatic.com/charts/loader.js', array(), FrmAppHelper::plugin_version() );
		}
	}

	/**
	 * @return void
	 */
	public static function footer_js() {
		global $frm_vars;

		$frm_vars['footer_loaded'] = true;

		if ( empty( $frm_vars['forms_loaded'] ) ) {
			return;
		}

		include FrmProAppHelper::plugin_path() . '/classes/views/frmpro-entries/footer_js.php';

		/**
		 * Add custom scripts after the form scripts are done loading
		 *
		 * @since 2.0.6
		 *
		 * @param array $forms_loaded
		 */
		do_action( 'frm_footer_scripts', $frm_vars['forms_loaded'] );
	}

	/**
	 * @param string $keep
	 * @return void
	 */
	public static function print_ajax_scripts( $keep = '' ) {
		self::enqueue_footer_js();

		if ( $keep !== 'all' ) {
			if ( $keep === 'none' ) {
				$keep_scripts = array();
				$keep_styles  = array();
			} else {
				$keep_scripts = array(
					'recaptcha-api',
					'captcha-api',
					'jquery-chosen',
					'slimselect',
					'google_jsapi',
					'dropzone',
					'jquery-maskedinput',
				);
				$keep_styles  = array(
					'dashicons',
					'jquery-theme',
				);

				if ( is_array( $keep ) ) {
					$keep_scripts = array_merge( $keep_scripts, $keep );
				}
			}

			global $wp_scripts, $wp_styles;
			$keep_scripts       = apply_filters( 'frm_ajax_load_scripts', $keep_scripts );
			$registered_scripts = (array) $wp_scripts->registered;
			$registered_scripts = array_diff( array_keys( $registered_scripts ), $keep_scripts );
			self::mark_scripts_as_loaded( $registered_scripts );

			$keep_styles       = apply_filters( 'frm_ajax_load_styles', $keep_styles );
			$registered_styles = (array) $wp_styles->registered;
			$registered_styles = array_diff( array_keys( $registered_styles ), $keep_styles );
			if ( ! empty( $registered_styles ) ) {
				$wp_styles->done = array_merge( $wp_styles->done, $registered_styles );
			}
		}

		wp_print_footer_scripts();
	}

	/**
	 * Used during ajax when we know jQuery has already been loaded
	 * Used when a form is loaded for edit-in-place
	 *
	 * @since 2.05
	 */
	public static function mark_jquery_as_loaded() {
		$mark_complete = array( 'jquery-core', 'jquery-migrate', 'jquery' );
		self::mark_scripts_as_loaded( $mark_complete );
	}

	/**
	 * @since 2.05
	 *
	 * @param array $scripts
	 */
	private static function mark_scripts_as_loaded( $scripts ) {
		global $wp_scripts;
		$wp_scripts->done = array_merge( $wp_scripts->done, $scripts );
	}

	/**
	 * Check if the form is loaded after the wp_footer hook.
	 * If it is, we'll need to make sure the scripts are loaded.
	 *
	 * @return void
	 */
	public static function after_footer_loaded() {
		global $frm_vars;

		if ( ! isset( $frm_vars['footer_loaded'] ) || ! $frm_vars['footer_loaded'] ) {
			wp_enqueue_script( 'formidablepro' );
			return;
		}

		self::enqueue_footer_js();

		_wp_footer_scripts();

		self::footer_js();
	}

	/**
	 * Used for hiding the form on page load.
	 *
	 * @since 2.3
	 *
	 * @return void
	 */
	public static function head() {
		echo '<script>document.documentElement.className += " js";</script>' . "\r\n";
	}

	/**
	 * @since 4.0
	 *
	 * @param array $sections
	 * @return array
	 */
	public static function form_settings_sections( $sections ) {
		$sections['permissions'] = array(
			'function' => array( __CLASS__, 'add_form_options' ),
			'name'     => isset( $sections['permissions'] ) ? $sections['permissions']['name'] : __( 'Form Permissions', 'formidable' ),
			'icon'     => isset( $sections['permissions'] ) ? $sections['permissions']['icon'] : 'frm_icon_font frm_lock_icon',
			'anchor'   => 'permissions_settings',
		);

		$sections['scheduling'] = array(
			'function' => array( __CLASS__, 'add_form_status_options' ),
			'name'     => isset( $sections['scheduling'] ) ? $sections['scheduling']['name'] : __( 'Form Scheduling', 'formidable' ),
			'icon'     => isset( $sections['scheduling'] ) ? $sections['scheduling']['icon'] : 'frm_icon_font frm_calendar_icon',
			'anchor'   => 'scheduling_settings',
		);

		return $sections;
	}

	/**
	 * @param array $values
	 * @return void
	 */
	public static function add_form_options( $values ) {
		global $frm_vars;

		$post_types     = FrmProAppHelper::get_custom_post_types();
		$has_file_field = FrmField::get_all_types_in_form( $values['id'], 'file', 2, 'include' );
		$email_fields   = FrmField::get_all_types_in_form( $values['id'], 'email' );
		$values         = self::prepare_single_entry_settings( $values, $email_fields );

		require FrmProAppHelper::plugin_path() . '/classes/views/frmpro-forms/add_form_options.php';
	}

	/**
	 * Conditionally outputs option for Logged-out editing education in roles dropdowns in Form Permission settings.
	 *
	 * @since 6.9.1
	 *
	 * @return void
	 */
	public static function maybe_output_logged_out_editing_education_option() {
		if ( class_exists( 'FrmAbandonmentHooksController' ) ) {
			return;
		}
		$params  = array(
			'data-oneclick' => wp_json_encode( FrmProAddonsController::install_link( 'abandonment' ) ),
			'data-upgrade'  => __( 'Abandonment roles', 'formidable-pro' ),
		);
		?>
		<option class="frm_disabled_option" <?php FrmAppHelper::array_to_html_params( $params, true ); ?>><?php esc_html_e( 'Logged-out Users', 'formidable-pro' ); ?></option>
		<?php
	}

	/**
	 * Make sure single_entry_type is always an array.
	 * Also inject a unique email setting if there is a unique email field in the form.
	 * And if no types are selected, disable the single entry setting on load.
	 *
	 * @since 6.8.3
	 *
	 * @param array           $values
	 * @param array<stdClass> $email_fields
	 * @return array
	 */
	private static function prepare_single_entry_settings( $values, $email_fields ) {
		$values['single_entry_type'] = (array) $values['single_entry_type'];
		$values                      = self::maybe_map_unique_email_field_setting( $values, $email_fields );

		// If no single entry types are selected, disable the single entry checkbox on load.
		if ( $values['single_entry'] && ! $values['single_entry_type'] ) {
			$values['single_entry'] = 0;
		}

		return $values;
	}

	/**
	 * @since 6.8.3
	 *
	 * @param array           $values
	 * @param array<stdClass> $email_fields
	 * @return array
	 */
	private static function maybe_map_unique_email_field_setting( $values, $email_fields ) {
		if ( FrmProFormsHelper::check_single_entry_type( $values, 'email' ) ) {
			return $values;
		}

		$unique_email_field_id = self::get_unique_email_field_id( $email_fields );
		if ( ! $unique_email_field_id ) {
			return $values;
		}

		if ( ! $values['single_entry'] ) {
			$values['single_entry']      = 1;
			$values['single_entry_type'] = array();
		}

		if ( ! in_array( 'email', $values['single_entry_type'], true ) ) {
			$values['single_entry_type'][] = 'email';
		}

		$values['unique_email_id'] = $unique_email_field_id;
		return $values;
	}

	/**
	 * Check an array of email fields to see if any are unique.
	 *
	 * @since 6.8.3
	 *
	 * @param array<stdClass> $email_fields
	 * @return false|int ID of the unique email field. False if there is no unique email field in the form.
	 */
	private static function get_unique_email_field_id( $email_fields ) {
		foreach ( $email_fields as $field ) {
			if ( ! empty( $field->field_options['unique'] ) ) {
				return (int) $field->id;
			}
		}
		return false;
	}

	public static function add_form_page_options( $values ) {
		$page_fields = FrmField::get_all_types_in_form( $values['id'], 'break' );
		if ( $page_fields ) {
			$hide_rootline_class       = empty( $values['rootline'] ) ? 'frm_hidden' : '';
			$hide_rootline_title_class = empty( $values['rootline_titles_on'] ) ? 'frm_hidden' : '';
			$i                         = 1;
			require FrmProAppHelper::plugin_path() . '/classes/views/frmpro-forms/form_page_options.php';
		}

		// Print the hidden inputs for backward compatibility.
		self::add_form_button_options( $values );
		self::add_form_style_tab_options( $values );
	}

	/**
	 * @param array $values
	 * @return void
	 */
	public static function add_form_ajax_options( $values ) {
		if ( ! FrmProFormsHelper::lite_supports_ajax_submit() ) {
			// Only show the option when Lite doesn't support AJAX submit.
			require FrmProAppHelper::plugin_path() . '/classes/views/frmpro-forms/add_form_ajax_options.php';
		}
	}

	/**
	 * Remove the noallow class on pro fields
	 *
	 * @param string $class_name
	 * @return string
	 */
	public static function noallow_class( $class_name ) {
		if ( FrmProAddonsController::is_expired_outside_grace_period() ) {
			return $class_name . ' frm_show_upgrade frm_show_expired_modal';
		}
		return '';
	}

	/**
	 * Use a different name on the 'Field Label' setting for some field types.
	 *
	 * @since 4.0
	 * @return string
	 */
	public static function builder_field_label( $label, $field ) {
		if ( $field['type'] === 'break' ) {
			$label = __( 'Button Label', 'formidable-pro' );
		}
		return $label;
	}

	/**
	 * @param array $values
	 * @return void
	 */
	public static function add_form_button_options( $values ) {
		FrmProFormsHelper::array_to_hidden_inputs(
			array(
				'submit_conditions' => $values['submit_conditions'],
			),
			'options'
		);
	}

	/**
	 * @since 4.05
	 *
	 * @param array $values
	 * @return void
	 */
	public static function add_form_style_tab_options( $values ) {
		FrmProFormsHelper::array_to_hidden_inputs(
			array(
				'transition' => $values['transition'],
			),
			'options'
		);
	}

	/**
	 * @since 3.04
	 *
	 * @param array $values
	 * @return void
	 */
	public static function add_form_status_options( $values ) {
		FrmProStylesController::enqueue_jquery_css();
		wp_enqueue_script( 'jquery-ui-datepicker' );
		$values['open_date']  = empty( $values['open_date'] ) ? '' : gmdate( 'Y-m-d H:i', strtotime( $values['open_date'] ) );
		$values['close_date'] = empty( $values['close_date'] ) ? '' : gmdate( 'Y-m-d H:i', strtotime( $values['close_date'] ) );

		require FrmProAppHelper::plugin_path() . '/classes/views/frmpro-forms/add_form_status_options.php';
	}

	/**
	 * @since 4.0
	 * @return false The lite version should not show an upsell.
	 */
	public static function smart_values_box() {
		self::instructions();
		return false;
	}

	/**
	 * @return void
	 */
	public static function instructions() {
		$tags = array(
			'date'                   => __( 'Current Date', 'formidable-pro' ),
			'time'                   => __( 'Current Time', 'formidable-pro' ),
			'email'                  => __( 'Email', 'formidable-pro' ),
			'login'                  => __( 'Login', 'formidable-pro' ),
			'display_name'           => __( 'Display Name', 'formidable-pro' ),
			'first_name'             => __( 'First Name', 'formidable-pro' ),
			'last_name'              => __( 'Last Name', 'formidable-pro' ),
			'user_id'                => __( 'User ID', 'formidable-pro' ),
			'user_meta key=whatever' => __( 'User Meta', 'formidable-pro' ),
			'user_role'              => __( 'User Role', 'formidable-pro' ),
			'post_id'                => __( 'Post ID', 'formidable-pro' ),
			'post_title'             => __( 'Post Title', 'formidable-pro' ),
			'post_author_email'      => __( 'Author Email', 'formidable-pro' ),
			'post_meta key=whatever' => __( 'Post Meta', 'formidable-pro' ),
			'ip'                     => __( 'IP Address', 'formidable-pro' ),
			'auto_id start=1'        => __( 'Increment', 'formidable-pro' ),
			'get param=whatever'     => array(
				'label' => __( 'GET/POST', 'formidable-pro' ),
				'title' => __( 'A variable from the URL or value posted from previous page.', 'formidable-pro' ) . ' ' . __( 'Replace \'whatever\' with the parameter name. In url.com?product=form, the variable is \'product\'. You would use [get param=product] in your field.', 'formidable-pro' ),
			),
			'server param=whatever'  => array(
				'label' => 'SERVER',
				'title' => __( 'A variable from the PHP SERVER array.', 'formidable-pro' ) . ' ' . __( 'Replace \'whatever\' with the parameter name. To get the url of the current page, use [server param="REQUEST_URI"] in your field.', 'formidable-pro' ),
			),
		);

		self::maybe_remove_ip( $tags );

		include FrmProAppHelper::plugin_path() . '/classes/views/frmpro-forms/instructions.php';
	}

	/**
	 * @param array $tags
	 * @return void
	 */
	private static function maybe_remove_ip( &$tags ) {
		if ( ! FrmAppHelper::ips_saved() ) {
			unset( $tags['ip'] );
		}
	}

	/**
	 * Maybe add a link wrapper around field HTML to turn it into an active button.
	 *
	 * @param string $field_type
	 * @return string
	 */
	public static function add_field_link( $field_type ) {
		if ( FrmProAddonsController::is_expired_outside_grace_period() ) {
			return $field_type;
		}
		return '<a href="#" class="frm_add_field">' . $field_type . '</a>';
	}

	/**
	 * @param array $atts
	 * @param array $all_atts
	 * @return void
	 */
	public static function formidable_shortcode_atts( $atts, $all_atts ) {
		global $frm_vars, $wpdb;

		// reset globals
		$frm_vars['readonly']      = $atts['readonly'];
		$frm_vars['editing_entry'] = false;
		$frm_vars['show_fields']   = array();

		self::set_included_fields( $atts );

		if ( $atts['entry_id'] && $atts['entry_id'] === 'last' ) {
			$user_ID = get_current_user_id();
			if ( $user_ID ) {
				$frm_vars['editing_entry'] = FrmDb::get_var(
					$wpdb->prefix . 'frm_items',
					array(
						'form_id' => $atts['id'],
						'user_id' => $user_ID,
					),
					'id',
					array( 'order_by' => 'created_at DESC' )
				);
			}
		} elseif ( $atts['entry_id'] ) {
			$frm_vars['editing_entry'] = $atts['entry_id'];
		}

		foreach ( $atts as $unset => $val ) {
			if ( is_array( $all_atts ) && isset( $all_atts[ $unset ] ) ) {
				unset( $all_atts[ $unset ] );
			}
			unset( $unset, $val );
		}

		if ( is_array( $all_atts ) ) {
			foreach ( $all_atts as $att => $val ) {
				$_GET[ $att ] = $val;
				unset( $att, $val );
			}
		}

		self::maybe_set_page( $atts, $all_atts );
	}

	/**
	 * Set page if the page attribute is set on a form shortcode.
	 *
	 * @since 5.5.3
	 *
	 * @param array $atts
	 * @param array $all_atts
	 * @return void
	 */
	private static function maybe_set_page( $atts, $all_atts ) {
		if ( empty( $all_atts['page'] ) || empty( $atts['id'] ) ) {
			return;
		}

		if ( is_numeric( $all_atts['page'] ) ) {
			$page = absint( $all_atts['page'] );
		} else {
			$param_name = sanitize_text_field( $all_atts['page'] );
			$page       = FrmAppHelper::simple_get( $param_name, 'absint', 0 );
		}

		if ( is_numeric( $atts['id'] ) ) {
			$form_id = absint( $atts['id'] );
		} else {
			$form_key = sanitize_text_field( $atts['id'] );
			$form_id  = $form_key ? FrmForm::get_id_by_key( $form_key ) : 0;
		}

		if ( $page <= 1 || ! $form_id ) {
			return;
		}

		global $frm_vars;
		if ( ! empty( $frm_vars['created_entries'] ) && ! empty( $frm_vars['created_entries'][ $form_id ] ) ) {
			return;
		}

		FrmProEntriesController::maybe_set_page_from_attribute( $form_id, $page );
	}

	/**
	 * If fields are excluded in the form shortcode, set the list of all fields
	 * that should be included.
	 *
	 * @since 4.03.03
	 *
	 * @param array $atts
	 * @return void
	 */
	public static function set_included_fields( $atts ) {
		FrmProGlobalVarsHelper::get_instance( true )->set_included_fields( $atts );
	}

	/**
	 * Echo additional form classes inside of a form's class attribute.
	 *
	 * @param stdClass $form
	 * @return void
	 */
	public static function add_form_classes( $form ) {
		echo ' frm_pro_form ';

		if ( ! FrmProFormsHelper::lite_supports_ajax_submit() && FrmProForm::is_ajax_on( $form ) ) {
			echo ' frm_ajax_submit ';
		}

		self::maybe_add_hide_class( $form );

		if ( current_user_can( 'activate_plugins' ) && current_user_can( 'frm_edit_forms' ) ) {
			echo ' frm-admin-viewing ';
		}

		$style = FrmStylesController::get_form_style( $form->id );
		if ( is_object( $style ) && ! empty( $style->post_content['bg_image_id'] ) ) {
			echo ' frm_with_bg_image ';
		}

		self::add_transitions( $form );
	}

	/**
	 * @param stdClass $form
	 * @return void
	 */
	private static function maybe_add_hide_class( $form ) {
		$frm_settings = FrmAppHelper::get_settings();
		if ( $frm_settings->fade_form && FrmProForm::has_fields_with_conditional_logic( $form ) ) {
			echo ' frm_logic_form ';
		}
	}

	/**
	 * @since 4.05
	 *
	 * @param stdClass $form
	 * @return void
	 */
	private static function add_transitions( $form ) {
		$transition = isset( $form->options['transition'] ) ? $form->options['transition'] : '';
		if ( empty( $transition ) ) {
			return;
		}

		echo ' frm_' . esc_attr( $transition ) . ' ';

		if ( FrmProFormsHelper::going_to_prev( $form->id ) ) {
			echo ' frm_going_back ';
		}
	}

	/**
	 * @param string $class
	 * @return string
	 */
	public static function form_fields_class( $class ) {
		global $frm_page_num;
		if ( $frm_page_num ) {
			$class .= ' frm_page_num_' . $frm_page_num;
		}

		return $class;
	}

	/**
	 * @param stdClass $form
	 * @return void
	 */
	public static function form_hidden_fields( $form ) {
		if ( self::is_draft_visible_to_user( $form ) && isset( $form->options['save_draft'] ) && $form->options['save_draft'] == 1 ) {
			echo '<input type="hidden" name="frm_saving_draft" class="frm_saving_draft" value="" />';
		}
		FrmProFormState::maybe_render_state_field();
		FrmProFieldCaptcha::render_checked_response();
	}

	public static function submit_button_label( $submit, $form ) {
		global $frm_vars;
		if ( ! FrmProFormsHelper::is_final_page( $form->id ) ) {
			$submit = $frm_vars['next_page'][ $form->id ];
			if ( is_object( $submit ) ) {
				$submit = $submit->name;
			}
		}
		return $submit;
	}

	public static function replace_shortcodes( $html, $form, $values = array() ) {
		preg_match_all( "/\[(if )?(deletelink|back_label|back_hook|back_button|draft_label|save_draft|draft_hook|start_over|start_over_label|start_over_hook)\b(.*?)(?:(\/))?\](?:(.+?)\[\/\2\])?/s", $html, $shortcodes, PREG_PATTERN_ORDER );

		if ( empty( $shortcodes[0] ) ) {
			return $html;
		}

		foreach ( $shortcodes[0] as $short_key => $tag ) {
			$replace_with = '';
			$atts         = FrmShortcodeHelper::get_shortcode_attribute_array( $shortcodes[3][ $short_key ] );

			switch ( $shortcodes[2][ $short_key ] ) {
				case 'deletelink':
					$replace_with = FrmProEntriesController::entry_delete_link( $atts );
					break;
				case 'back_label':
					global $frm_vars;
					if ( isset( $frm_vars['frm_prev_label'] ) ) {
						$replace_with = $frm_vars['frm_prev_label'];
					} elseif ( isset( $values['prev_label'] ) ) {
						$replace_with = $values['prev_label'];
					} else {
						$replace_with = __( 'Previous', 'formidable-pro' );
					}
					break;
				case 'back_hook':
					$replace_with = apply_filters( 'frm_back_button_action', '', $form );
					break;
				case 'back_button':
					global $frm_vars;
					if ( ! $frm_vars['prev_page'] || ! is_array( $frm_vars['prev_page'] ) || empty( $frm_vars['prev_page'][ $form->id ] ) ) {
						unset( $replace_with );
					} else {
						$classes = apply_filters( 'frm_back_button_class', array(), $form );
						if ( ! empty( $classes ) ) {
							$html = str_replace( 'class="frm_prev_page', 'class="frm_prev_page ' . implode( ' ', $classes ), $html );
						}

						$html = str_replace( '[/if back_button]', '', $html );
					}
					break;
				case 'draft_label':
					$replace_with = esc_html( ! empty( $form->options['draft_label'] ) ? $form->options['draft_label'] : __( 'Save Draft', 'formidable-pro' ) );
					break;
				case 'save_draft':
					if ( ! self::is_draft_visible_to_user( $form ) || ! isset( $form->options['save_draft'] ) || $form->options['save_draft'] != 1 || ( isset( $values['is_draft'] ) && ! $values['is_draft'] ) ) {
						//remove button if user is not logged in, drafts are not allowed, or editing an entry that is not a draft
						unset( $replace_with );
					} else {
						$html = str_replace( '[/if save_draft]', '', $html );
					}
					break;
				case 'draft_hook':
					$replace_with = apply_filters( 'frm_draft_button_action', '', $form );
					break;

				case 'start_over':
					if ( empty( $form->options['start_over'] ) ) {
						unset( $replace_with );
					} else {
						$html = str_replace( '[/if start_over]', '', $html );
					}
					break;

				case 'start_over_label':
					$replace_with = esc_html( ! empty( $form->options['start_over_label'] ) ? $form->options['start_over_label'] : __( 'Start Over', 'formidable-pro' ) );
					break;

				case 'start_over_hook':
					$replace_with = apply_filters( 'frm_start_over_button_action', '', $form );
			}

			if ( isset( $replace_with ) ) {
				$html = str_replace( $shortcodes[0][ $short_key ], $replace_with, $html );
			}

			unset( $short_key, $tag, $replace_with );
		}

		return $html;
	}

	public static function replace_content_shortcodes( $content, $entry, $shortcodes ) {
		remove_filter( 'frm_replace_content_shortcodes', 'FrmFormsController::replace_content_shortcodes', 20 );
		return FrmProContent::replace_shortcodes( $content, $entry, $shortcodes );
	}

	/**
	 * @since 5.0.17
	 *
	 * @param string $class
	 * @param string $style
	 * @param array  $args
	 * @return string
	 */
	public static function add_form_style_class( $class, $style, $args = array() ) {
		if ( empty( $args['form'] ) || empty( $args['form']['submit_align'] ) || 'full' !== $args['form']['submit_align'] ) {
			return $class;
		}
		return $class . ' frm_full_submit';
	}

	/**
	 * Get the dropdown options for inserting conditional statement shortcodes (like [if x equals="Value"][/if x]).
	 *
	 * @param array $options
	 * @return array
	 */
	public static function conditional_options( $options ) {
		$cond_opts = array(
			'equals'                   => __( 'Equals', 'formidable-pro' ),
			'not_equal'                => __( 'Does not equal', 'formidable-pro' ),
			'contains'                 => __( 'Contains', 'formidable-pro' ),
			'does_not_contain'         => __( 'Does not contain', 'formidable-pro' ),
			'greater_than'             => __( 'Is greater than', 'formidable-pro' ),
			'greater_than_or_equal_to' => __( 'Is greater than or equal to', 'formidable-pro' ),
			'less_than'                => __( 'Is less than', 'formidable-pro' ),
			'less_than_or_equal_to'    => __( 'Is less than or equal to', 'formidable-pro' ),
		);
		return array_merge( $options, $cond_opts );
	}

	/**
	 * @param array $options
	 * @return array
	 */
	public static function advanced_options( $options ) {
		$adv_opts = array(
			'x clickable=1'                               => __( 'Clickable Links', 'formidable-pro' ),
			'x links=0'                                   => array(
				'label' => __( 'Remove Links', 'formidable-pro' ),
				'title' => __( 'Removes the automatic links to category pages', 'formidable-pro' ),
			),
			'x sanitize=1'                                => array(
				'label' => __( 'Sanitize', 'formidable-pro' ),
				'title' => __( 'Replaces spaces with dashes and lowercases all. Use if adding an HTML class or ID', 'formidable-pro' ),
			),
			'x sanitize_url=1'                            => array(
				'label' => __( 'Sanitize URL', 'formidable-pro' ),
				'title' => __( 'Replaces all HTML entities with a URL safe string.', 'formidable-pro' ),
			),
			'x truncate=40'                               => array(
				'label' => __( 'Truncate', 'formidable-pro' ),
				'title' => __( 'Truncate text with a link to view more. If using Both (dynamic), the link goes to the detail page. Otherwise, it will show in-place.', 'formidable-pro' ),
			),
			'x truncate=100 more_text="More"'             => __( 'More Text', 'formidable-pro' ),
			'x time_ago=1'                                => array(
				'label' => __( 'Time Ago', 'formidable-pro' ),
				'title' => __( 'How long ago a date was in minutes, hours, days, months, or years.', 'formidable-pro' ),
			),
			'x offset="+1 month"'                         => array(
				'label' => __( 'Date Offset', 'formidable-pro' ),
				'title' => __( 'Add or remove time from the selected date for date calculations.', 'formidable-pro' ),
			),
			'x decimal=2 dec_point="." thousands_sep=","' => __( '# Format', 'formidable-pro' ),
			'x show="value"'                              => array(
				'label' => __( 'Saved Value', 'formidable-pro' ),
				'title' => __( 'Show the saved value for fields with separate values.', 'formidable-pro' ),
			),
			'x striphtml=1'                               => array(
				'label' => __( 'Remove HTML', 'formidable-pro' ),
				'title' => __( 'Remove all HTML added into your form before display', 'formidable-pro' ),
			),
			'x keepjs=1'                                  => array(
				'label' => __( 'Keep JS', 'formidable-pro' ),
				'title' => __( 'Javascript from your form entries are automatically removed. Add this option only if you trust those submitting entries.', 'formidable-pro' ),
			),
		);

		$options = array_merge( $options, $adv_opts );
		return $options;
	}

	/**
	 * Handles frm_pre_get_form action.
	 *
	 * @since 5.2.02
	 *
	 * @param stdClass $form
	 * @return void
	 */
	public static function pre_get_form( $form ) {
		self::add_submit_conditions_to_frm_vars( $form );
		self::add_honeypot_globals_to_frm_vars( $form );
	}

	/**
	 * Add submit conditions to $frm_vars for inclusion in Conditional Logic processing
	 *
	 * @param stdClass $form
	 * @return void
	 */
	public static function add_submit_conditions_to_frm_vars( $form ) {
		if ( ! isset( $form->options['submit_conditions'] ) || empty( $form->options['submit_conditions']['hide_field'] ) ) {
			return;
		}

		$submit_field = array(
			'id'              => 'submit_' . $form->id,
			'key'             => 'submit_' . $form->id,
			'type'            => 'submit',
			'form_id'         => $form->id,
			'parent_form_id'  => $form->id,
			'form_select'     => '',
			'hide_field'      => $form->options['submit_conditions']['hide_field'],
			'hide_field_cond' => $form->options['submit_conditions']['hide_field_cond'],
			'hide_opt'        => $form->options['submit_conditions']['hide_opt'],
			'show_hide'       => $form->options['submit_conditions']['show_hide'],
			'any_all'         => $form->options['submit_conditions']['any_all'],
		);

		FrmProFieldsHelper::setup_conditional_fields( $submit_field );
	}

	/**
	 * @since 5.2.02
	 *
	 * @param stdClass $form
	 * @return void
	 */
	private static function add_honeypot_globals_to_frm_vars( $form ) {
		global $frm_vars;

		if ( ! array_key_exists( 'honeypot', $frm_vars ) ) {
			$frm_vars['honeypot'] = array();
		}

		if ( class_exists( 'FrmHoneypot' ) ) {
			$honeypot = isset( $form->options['honeypot'] ) ? $form->options['honeypot'] : 'basic';
		} else {
			$honeypot = 'strict';
		}

		$frm_vars['honeypot'][ $form->id ] = $honeypot;
	}

	/**
	 * @param string $button
	 * @param array  $args
	 * @return string
	 */
	public static function maybe_hide_submit_button( $button, $args ) {
		if ( ! is_array( $args ) || empty( $args['form'] ) ) {
			return $button;
		}

		$form = $args['form'];
		if ( ! isset( $form->options['submit_align'] ) || 'none' !== $form->options['submit_align'] ) {
			return $button;
		}

		if ( ! FrmProFormsHelper::is_final_page( $form->id ) ) {
			return $button;
		}

		return preg_replace( '/frm_button_submit/', 'frm_button_submit frm_hidden', $button, 1 );
	}

	/**
	 * Adds a row to Conditional Logic for the submit button
	 */
	public static function _submit_logic_row() {
		FrmAppHelper::permission_check( 'frm_edit_forms' );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		$meta_name   = FrmAppHelper::get_post_param( 'meta_name', '', 'absint' );
		$hide_field  = '';
		$form_id     = FrmAppHelper::get_param( 'form_id', '', 'get', 'absint' );
		$form        = FrmForm::getOne( $form_id );
		$form_fields = FrmField::get_all_for_form( $form_id );
		if ( ! $form_fields ) {
			wp_die();
		}
		$exclude_fields = array_merge( FrmField::no_save_fields(), array( 'file', 'rte', 'date' ) );

		$condition         = array(
			'hide_field'      => '',
			'hide_field_cond' => '==',
		);
		$submit_conditions = array( $condition );

		include FrmProAppHelper::plugin_path() . '/classes/views/frmpro-forms/_submit_logic_row.php';

		wp_die();
	}

	/**
	 * @param array $atts
	 * @return void
	 */
	public static function include_logic_row( $atts ) {
		$defaults = array(
			'meta_name'      => '',
			'condition'      => array(
				'hide_field'      => '',
				'hide_field_cond' => '==',
				'hide_opt'        => '',
			),
			'key'            => '',
			'type'           => 'form',
			'form_id'        => 0,
			'id'             => '',
			'name'           => '',
			'names'          => array(),
			'showlast'       => '',
			'hidelast'       => '',
			'onchange'       => '',
			'exclude_fields' => array_merge( FrmField::no_save_fields(), array( 'file', 'rte', 'date' ) ),
		);

		$atts = wp_parse_args( $atts, $defaults );

		if ( empty( $atts['id'] ) ) {
			$atts['id'] = 'frm_logic_' . $atts['key'] . '_' . $atts['meta_name'];
		}

		if ( empty( $atts['name'] ) ) {
			$atts['name'] = 'frm_form_action[' . $atts['key'] . '][post_content][conditions][' . $atts['meta_name'] . ']';
		}

		if ( empty( $atts['names'] ) ) {
			$atts['names'] = array(
				'hide_field'      => $atts['name'] . '[hide_field]',
				'hide_field_cond' => $atts['name'] . '[hide_field_cond]',
				'hide_opt'        => $atts['name'] . '[hide_opt]',
			);
		}

		// TODO: get rid of this and add event binding instead
		if ( $atts['onchange'] == '' ) {
			$atts['onchange'] = "frmGetFieldValues(this.value,'" . $atts['key'] . "','" . $atts['meta_name'] . "','','" . $atts['names']['hide_opt'] . "')";
		}

		$form_fields = FrmField::get_all_for_form( $atts['form_id'], '', 'include' );

		extract( $atts );
		include FrmProAppHelper::plugin_path() . '/classes/views/frmpro-forms/_logic_row.php';
	}

	public static function setup_new_vars( $values ) {
		return FrmProFormsHelper::setup_new_vars( $values );
	}

	public static function setup_edit_vars( $values ) {
		return FrmProFormsHelper::setup_edit_vars( $values );
	}

	/**
	 * @param array $shortcodes
	 * @return array $shortcodes
	 */
	public static function popup_shortcodes( $shortcodes ) {
		$shortcodes['frm-graph']       = array(
			'name'  => __( 'Graph', 'formidable-pro' ),
			'label' => __( 'Insert a Graph', 'formidable-pro' ),
		);
		$shortcodes['frm-search']      = array(
			'name'  => __( 'Search', 'formidable-pro' ),
			'label' => __( 'Add a Search Form', 'formidable-pro' ),
		);
		$shortcodes['frm-show-entry']  = array(
			'name'  => __( 'Single Entry', 'formidable-pro' ),
			'label' => __( 'Display a Single Entry', 'formidable-pro' ),
		);
		$shortcodes['frm-entry-links'] = array(
			'name'  => __( 'List of Entries', 'formidable-pro' ),
			'label' => __( 'Display a List of Entries', 'formidable-pro' ),
		);

		/*
		To add:
			formresults, frm-entry-edit-link, frm-entry-delete-link,
			frm-entry-update-field, frm-field-value, frm-set-get?,
			frm-alt-color?
		*/
		return $shortcodes;
	}

	public static function sc_popup_opts( $opts, $shortcode ) {
		$function_name = 'popup_opts_' . str_replace( '-', '_', $shortcode );
		if ( method_exists( 'FrmProFormsController', $function_name ) ) {
			self::$function_name( $opts, $shortcode );
		}
		return $opts;
	}

	/**
	 * @param array $opts
	 * @return void
	 */
	private static function popup_opts_formidable( array &$opts ) {
		//'fields' => '', 'entry_id' => 'last' or #, 'exclude_fields' => '', GET => value
		$opts['readonly'] = array(
			'val'   => 'disabled',
			'label' => __( 'Make read-only fields editable', 'formidable-pro' ),
		);
	}

	private static function popup_opts_display_frm_data( array &$opts, $shortcode ) {
		if ( is_callable( 'FrmViewsDisplaysController::popup_opts_display_frm_data' ) ) {
			FrmViewsDisplaysController::popup_opts_display_frm_data( $opts, $shortcode );
		}
	}

	/**
	 * @param array $opts
	 * @return void
	 */
	private static function popup_opts_frm_search( array &$opts ) {
		$opts = array(
			'style'   => array(
				'val'   => 1,
				'label' => __( 'Use Formidable styling', 'formidable-pro' ),
			), // or custom class?
			'label'   => array(
				'val'   => __( 'Search', 'formidable-pro' ),
				'label' => __( 'Customize search button', 'formidable-pro' ),
				'type'  => 'text',
			),
			'post_id' => array(
				'val'   => '',
				'label' => __( 'The ID of the page with the search results', 'formidable-pro' ),
				'type'  => 'text',
			),
		);
	}

	private static function popup_opts_frm_graph( array &$opts, $shortcode ) {
		$where     = array(
			'status'      => 'published',
			'is_template' => 0,
			array(
				'or'               => 1,
				'parent_form_id'   => null,
				'parent_form_id <' => 1,
			),
		);
		$form_list = FrmForm::getAll( $where, 'name' );

		?>
		<h4 class="frm_left_label"><?php esc_html_e( 'Select a form and field:', 'formidable-pro' ); ?></h4>

		<select class="frm_get_field_selection" id="<?php echo esc_attr( $shortcode ); ?>_form">
			<option value="">&mdash; <?php esc_html_e( 'Select Form', 'formidable-pro' ); ?> &mdash;</option>
			<?php foreach ( $form_list as $form_opts ) { ?>
			<option value="<?php echo esc_attr( $form_opts->id ); ?>">
				<?php echo '' == $form_opts->name ? esc_html__( '(no title)', 'formidable-pro' ) : esc_html( FrmAppHelper::truncate( $form_opts->name, 50 ) ); ?>
			</option>
			<?php } ?>
		</select>

		<span id="<?php echo esc_attr( $shortcode ); ?>_fields_container">
		</span>

		<div class="frm_box_line"></div><?php

		$opts = array(
			'type'         => array(
				'val'   => 'default',
				'label' => __( 'Graph Type', 'formidable-pro' ),
				'type'  => 'select',
				'opts'  => array(
					'column'       => __( 'Column', 'formidable-pro' ),
					'hbar'         => __( 'Horizontal Bar', 'formidable-pro' ),
					'pie'          => __( 'Pie', 'formidable-pro' ),
					'line'         => __( 'Line', 'formidable-pro' ),
					'area'         => __( 'Area', 'formidable-pro' ),
					'scatter'      => __( 'Scatter', 'formidable-pro' ),
					'histogram'    => __( 'Histogram', 'formidable-pro' ),
					'table'        => __( 'Table', 'formidable-pro' ),
					'stepped_area' => __( 'Stepped Area', 'formidable-pro' ),
					'geo'          => __( 'Geographical Map', 'formidable-pro' ),
				),
			),
			'data_type'    => array(
				'val'   => 'count',
				'label' => __( 'Data Type', 'formidable-pro' ),
				'type'  => 'select',
				'opts'  => FrmProGraphsController::get_data_type_options(),
			),
			'height'       => array(
				'val'   => '',
				'label' => __( 'Height', 'formidable-pro' ),
				'type'  => 'text',
			),
			'width'        => array(
				'val'   => '',
				'label' => __( 'Width', 'formidable-pro' ),
				'type'  => 'text',
			),
			'bg_color'     => array(
				'val'   => '',
				'label' => __( 'Background color', 'formidable-pro' ),
				'type'  => 'text',
			),
			'title'        => array(
				'val'   => '',
				'label' => __( 'Graph title', 'formidable-pro' ),
				'type'  => 'text',
			),
			'title_size'   => array(
				'val'   => '',
				'label' => __( 'Title font size', 'formidable-pro' ),
				'type'  => 'text',
			),
			'title_font'   => array(
				'val'   => '',
				'label' => __( 'Title font name', 'formidable-pro' ),
				'type'  => 'text',
			),
			'is3d'         => array(
				'val'   => 1,
				'label' => __( 'Turn your pie graph three-dimensional', 'formidable-pro' ),
				'show'  => array( 'type' => 'pie' ),
			),
			'include_zero' => array(
				'val'   => 1,
				'label' => __( 'When using dates for the x_axis parameter, you can include dates with a zero value.', 'formidable-pro' ),
			),
			'show_key'     => array(
				'val'   => 1,
				'label' => __( 'Include a legend with the graph', 'formidable-pro' ),
			),
		);
	}

	private static function popup_opts_frm_show_entry( array &$opts, $shortcode ) {

		?>
	<h4 class="frm_left_label"><?php esc_html_e( 'Insert an entry ID/key:', 'formidable-pro' ); ?></h4>

	<input type="text" value="" id="frmsc_<?php echo esc_attr( $shortcode ); ?>_id" />

	<div class="frm_box_line"></div>
<?php
		$opts = array(
			'user_info'     => array(
				'val'   => 1,
				'label' => __( 'Include user info like browser and IP', 'formidable-pro' ),
			),
			'include_blank' => array(
				'val'   => 1,
				'label' => __( 'Include rows for blank fields', 'formidable-pro' ),
			),
			'plain_text'    => array(
				'val'   => 1,
				'label' => __( 'Do not include any HTML', 'formidable-pro' ),
			),
			'direction'     => array(
				'val'   => 'rtl',
				'label' => __( 'Use RTL format', 'formidable-pro' ),
			),
			'font_size'     => array(
				'val'   => '',
				'label' => __( 'Font size', 'formidable-pro' ),
				'type'  => 'text',
			),
			'text_color'    => array(
				'val'   => '',
				'label' => __( 'Text color', 'formidable-pro' ),
				'type'  => 'text',
			),
			'border_width'  => array(
				'val'   => '',
				'label' => __( 'Border width', 'formidable-pro' ),
				'type'  => 'text',
			),
			'border_color'  => array(
				'val'   => '',
				'label' => __( 'Border color', 'formidable-pro' ),
				'type'  => 'text',
			),
			'bg_color'      => array(
				'val'   => '',
				'label' => __( 'Background color', 'formidable-pro' ),
				'type'  => 'text',
			),
			'alt_bg_color'  => array(
				'val'   => '',
				'label' => __( 'Alternate background color', 'formidable-pro' ),
				'type'  => 'text',
			),
		);
	}

	private static function popup_opts_frm_entry_links( array &$opts, $shortcode ) {
		$opts = array(
			'form_id'     => 'id',
			'field_key'   => array(
				'val'   => 'created_at',
				'type'  => 'text',
				'label' => __( 'Field ID/key for labels', 'formidable-pro' ),
			),
			'type'        => array(
				'val'   => 'list',
				'label' => __( 'Display format', 'formidable-pro' ),
				'type'  => 'select',
				'opts'  => array(
					'list'     => __( 'List', 'formidable-pro' ),
					'select'   => __( 'Drop down', 'formidable-pro' ),
					'collapse' => __( 'Expanding archive', 'formidable-pro' ),
				),
			),
			'logged_in'   => array(
				'val'   => 1,
				'type'  => 'select',
				'label' => __( 'Privacy', 'formidable-pro' ),
				'opts'  => array(
					1 => __( 'Only include the entries the current user created', 'formidable-pro' ),
					0 => __( 'Include all entries', 'formidable-pro' ),
				),
			),
			'page_id'     => array(
				'val'   => '',
				'label' => __( 'The ID of the page to link to', 'formidable-pro' ),
				'type'  => 'text',
			),
			'edit'        => array(
				'val'   => 1,
				'type'  => 'select',
				'label' => __( 'Link action', 'formidable-pro' ),
				'opts'  => array(
					1 => __( 'Edit if allowed', 'formidable-pro' ),
					0 => __( 'View only', 'formidable-pro' ),
				),
			),
			'show_delete' => array(
				'val'   => '',
				'label' => __( 'Delete link label', 'formidable-pro' ),
				'type'  => 'text',
			),
			'confirm'     => array(
				'val'   => '',
				'label' => __( 'Delete confirmation message', 'formidable-pro' ),
				'type'  => 'text',
			),
			'link_type'   => array(
				'val'   => 'page',
				'type'  => 'select',
				'label' => __( 'Send users to', 'formidable-pro' ),
				'opts'  => array(
					'page'   => __( 'A page', 'formidable-pro' ),
					'scroll' => __( 'An anchor on the page with id="[key]"', 'formidable-pro' ),
					'admin'  => __( 'The entry in the back-end', 'formidable-pro' ),
				),
			),
			'param_name'  => array(
				'val'   => 'entry',
				'label' => __( 'URL parameter (?entry=5)', 'formidable-pro' ),
				'type'  => 'text',
			),
			'param_value' => array(
				'val'   => 'key',
				'type'  => 'select',
				'label' => __( 'Identify the entry by', 'formidable-pro' ),
				'opts'  => array(
					'key' => __( 'Entry key', 'formidable-pro' ),
					'id'  => __( 'Entry ID', 'formidable-pro' ),
				),
			),
			'class'       => array(
				'val'   => '',
				'label' => __( 'Add HTML classes', 'formidable-pro' ),
				'type'  => 'text',
			),
			'blank_label' => array(
				'val'   => '',
				'label' => __( 'Label on first option in the dropdown', 'formidable-pro' ),
				'type'  => 'text',
			),
			'drafts'      => array(
				'val'   => 1,
				'label' => __( 'Include draft entries', 'formidable-pro' ),
			),
		);
	}

	/**
	 * Add Pro field helpers to Customization Panel
	 *
	 * @since 2.0.22
	 * @param array $entry_shortcodes
	 * @param bool $settings_tab
	 * @return array
	 */
	public static function add_pro_field_helpers( $entry_shortcodes, $settings_tab ) {
		if ( ! $settings_tab ) {
			$entry_shortcodes['detaillink']                      = __( 'Detail Link', 'formidable-pro' );
			$entry_shortcodes['editlink label="Edit" page_id=x'] = __( 'Edit Entry Link', 'formidable-pro' );
			$entry_shortcodes['entry_count']                     = __( 'Entry Count', 'formidable-pro' );
			$entry_shortcodes['entry_position']                  = __( 'Entry Position', 'formidable-pro' );
			$entry_shortcodes['evenodd']                         = __( 'Even/Odd', 'formidable-pro' );
			$entry_shortcodes['is_draft']                        = __( 'Draft status', 'formidable-pro' );
			$entry_shortcodes['event_date format="Y-m-d"']       = __( 'Calendar Date', 'formidable-pro' );
		}

		return $entry_shortcodes;
	}

	/**
	 * Set the strings to be translatable by multilingual plugins.
	 *
	 * @since 3.06.01
	 * @param array $strings
	 * @param object $form
	 */
	public static function add_form_strings( $strings, $form ) {
		// Add edit and delete options.
		if ( $form->editable ) {
			$strings[] = 'edit_value';
			$strings[] = 'edit_msg';
		}

		if ( isset( $form->options['save_draft'] ) && $form->options['save_draft'] ) {
			if ( isset( $form->options['draft_msg'] ) ) {
				$strings[] = 'draft_msg';
			}
			if ( ! empty( $form->options['draft_label'] ) ) {
				$strings[] = 'draft_label';
			}
		}

		if ( ! empty( $form->options['open_status'] ) ) {
			$strings[] = 'closed_msg';
		}

		if ( ! empty( $form->options['rootline_titles_on'] ) ) {
			$strings[] = 'rootline_titles';
		}

		if ( ! empty( $form->options['start_over'] ) && isset( $form->options['start_over_label'] ) ) {
			$strings[] = 'start_over_label';
		}

		return $strings;
	}

	/**
	 * @param object $entry
	 * @param array  $values
	 * @return void
	 */
	public static function setup_form_data_for_editing_entry( $entry, &$values ) {
		$form = $entry->form_id;
		FrmForm::maybe_get_form( $form );

		if ( ! $form || ! is_array( $form->options ) ) {
			return;
		}

		$values['form_name']      = $form->name;
		$values['parent_form_id'] = $form->parent_form_id;

		foreach ( $form->options as $opt => $value ) {
			$values[ $opt ] = $value;
		}

		$form_defaults = FrmFormsHelper::get_default_opts();

		foreach ( $form_defaults as $opt => $default ) {
			// If 'custom_style' is an empty string, it means that styling is disabled.
			// We do not want to overwrite it with the default value (styling is enabled by default).
			if ( ! isset( $values[ $opt ] ) || ( $values[ $opt ] == '' && 'custom_style' !== $opt ) ) {
				$values[ $opt ] = $default;
			}
		}
		unset( $opt, $default );

		$post_values = wp_unslash( $_POST );
		if ( ! isset( $values['custom_style'] ) ) {
			$values['custom_style'] = FrmAppHelper::custom_style_value( $post_values );
		}

		foreach ( array( 'before', 'after', 'submit' ) as $h ) {
			if ( ! isset( $values[ $h . '_html' ] ) ) {
				$values[ $h . '_html' ] = ( isset( $post_values['options'][ $h . '_html' ] ) ? $post_values['options'][ $h . '_html' ] : FrmFormsHelper::get_default_html( $h ) );
			}
		}
		unset( $h );
	}

	/* Trigger model actions */

	/**
	 * Modifies form options when updating or creating.
	 *
	 * @since 5.4 Added the third param.
	 *
	 * @param array $options Form options.
	 * @param array $values  Form data.
	 * @param bool  $update  Is form updating or creating. Default is `true`: form is updating.
	 * @return array
	 */
	public static function update_options( $options, $values, $update = true ) {
		return FrmProForm::update_options( $options, $values, $update );
	}

	public static function save_wppost_actions( $settings, $action ) {
		return FrmProForm::save_wppost_actions( $settings, $action );
	}

	public static function update_form_field_options( $field_options, $field ) {
		return FrmProForm::update_form_field_options( $field_options, $field );
	}

	/**
	 * Update Pro settings for a form on form update.
	 *
	 * @param int   $id Form id.
	 * @param array $values
	 * @return void
	 */
	public static function update( $id, $values ) {
		FrmProForm::update( $id, $values );

		if ( ! empty( $values['optionmap'] ) ) {
			FrmProForm::maybe_fix_conditions( $id, $values['optionmap'] );
		}

		self::clear_field_transient_for_parent_forms( $id );
	}

	/**
	 * Clear the field transient for any parent forms if this form is embedded.
	 * This is to avoid stale column names in the entries list table.
	 *
	 * @since 6.6
	 *
	 * @param int $form_id
	 * @return void
	 */
	private static function clear_field_transient_for_parent_forms( $form_id ) {
		// Get every embed form field with this form selected in its "form_select" field option value.
		// These are all parent forms that need to their transients cleared.
		$substring  = 's:11:"form_select";s:';
		$substring .= strlen( (string) $form_id ) . ':"' . $form_id . '";';
		$rows       = FrmDb::get_results(
			'frm_fields',
			array(
				'type'               => 'form',
				'field_options LIKE' => $substring,
			),
			'form_id, field_options'
		);
		if ( ! $rows ) {
			return;
		}
		foreach ( $rows as $row ) {
			FrmAppHelper::unserialize_or_decode( $row->field_options );
			if ( ! is_array( $row->field_options ) || empty( $row->field_options['form_select'] ) || $form_id !== (int) $row->field_options['form_select'] ) {
				continue;
			}

			FrmField::delete_form_transient( (int) $row->form_id );
		}
	}

	public static function after_duplicate( $new_opts, $form_id = 0 ) {
		return FrmProForm::after_duplicate( $new_opts, $form_id );
	}

	public static function validate( $errors, $values ) {
		return FrmProForm::validate( $errors, $values );
	}

	/**
	 * @since 5.0.06
	 *
	 * @param string $button
	 * @return string
	 */
	public static function frm_submit_button_html( $button ) {
		FrmProFieldsHelper::replace_non_standard_formidable_shortcodes( array(), $button );
		return $button;
	}

	/**
	 * Loads form via AJAX.
	 *
	 * @since 5.4
	 *
	 * @return void
	 */
	public static function load_form_ajax() {
		check_ajax_referer( 'frm_ajax' );

		$form = FrmAppHelper::get_post_param( 'form', 0, 'intval' );
		if ( ! $form || ! FrmForm::getOne( $form ) ) {
			wp_send_json_error();
		}

		$form_output  = FrmFormsController::show_form( $form );
		$form_output  = str_replace( ' frm_logic_form ', '', $form_output );
		$form_output .= self::get_form_ajax_extra_scripts();
		wp_send_json_success( $form_output );
	}

	/**
	 * Gets extra scripts when loading form via AJAX.
	 *
	 * @since 5.4.2
	 *
	 * @return string
	 */
	private static function get_form_ajax_extra_scripts() {
		ob_start();
		?>
		<script type="text/javascript">
			<?php FrmProFormsHelper::load_dropzone_js( $GLOBALS['frm_vars'] ); ?>
		</script>
		<?php
		$output       = ob_get_clean();
		$has_dropzone = strpos( $output, '__frmDropzone=' );
		if ( $has_dropzone ) {
			$output  = str_replace( '__frmDropzone=', '__frmAjaxDropzone=', $output );
			$js_file = '<script src="' . FrmProAppHelper::plugin_url() . '/js/dropzone.min.js?ver=5.9.3" id="dropzone-js"></script>'; // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
			$output  = $js_file . $output;
		}
		return $output;
	}

	/**
	 * @since 5.3.1
	 *
	 * @return string
	 */
	public static function list_class() {
		return 'FrmProFormsListHelper';
	}

	/**
	 * Add Application column to forms list table.
	 *
	 * @since 5.3.1
	 *
	 * @param array<string,string> $columns
	 * @return array<string,string>
	 */
	public static function get_columns( $columns ) {
		if ( ! is_callable( 'FrmAppHelper::on_form_listing_page' ) || ! FrmAppHelper::on_form_listing_page() ) {
			return $columns;
		}

		$keys       = array_keys( $columns );
		$name_index = array_search( 'name', $keys, true );
		$key        = 'application';
		$label      = __( 'Application', 'formidable-pro' );

		if ( false !== $name_index ) {
			// Place application column after name column.
			$columns = array_slice( $columns, 0, $name_index + 1, true ) +
						array( $key => $label ) +
						array_slice( $columns, $name_index, null, true );
		} else {
			$columns[ $key ] = $label;
		}

		return $columns;
	}

	/**
	 * Hook in before a form is saved to get the original form options values to compare with the updated values.
	 *
	 * @since 6.8.3
	 *
	 * @param int $id
	 * @return void
	 */
	public static function before_update_form_settings( $id ) {
		$form                        = FrmForm::getOne( $id );
		$email_setting_before_update = self::get_email_id_from_options( $form->options );

		add_action(
			'frm_update_form',
			function ( $id, $values ) use ( $email_setting_before_update ) {
				self::check_unique_single_entry_email_type( $email_setting_before_update, $values );
			},
			10,
			2
		);
	}

	/**
	 * Check options for unique_email_id and only return it if the form is set to one entry per email.
	 *
	 * @since 6.8.3
	 *
	 * @param array $options
	 * @return string
	 */
	private static function get_email_id_from_options( $options ) {
		$email_id = '';
		if ( FrmProFormsHelper::check_single_entry_type( $options, 'email' ) && ! empty( $options['unique_email_id'] ) ) {
			$email_id = $options['unique_email_id'];
		}
		return $email_id;
	}

	/**
	 * When a form is updated, check the single entry email settings.
	 * If the form is set to limit to one entry per email, we need to update the target email field and mark it as unique.
	 * The option to limit entries by email address is really just another way of making email fields unique.
	 *
	 * @since 6.8.3
	 *
	 * @param string $old_email_field_id
	 * @param array  $new_values Form settings values.
	 * @return void
	 */
	private static function check_unique_single_entry_email_type( $old_email_field_id, $new_values ) {
		if ( empty( $new_values['options'] ) || ! is_array( $new_values['options'] ) ) {
			return;
		}

		if ( ! $old_email_field_id ) {
			// Check if a unique field exists and use that for the $old_email_field_id if the setting is technically empty.
			$email_fields          = FrmField::get_all_types_in_form( $new_values['id'], 'email' );
			$unique_email_field_id = self::get_unique_email_field_id( $email_fields );
			if ( $unique_email_field_id ) {
				$old_email_field_id = $unique_email_field_id;
			}
		}

		$new_email_field_id = self::get_email_id_from_options( $new_values['options'] );
		if ( $old_email_field_id === $new_email_field_id ) {
			return;
		}

		// If an old email field was set, mark it as no longer unique.
		if ( $old_email_field_id ) {
			self::update_field_unique_setting( $old_email_field_id, 0 );
		}

		// Mark the new field as unique if one is set.
		if ( $new_email_field_id ) {
			self::update_field_unique_setting( $new_email_field_id, 1 );
		}
	}

	/**
	 * Change the unique setting for a single field.
	 *
	 * @since 6.8.3
	 *
	 * @param int $field_id Target field to change the unique value of.
	 * @param int $value    Unique value, either 1 or 0.
	 */
	private static function update_field_unique_setting( $field_id, $value ) {
		$field = FrmField::getOne( $field_id );
		if ( ! $field ) {
			return;
		}

		$field->field_options['unique'] = $value;
		FrmField::update(
			$field_id,
			array(
				'field_options' => $field->field_options,
			)
		);
	}

	/**
	 * Is draft eligible for "Logged ins" and "Logged out" visitors.
	 *
	 * @since 6.8
	 *
	 * @param stdClass $form Form.
	 *
	 * @return bool
	 */
	private static function is_draft_visible_to_user( $form ) {
		if ( ! empty( $form->options['edit_draft_role'] ) ) {
			$visible = FrmAppHelper::user_has_permission( $form->options['edit_draft_role'] );
		} else {
			$visible = is_user_logged_in();
		}

		return $visible;
	}

	/**
	 * @deprecated 6.12
	 */
	public static function add_js() {
		_deprecated_function( __METHOD__, '6.12' );
	}
}
