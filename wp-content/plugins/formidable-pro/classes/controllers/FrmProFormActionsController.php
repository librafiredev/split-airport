<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmProFormActionsController {

	public static function register_actions( $actions ) {
        $actions['wppost'] = 'FrmProPostAction';

        include_once FrmProAppHelper::plugin_path() . '/classes/views/frmpro-form-actions/post_action.php';

        return $actions;
    }

	public static function email_action_control( $settings ) {
		$settings['event']    = array_unique(
			array_merge(
				$settings['event'],
				array( 'draft', 'create', 'update', 'delete', 'import' )
			)
		);
	    $settings['priority'] = 41;

	    return $settings;
	}

	/**
	 * Shows custom settings before form action settings.
	 *
	 * @since 6.10.1
	 *
	 * @param $form_action
	 * @param $atts
	 */
	public static function before_form_action_settings( $form_action, $atts ) {
		?>
		<div class="frm_grid_container">
			<?php self::show_repeater_entries_dropdown( $form_action, $atts ); ?>
		</div>
		<?php
	}

	public static function form_action_settings( $form_action, $atts ) {
		global $wpdb;
		extract( $atts );

        $show_logic = self::has_valid_conditions( $form_action->post_content['conditions'] );

        // Text for different actions
        if ( $form_action->post_excerpt === 'email' ) {
			/**
			 * Adds fields to add email attachment.
			 */
			self::add_file_attachment_field( $form_action, $atts );

            $send           = __( 'Send', 'formidable-pro' );
            $stop           = __( 'Stop', 'formidable-pro' );
            $this_action_if = __( 'this notification if', 'formidable-pro' );
        } if ( $form_action->post_excerpt === 'wppost' ) {
            $send           = __( 'Create', 'formidable-pro' );
            $stop           = __( 'Don\'t create', 'formidable-pro' );
            $this_action_if = __( 'this post if', 'formidable-pro' );
        } elseif ( $form_action->post_excerpt === 'register' ) {
            $send           = __( 'Register', 'formidable-pro' );
            $stop           = __( 'Don\'t register', 'formidable-pro' );
            $this_action_if = __( 'user if', 'formidable-pro' );
        } else {
            $send           = __( 'Do', 'formidable-pro' );
            $stop           = __( 'Don\'t do', 'formidable-pro' );
            $this_action_if = __( 'this action if', 'formidable-pro' );
        }

        $form_fields = $atts['values']['fields'];
        unset( $atts['values']['fields'] );
        include FrmProAppHelper::plugin_path() . '/classes/views/frmpro-form-actions/_form_action.php';
	}

	/**
	 * Adds necessary fields to attach a file as attachment to email.
	 *
	 * @since 4.06.02
	 * @param object $form_action Describes the current Form Action.
	 * @param object $pass_args
	 */
	private static function add_file_attachment_field( $form_action, $pass_args ) {
		$has_attachment        = ! empty( $form_action->post_content['email_attachment_id'] );
		$can_generate_csv_file = is_callable( 'FrmXMLController::get_fields_for_csv_export' );

		include FrmProAppHelper::plugin_path() . '/classes/views/frmpro-form-actions/_email_attachment_row.php';
	}

	/**
	 * @since 4.06.02
	 *
	 * @param WP_Post  $action
	 * @param stdClass $entry
	 * @return bool
	 */
	public static function action_conditions_met( $action, $entry ) {
		if ( empty( $entry->parent_entry ) && self::has_repeater_actions_support( $action->post_excerpt ) && self::get_child_form_from_action( $action ) ) {
			// Skip this check if the action is a repeater action. We will check later before running trigger hook.
			return false;
		}

		$notification = $action->post_content;
		$stop         = false;
		$met          = array();

		if ( empty( $notification['conditions'] ) ) {
			return $stop;
		}

		foreach ( $notification['conditions'] as $k => $condition ) {
			if ( ! is_numeric( $k ) ) {
				continue;
			}

			if ( $stop && 'any' === $notification['conditions']['any_all'] && 'stop' === $notification['conditions']['send_stop'] ) {
				continue;
			}

			self::prepare_logic_value( $condition['hide_opt'], $entry );

			$observed_value = self::get_value_from_entry( $entry, $condition['hide_field'] );

			$stop = FrmFieldsHelper::value_meets_condition( $observed_value, $condition['hide_field_cond'], $condition['hide_opt'] );

			if ( $notification['conditions']['send_stop'] === 'send' ) {
				$stop = $stop ? false : true;
			}

			$met[ $stop ] = $stop;
		}

		if ( $notification['conditions']['any_all'] === 'all' && ! empty( $met ) && isset( $met[0] ) && isset( $met[1] ) ) {
			$stop = ( $notification['conditions']['send_stop'] === 'send' );
		} elseif ( $notification['conditions']['any_all'] === 'any' && $notification['conditions']['send_stop'] === 'send' && isset( $met[0] ) ) {
			$stop = false;
		}

		return $stop;
	}

	/**
	 * Prepare the logic value for comparison against the entered value.
	 *
	 * @since 4.06.02 function introduced.
	 * @since 5.4.4 access was changed from private to public and a parameter "$action" was removed as it was not necessary.
	 *
	 * @param array|string $logic_value
	 * @param stdClass     $entry
	 * @return void
	 */
	public static function prepare_logic_value( &$logic_value, $entry ) {
		if ( is_array( $logic_value ) ) {
			$logic_value = reset( $logic_value );
		}

		if ( $logic_value === 'current_user' ) {
			$logic_value = get_current_user_id();
		}

		FrmProFieldsHelper::replace_non_standard_formidable_shortcodes( array(), $logic_value );

		$logic_value = apply_filters( 'frm_content', $logic_value, $entry->form_id, $entry );

		/**
		 * @since 4.04.05
		 */
		$logic_value = apply_filters( 'frm_action_logic_value', $logic_value );
	}

	/**
	 * Get the value from a specific field and entry
	 *
	 * @since 4.06.02
	 * @since 5.4.4 access was changed from private to public.
	 *
	 * @param object $entry
	 * @param int    $field_id
	 *
	 * @return array|bool|mixed|string
	 */
	public static function get_value_from_entry( $entry, $field_id ) {
		$observed_value = '';

		if ( isset( $entry->metas[ $field_id ] ) ) {
			$observed_value = $entry->metas[ $field_id ];
		} elseif ( FrmAppHelper::pro_is_installed() ) {
			$field = FrmField::getOne( $field_id );

			if ( $entry->post_id ) {
				$observed_value = FrmProEntryMetaHelper::get_post_or_meta_value(
					$entry,
					$field,
					array(
						'links'    => false,
						'truncate' => false,
					)
				);
			} else {
				$observed_value = self::maybe_get_child_values_from_entry( $field, $entry );
			}
		}

		/**
		 * @since 4.06.02
		 */
		$observed_value = apply_filters( 'frm_action_logic_value', $observed_value, compact( 'entry', 'field_id' ) );

		return $observed_value;
	}

	/**
	 * @param stdClass $field
	 * @param stdClass $entry
	 * @return array|string
	 */
	private static function maybe_get_child_values_from_entry( $field, $entry ) {
		if ( ! $field || (int) $entry->form_id === (int) $field->form_id ) {
			return '';
		}

		global $wpdb;
		return FrmDb::get_col(
			$wpdb->prefix . 'frm_item_metas m INNER JOIN ' . $wpdb->prefix . 'frm_items i ON i.id = m.item_id',
			array(
				'm.field_id'       => $field->id,
				'i.parent_item_id' => $entry->id,
			),
			'm.meta_value'
		);
	}

	/**
	 * Load a new action logic row with an AJAX request.
	 *
	 * @return void
	 */
	public static function _logic_row() {
		FrmAppHelper::permission_check( 'frm_edit_forms' );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		$meta_name = FrmAppHelper::get_param( 'meta_name', '', 'get', 'sanitize_title' );
		$form_id   = FrmAppHelper::get_param( 'form_id', '', 'get', 'absint' );
		$key       = FrmAppHelper::get_param( 'email_id', '', 'get', 'sanitize_title' );
		$type      = FrmAppHelper::get_param( 'type', '', 'get', 'sanitize_title' );

		$condition = array(
			'hide_field_cond' => '==',
			'hide_field'      => '',
		);

		self::include_action_logic_row( $form_id, $meta_name, $key, $type, $condition );
		wp_die();
	}

	/**
	 * Include a logic row for a form action.
	 *
	 * @since 5.4.5
	 *
	 * @param int    $form_id
	 * @param string $meta_name
	 * @param string $key
	 * @param string $type
	 * @param array  $condition
	 * @return void
	 */
	public static function include_action_logic_row( $form_id, $meta_name, $key, $type, $condition ) {
		$exclude_fields = FrmField::no_save_fields();

		/**
		 * @since 5.4.5
		 *
		 * @param array<string> $exclude_fields
		 * @param array         $args {
		 *     @type int    $form_id
		 *     @type string $type Type of action (ie email, quiz_outcome).
		 * }
		 */
		$exclude_fields = apply_filters( 'frm_action_logic_exclude_fields', $exclude_fields, compact( 'form_id', 'type' ) );

		FrmProFormsController::include_logic_row(
			array(
				'form_id'        => $form_id,
				'meta_name'      => $meta_name,
				'condition'      => $condition,
				'key'            => $key,
				'name'           => 'frm_' . $type . '_action[' . $key . '][post_content][conditions][' . $meta_name . ']',
				'hidelast'       => '#frm_logic_rows_' . $key,
				'showlast'       => '#logic_link_' . $key,
				'exclude_fields' => $exclude_fields,
			)
		);
	}

	/**
	 * Before the form action is saved, check for logic that
	 * needs to be removed.
	 *
	 * @since 3.0
	 */
	public static function remove_incomplete_logic( $settings ) {
		if ( isset( $settings['post_content']['conditions'] ) ) {
			self::remove_logic_without_field( $settings['post_content']['conditions'] );
		}

		return $settings;
	}

	/**
	 * If a condition doesn't include a selected field, remove it
	 *
	 * @since 3.0
	 *
	 * @param array $conditions
	 * @return void
	 */
	private static function remove_logic_without_field( &$conditions ) {
		if ( empty( $conditions ) ) {
			return;
		}

		foreach ( $conditions as $k => $condition ) {
			if ( ! is_numeric( $k ) ) {
				continue;
			}

			if ( empty( $condition['hide_field'] ) ) {
				unset( $conditions[ $k ] );
			}
		}
	}

	/**
	 * If logic includes rows with a field selected, it is value
	 *
	 * @since 3.0
	 *
	 * @param array $conditions
	 * @return bool
	 */
	private static function has_valid_conditions( $conditions ) {
		self::remove_logic_without_field( $conditions );
		return count( $conditions ) > 2;
	}

	public static function fill_action_options( $action, $type ) {
        if ( 'wppost' === $type ) {

            $default_values = array(
                'post_type'          => 'post',
                'post_category'      => array(),
                'post_content'       => '',
                'post_excerpt'       => '',
                'post_title'         => '',
                'post_name'          => '',
                'post_date'          => '',
                'post_status'        => '',
                'post_custom_fields' => array(),
                'post_password'      => '',
                'post_parent'        => '',
				'menu_order'         => '',
            );

            $action->post_content = array_merge( $default_values, (array) $action->post_content );
        }

        return $action;
    }

	/**
	 * @since 2.0.23
	 */
	public static function maybe_trigger_draft_actions( $event, $args ) {
		if ( isset( $args['entry_id'] ) && FrmProEntry::is_draft( $args['entry_id'] ) ) {
			$event = 'draft';
		}
		return $event;
	}

	public static function trigger_draft_actions( $entry_id, $form_id ) {
		FrmFormActionsController::trigger_actions( 'draft', $form_id, $entry_id );
	}

	public static function trigger_update_actions( $entry_id, $form_id ) {
		$event = apply_filters( 'frm_trigger_update_action', 'update', array( 'entry_id' => $entry_id ) );
		FrmFormActionsController::trigger_actions( $event, $form_id, $entry_id );
	}

	public static function trigger_delete_actions( $entry_id, $entry = false ) {
		if ( empty( $entry ) ) {
			$entry = FrmEntry::getOne( $entry_id );
		}
        FrmFormActionsController::trigger_actions( 'delete', $entry->form_id, $entry );
    }

	/**
	 * Merges fields from embedded forms with parent form fields.
	 *
	 * @since 6.8
	 *
	 * @param array $values
	 * @param array $embedded_fields
	 * @return array
	 */
	public static function maybe_merge_fields( $values, $embedded_fields ) {
		if ( empty( $embedded_fields ) ) {
			return $values;
		}
		$values['fields'] = array_merge( $values['fields'], $embedded_fields );

		return $values;
	}

	public static function _postmeta_row() {
		FrmAppHelper::permission_check( 'frm_edit_forms' );
        check_ajax_referer( 'frm_ajax', 'nonce' );

        $custom_data    = array(
			'meta_name' => FrmAppHelper::get_post_param( 'meta_name', '', 'sanitize_text_field' ),
			'field_id'  => '',
		);
        $action_key     = FrmAppHelper::get_post_param( 'action_key', 0, 'absint' );
        $action_control = FrmFormActionsController::get_form_actions( 'wppost' );
        $action_control->_set( $action_key );

        $values  = array();
		$form_id = FrmAppHelper::get_param( 'form_id', '', 'post', 'absint' );

        if ( $form_id ) {
			$values['fields'] = FrmField::getAll(
                array(
					'fi.form_id'  => $form_id,
					'fi.type not' => FrmField::no_save_fields(),
                ),
                'field_order'
            );
        }
        $echo = false;

		$cf_keys = self::get_post_meta_keys();

		if ( $form_id ) {
			$embedded_form_ids = FrmProFormsHelper::get_embedded_form_ids( $form_id );
			if ( $embedded_form_ids ) {
				$embedded_fields = FrmDb::get_results( 'frm_fields', array( 'form_id' => $embedded_form_ids ) );
				$values          = self::maybe_merge_fields( $values, $embedded_fields );
				unset( $embedded_fields );
				unset( $embedded_form_ids );
			}
		}

        include FrmProAppHelper::plugin_path() . '/classes/views/frmpro-form-actions/_custom_field_row.php';
        wp_die();
    }

	/**
	 * @return array
	 */
	private static function get_post_meta_keys() {
		global $wpdb;

		$post_type = FrmAppHelper::get_param( 'post_type', 'post', 'post', 'sanitize_text_field' );
		$limit     = (int) apply_filters( 'postmeta_form_limit', 50 );
		$cf_keys   = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT meta_key
				FROM $wpdb->postmeta pm
				LEFT JOIN $wpdb->posts p
				ON (p.ID = pm.post_ID)
				WHERE p.post_type = %s
				ORDER BY meta_key
				LIMIT %d",
				$post_type,
				$limit
			)
		);

		if ( ! is_array( $cf_keys ) ) {
			$cf_keys = array();
		}

		if ( 'post' === $post_type && ! in_array( '_thumbnail_id', $cf_keys, true ) ) {
			$cf_keys[] = '_thumbnail_id';
		}

		if ( $cf_keys ) {
			natcasesort( $cf_keys );
		}

		return $cf_keys;
	}

	public static function _posttax_row() {
		FrmAppHelper::permission_check( 'frm_edit_forms' );
        check_ajax_referer( 'frm_ajax', 'nonce' );

        if ( isset( $_POST['field_id'] ) ) {
			$show_exclude = FrmAppHelper::get_post_param( 'show_exclude', 0, 'absint' );
            $field_vars   = array(
                'meta_name'    => FrmAppHelper::get_post_param( 'meta_name', '', 'sanitize_text_field' ),
                'field_id'     => FrmAppHelper::get_post_param( 'field_id', '', 'sanitize_text_field' ),
                'show_exclude' => $show_exclude,
                'exclude_cat'  => $show_exclude ? '-1' : 0,
            );
        } else {
            $field_vars = array(
				'meta_name'    => '',
				'field_id'     => '',
				'show_exclude' => 0,
				'exclude_cat'  => 0,
			);
        }

        $tax_meta       = FrmAppHelper::get_post_param( 'tax_key', '', 'sanitize_text_field' );
        $post_type      = FrmAppHelper::get_post_param( 'post_type', '', 'sanitize_text_field' );
        $action_key     = FrmAppHelper::get_post_param( 'action_key', 0, 'absint' );
        $action_control = FrmFormActionsController::get_form_actions( 'wppost' );
        $action_control->_set( $action_key );

        if ( $post_type ) {
            $taxonomies = get_object_taxonomies( $post_type );
        }

        $values = array();

		$form_id = FrmAppHelper::get_post_param( 'form_id', 0, 'absint' );

        if ( $form_id ) {
			$values['fields'] = FrmField::getAll(
                array(
					'fi.form_id' => $form_id,
					'fi.type'    => array( 'checkbox', 'radio', 'select', 'tag', 'data' ),
                ),
                'field_order'
            );
            $values['id']     = $form_id;
        }

        $echo = false;
        include FrmProAppHelper::plugin_path() . '/classes/views/frmpro-form-actions/_post_taxonomy_row.php';
        wp_die();
    }

	/**
	 * Reset excluded categories if show exclude checkbox is off.
	 *
	 * @since 5.5.3
	 *
	 * @param array  $post_content
	 * @param array  $instance
	 * @return array $post_content
	 */
	public static function update_create_post_action( $post_content, $instance ) {
		if ( $instance['post_excerpt'] === 'wppost' && ! empty( $post_content['post_category'] ) ) {
			foreach ( $post_content['post_category'] as $key => $post_cat ) {
				if ( ! isset( $post_cat['show_exclude'] ) ) {
					$post_content['post_category'][ $key ]['exclude_cat'] = array();
				}
			}
		}

		return $post_content;
	}

	public static function _replace_posttax_options() {
		FrmAppHelper::permission_check( 'frm_edit_forms' );
        check_ajax_referer( 'frm_ajax', 'nonce' );

        // Get the post type, and all taxonomies for that post type
        $post_type  = FrmAppHelper::get_post_param( 'post_type', '', 'sanitize_text_field' );
        $taxonomies = $post_type ? get_object_taxonomies( $post_type ) : array();

        // Get the HTML for the options
        include FrmProAppHelper::plugin_path() . '/classes/views/frmpro-form-actions/_post_taxonomy_select.php';
        wp_die();
    }

	/**
	 * Display the taxonomy checkboxes for a specific taxonomy in a Create Post action
	 *
	 * @since 2.01.0
	 * @param array $args (MUST include taxonomy, form_id, field_name, and value)
	 */
	public static function display_taxonomy_checkboxes_for_post_action( $args ) {
		if ( ! $args['taxonomy'] ) {
			return;
		}

		$args['level'] = 1;

		$args['post_type'] = FrmProFormsHelper::post_type( $args['form_id'] );

		$children = get_categories(
			array(
				'hide_empty' => false,
				'parent'     => 0,
				'type'       => $args['post_type'],
				'taxonomy'   => $args['taxonomy'],
			)
		);

		foreach ( $children as $key => $cat ) {
			$args['cat'] = $cat;
			?>
			<div class="frm_catlevel_1"><?php
				self::display_taxonomy_checkbox_group( $args );
				?>
			</div><?php
		}
	}

	/**
	 * Display a single taxonomy checkbox and its children
	 *
	 * @since 2.01.0
	 * @param array $args (MUST include cat, value, field_name, post_type, taxonomy, and level)
	 */
	private static function display_taxonomy_checkbox_group( $args ) {
		if ( ! is_object( $args['cat'] ) ) {
			return;
		}

		if ( is_array( $args['value'] ) ) {
			$checked = in_array( $args['cat']->cat_ID, $args['value'] ) ? ' checked="checked" ' : '';
		} else {
			$checked = checked( $args['value'], $args['cat']->cat_ID, false );
		}

		?>
		<div class="frm_checkbox">
			<label><input type="checkbox" name="<?php echo esc_attr( $args['field_name'] ); ?>" value="<?php
			echo esc_attr( $args['cat']->cat_ID );
			?>"<?php
			echo $checked; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?> /><?php echo esc_html( $args['cat']->cat_name ); ?></label><?php

		$children = get_categories(
			array(
				'type'       => $args['post_type'],
				'hide_empty' => false,
				'parent'     => $args['cat']->cat_ID,
				'taxonomy'   => $args['taxonomy'],
			)
		);

		if ( $children ) {
				++$args['level'];
				foreach ( $children as $key => $cat ) {
					$args['cat'] = $cat;
					?>
		<div class="frm_catlevel_<?php echo esc_attr( $args['level'] ); ?>"><?php self::display_taxonomy_checkbox_group( $args ); ?></div>
	<?php
					}
		}
		echo '</div>';
	}

	/**
	 * AJAX get post parent option.
	 *
	 * @since 4.10.01
	 */
	public static function ajax_get_post_parent_option() {
		FrmAppHelper::permission_check( 'frm_edit_forms' );
		check_ajax_referer( 'frm_ajax' );

		$post_type = FrmAppHelper::get_post_param( 'post_type', '', 'sanitize_text_field' );
		if ( ! $post_type ) {
			wp_send_json_error( __( 'Post type is empty', 'formidable-pro' ) );
		}

		if ( ! is_post_type_hierarchical( $post_type ) ) {
			wp_die( '0' );
		}

		FrmProPostAction::post_parent_dropdown(
			array(
				'post_type'  => $post_type,
				'field_name' => 'REPLACETHISNAME', // This string is replaced in file js/formidable_admin.js in the lite version.
			)
		);

		wp_die();
	}

	/**
	 * AJAX get post parent option.
	 *
	 * @since 4.11.03
	 */
	public static function ajax_should_use_post_menu_order_option() {
		FrmAppHelper::permission_check( 'frm_edit_forms' );
		check_ajax_referer( 'frm_ajax' );

		$post_type = FrmAppHelper::get_post_param( 'post_type', '', 'sanitize_text_field' );
		if ( ! $post_type ) {
			wp_send_json_error( __( 'Post type is empty', 'formidable-pro' ) );
		}

		$should_use_post_menu_order_option = post_type_supports( $post_type, 'page-attributes' ) ? '1' : '0';
		wp_die( esc_html( $should_use_post_menu_order_option ) );
	}

	/**
	 * Shows disabled PDF attachment option.
	 *
	 * @since 5.4.3
	 */
	public static function show_disabled_pdf_attachment_option() {
		if ( function_exists( 'frm_pdfs_autoloader' ) || ! FrmAppHelper::show_new_feature( 'pdfs' ) ) {
			return;
		}

		$data = FrmAddonsController::install_link( 'pdfs' );
		?>
		<div
			id="frm_attach_pdf_setting"
			style="margin-top: 15px;"
			data-upgrade="<?php esc_attr_e( 'Forms to PDF', 'formidable-pro' ); ?>"
			data-oneclick="<?php echo esc_attr( wp_json_encode( $data ) ); ?>"
		>
			<?php
			FrmProHtmlHelper::admin_toggle(
				'frm_attach_pdf',
				'frm_attach_pdf',
				array(
					'div_class' => 'with_frm_style frm_toggle',
					'checked'   => false,
					'echo'      => true,
				)
			);
			?>
			<label id="frm_attach_pdf_label" for="frm_attach_pdf">
				<?php esc_html_e( 'Attach PDF of entry to email', 'formidable-pro' ); ?>
			</label>
		</div>
		<style>
			#frm_attach_pdf_setting label {
				color: var(--grey);
			}
		</style>
		<?php
	}

	/**
	 * Shows disabled ACF integration option.
	 *
	 * @since 5.5.4
	 */
	public static function show_disabled_acf_integration_option() {
		if ( function_exists( 'frm_acf_autoloader' ) || ! FrmAppHelper::show_new_feature( 'acf' ) ) {
			return;
		}

		$data = FrmAddonsController::install_link( 'acf' );
		?>
		<div
			id="frm_acf_setting"
			style="margin-top: 15px;"
			data-upgrade="<?php esc_attr_e( 'ACF integration', 'formidable-pro' ); ?>"
			data-oneclick="<?php echo esc_attr( wp_json_encode( $data ) ); ?>"
		>
			<?php
			FrmProHtmlHelper::admin_toggle(
				'frm_acf',
				'frm_acf',
				array(
					'div_class' => 'with_frm_style frm_toggle',
					'checked'   => false,
					'echo'      => true,
				)
			);
			?>
			<label id="frm_acf_label" for="frm_acf" style="color: var(--grey);">
				<?php esc_html_e( 'Map form fields to Advanced Custom Fields', 'formidable-pro' ); ?>
			</label>
		</div>
		<?php
	}

	/**
	 * Changes the On Submit action options.
	 *
	 * @since 6.0
	 *
	 * @param array $ops Action options.
	 * @return array
	 */
	public static function change_on_submit_action_ops( $ops ) {
		$ops['event'][] = 'update';
		return $ops;
	}

	/**
	 * Checks if the given action supports repeater action.
	 *
	 * @since 6.10.1
	 *
	 * @param string $action_id_base Action ID base.
	 * @return bool
	 */
	public static function has_repeater_actions_support( $action_id_base ) {
		$actions = array(
			'activecampaign',
			'api',
			'aweber',
			'campaignmonitor',
			'constantcontact',
			'convertkit',
			'email',
			'getresponse',
			'googlespreadsheet',
			'hubspot',
			'mailchimp',
			'mailpoet',
			'salesforce',
			'twilio',
		);

		/**
		 * Filters the list of actions that support repeater action.
		 *
		 * @since 6.10.1
		 *
		 * @param array $actions Array of actions.
		 */
		$actions = apply_filters( 'frm_pro_repeater_action_support', $actions );

		return in_array( $action_id_base, $actions, true );
	}

	private static function get_child_form_from_action( $form_action ) {
		return isset( $form_action->post_content['child_form'] ) ? intval( $form_action->post_content['child_form'] ) : 0;
	}

	/**
	 * Shows repeater action dropdown.
	 *
	 * @since 6.10.1
	 *
	 * @param object $form_action Form action.
	 * @param array  $pass_args   Pass args.
	 */
	public static function show_repeater_entries_dropdown( $form_action, $pass_args ) {
		if ( ! self::has_repeater_actions_support( $form_action->post_excerpt ) ) {
			return;
		}

		$repeaters = FrmProFieldsHelper::get_repeater_fields( $pass_args['form']->id );
		if ( ! $repeaters ) {
			return;
		}

		$setting_id = $pass_args['action_control']->get_field_id( 'child_form' );
		$child_form = self::get_child_form_from_action( $form_action );
		?>
		<p class="frm_form_field frm6 frm_first">
			<label for="<?php echo esc_attr( $setting_id ); ?>"><?php esc_html_e( 'Run this action for', 'formidable-pro' ); ?></label>
			<select id="<?php echo esc_attr( $setting_id ); ?>" name="<?php echo esc_attr( $pass_args['action_control']->get_field_name( 'child_form' ) ); ?>">
				<option value=""><?php esc_html_e( 'Main entry', 'formidable-pro' ); ?></option>
				<?php
				foreach ( $repeaters as $repeater ) {
					?>
					<option value="<?php echo intval( $repeater->field_options['form_select'] ); ?>" <?php selected( intval( $repeater->field_options['form_select'] ), $child_form ); ?>><?php echo esc_html( $repeater->name ); ?></option>
					<?php
				}
				?>
			</select>
		</p>
		<?php
	}

	/**
	 * Runs custom form action trigger.
	 *
	 * @since 6.10.1
	 *
	 * @param bool    $skip        Skip default trigger.
	 * @param WP_Post $form_action Form action object.
	 * @param object  $entry       Entry object.
	 * @param object  $form        Form object.
	 * @param string  $event       Event ('create' or 'update').
	 * @return bool
	 */
	public static function custom_trigger( $skip, $form_action, $entry, $form, $event ) {
		if ( ! self::has_repeater_actions_support( $form_action->post_excerpt ) ) {
			return $skip;
		}

		$child_form = self::get_child_form_from_action( $form_action );

		if ( ! $child_form ) {
			return $skip;
		}

		$sub_entries = FrmProEntry::get_sub_entries( $entry->id, true );
		foreach ( $sub_entries as $sub_entry ) {
			if ( intval( $sub_entry->form_id ) !== $child_form ) {
				continue;
			}

			$sub_entry->metas       += $entry->metas;
			$sub_entry->parent_entry = $entry;

			$stop = self::action_conditions_met( $form_action, $sub_entry );
			if ( $stop ) {
				continue;
			}

			do_action( 'frm_trigger_' . $form_action->post_excerpt . '_action', $form_action, $sub_entry, $form, $event );
			do_action( 'frm_trigger_' . $form_action->post_excerpt . '_' . $event . '_action', $form_action, $sub_entry, $form );
		}

		// Return true to skip triggering this action for main entry.
		return true;
	}
}
