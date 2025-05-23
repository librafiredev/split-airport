<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmProEntriesController {

	/**
	 * Used to decide whether we should show entry deleted message since we don't
	 * want to show it when the user is not deleting entry.
	 *
	 * @since 6.17
	 *
	 * @var bool|null
	 */
	private static $delete_link_created;

	public static function remove_fullscreen( $init ) {
		if ( isset( $init['plugins'] ) ) {
			$init['plugins'] = str_replace( 'wpfullscreen,', '', $init['plugins'] );
			$init['plugins'] = str_replace( 'fullscreen,', '', $init['plugins'] );
		}
		return $init;
	}

	/* Back End CRUD */
	public static function show_comments( $entry ) {
		$id      = $entry->id;
		$user_ID = get_current_user_id();

		if ( $_POST && ! empty( $_POST['frm_comment'] ) ) {
			$meta_key   = '';
			$meta_value = array(
				'comment' => FrmAppHelper::get_post_param( 'frm_comment', '', 'sanitize_textarea_field' ),
				'user_id' => $user_ID,
			);
			FrmEntryMeta::add_entry_meta( FrmAppHelper::get_post_param( 'item_id', 0, 'absint' ), 0, $meta_key, $meta_value );
			//send email notifications
		}

		$comments    = FrmEntryMeta::getAll(
            array(
				'item_id'  => $id,
				'field_id' => 0,
            ),
            ' ORDER BY it.created_at ASC',
            '',
            true 
        );
		$date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );

		include FrmProAppHelper::plugin_path() . '/classes/views/frmpro-entries/show.php';
	}

	public static function add_new_entry_link( $form ) {
		FrmProEntriesHelper::show_new_entry_button( $form );
	}

	public static function new_entry() {
		$form_id = FrmAppHelper::get_param( 'form', '', 'get', 'absint' );
		if ( $form_id ) {
			$form = FrmForm::getOne( $form_id );
			self::get_new_vars( '', $form );
		} else {
			include FrmProAppHelper::plugin_path() . '/classes/views/frmpro-entries/new-selection.php';
		}
	}

	public static function create() {
		if ( ! current_user_can( 'frm_create_entries' ) ) {
			return FrmEntriesController::display_list();
		}

		$params = FrmForm::get_admin_params();
		$record = false;
		$form   = false;

		if ( $params['form'] ) {
			$form = FrmForm::getOne( $params['form'] );
		}

		if ( ! $form ) {
			return;
		}

		$errors = FrmEntryValidate::validate( wp_unslash( $_POST ) );

		if ( count( $errors ) > 0 ) {
			self::get_new_vars( $errors, $form );
			return;
		}

		if ( ( isset( $_POST[ 'frm_page_order_' . $form->id ] ) || FrmProFormsHelper::going_to_prev( $form->id ) ) && ! FrmProFormsHelper::saving_draft() ) {
			self::get_new_vars( '', $form );
			return;
		}

		$_SERVER['REQUEST_URI'] = str_replace( '&frm_action=new', '', FrmAppHelper::get_server_value( 'REQUEST_URI' ) );

		global $frm_vars;
		if ( empty( $frm_vars['created_entries'][ $form->id ] ) ) {
			$frm_vars['created_entries'][ $form->id ] = array();
		}

		if ( ! isset( $frm_vars['created_entries'][ $_POST['form_id'] ]['entry_id'] ) ) {
			$record = FrmEntry::create( $_POST );
			$frm_vars['created_entries'][ $form->id ]['entry_id'] = $record;
		}

		if ( $record ) {
			if ( FrmProFormsHelper::saving_draft() ) {
				$message = __( 'Draft was Successfully Created', 'formidable-pro' );
			} else {
				$message = __( 'Entry was Successfully Created', 'formidable-pro' );
			}

			self::get_edit_vars( $record, $errors, $message );
		} else {
			self::get_new_vars( $errors, $form );
		}
	}

	public static function edit() {
		$id = FrmAppHelper::get_param( 'id', '', 'get', 'absint' );

		if ( ! current_user_can( 'frm_edit_entries' ) ) {
			return FrmEntriesController::show( $id );
		}

		self::get_edit_vars( $id );
	}

	public static function update() {
		$id = FrmAppHelper::get_param( 'id', '', 'get', 'absint' );

		if ( ! current_user_can( 'frm_edit_entries' ) ) {
			return FrmEntriesController::show( $id );
		}

		$message = '';
		$errors  = FrmEntryValidate::validate( wp_unslash( $_POST ) );

		if ( empty( $errors ) ) {
			$form_id = FrmAppHelper::get_post_param( 'form_id', '', 'absint' );
			if ( ! empty( $form_id ) && ( isset( $_POST[ 'frm_page_order_' . $form_id ] ) || FrmProFormsHelper::going_to_prev( $form_id ) ) && ! FrmProFormsHelper::saving_draft() ) {
				self::get_edit_vars( $id );
				return;
			}

			FrmEntry::update( $id, $_POST );
			if ( isset( $_POST['form_id'] ) && FrmProFormsHelper::saving_draft() ) {
				$message = __( 'Draft was Successfully Updated', 'formidable-pro' );
			} else {
				$message = __( 'Entry was Successfully Updated', 'formidable-pro' );
			}

			$message .= ' <a href="?page=formidable-entries&form=' . absint( $_POST['form_id'] ) . '"> ' . __( 'Go Back to Entries', 'formidable-pro' ) . '</a>';
		}

		self::get_edit_vars( $id, $errors, $message );
	}

	public static function duplicate() {
		$params = FrmForm::get_admin_params();

		if ( ! current_user_can( 'frm_create_entries' ) ) {
			return FrmEntriesController::show( $params['id'] );
		}

		$message = '';
		$errors  = '';

		$record = FrmEntry::duplicate( $params['id'] );
		if ( $record ) {
			$message = __( 'Entry was Successfully Duplicated', 'formidable-pro' );
		} else {
			$errors = __( 'There was a problem duplicating that entry', 'formidable-pro' );
		}

		if ( ! empty( $errors ) ) {
			return FrmEntriesController::display_list( $message, $errors );
		}

		self::redirect_to_entry_edit( $record );
		exit();
	}

	/**
	 * If a field default value matches [auto_id] patterns, evaluate the shortcode again and assign it to the new entry.
	 *
	 * @since 5.4.4
	 *
	 * @param array $metas
	 *
	 * @return array $metas
	 */
	public static function autoincrement_on_duplicate( $metas ) {
		foreach ( $metas as $meta ) {
			$field = FrmField::getOne( $meta->field_id );
			if ( ! isset( $field->default_value ) || ! is_string( $field->default_value ) ) {
				continue;
			}

			if ( ! preg_match( '/\[auto_id[^\]]*\]/', $field->default_value ) ) {
				continue;
			}
			$value = $field->default_value;

			FrmProFieldsHelper::replace_non_standard_formidable_shortcodes( array( 'field' => $field ), $value );
			$meta->meta_value = $value;
		}

		return $metas;
	}

	/**
	 * Redirect to entry edit page after entry is duplicated.
	 *
	 * @param int $entry_id
	 */
	private static function redirect_to_entry_edit( $entry_id ) {
		FrmAppHelper::permission_check( 'frm_edit_entries' );

		if ( ! wp_verify_nonce( FrmAppHelper::simple_get( '_wpnonce' ) ) ) {
			$frm_settings = FrmAppHelper::get_settings();
			wp_die( esc_html( $frm_settings->admin_permission ) );
		}

		$args = array(
			'page'                        => 'formidable-entries',
			'frm_action'                  => 'edit',
			'id'                          => absint( $entry_id ),
			'frm_entries_updated_message' => 'duplicate_success',
		);

		$url = add_query_arg( $args, admin_url( 'admin.php?' ) );

		wp_safe_redirect( $url );
		exit();
	}

	/**
	 * Translates message code from url into message output to user.
	 *
	 * @param string $message_code
	 *
	 * @return string
	 */
	private static function translate_link_code_to_message( $message_code ) {
		if ( $message_code === 'duplicate_success' ) {
			return __( 'Entry was successfully duplicated', 'formidable-pro' );
		}

		return '';
	}

	/**
	 * @since 6.9.1
	 *
	 * @param int $form_id
	 *
	 * @return bool
	 */
	private static function should_trigger_on_delete_entry_actions( $form_id ) {
		if ( FrmFormAction::form_has_action_type( $form_id, 'wppost' ) ) {
			return true;
		}
		return FrmAppHelper::get_param( 'trigger_on_delete_entry_actions', false ) === 'delete';
	}

	/**
	 * Delete all entries in a form when the 'delete all' button is clicked.
	 *
	 * @since 4.02.04
	 */
	public static function destroy_all() {
		if ( ! current_user_can( 'frm_delete_entries' ) || ! wp_verify_nonce( FrmAppHelper::simple_get( '_wpnonce', '', 'sanitize_text_field' ), '-1' ) ) {
			$frm_settings = FrmAppHelper::get_settings();
			wp_die( esc_html( $frm_settings->admin_permission ) );
		}

		$params  = FrmForm::get_admin_params();
		$message = '';
		$form_id = (int) $params['form'];

		if ( $form_id ) {
			$entry_ids = FrmDb::get_col( 'frm_items', array( 'form_id' => $form_id ) );

			if ( self::should_trigger_on_delete_entry_actions( $form_id ) ) {
				// This action takes a while, so only trigger it if there are posts to delete.
				foreach ( $entry_ids as $entry_id ) {
					$entry = FrmEntry::getOne( $entry_id, true );
					do_action( 'frm_before_destroy_entry', $entry_id, $entry );
					unset( $entry_id, $entry );
				}
			}

			$results = self::delete_form_entries( $form_id );
			if ( $results ) {
				$message = 'destroy_all';
				FrmEntry::clear_cache();
			}
		} else {
			$message = 'no_entries_selected';
		}

		$url = admin_url( 'admin.php?page=formidable-entries&frm_action=list&form=' . absint( $form_id ) );

		if ( $message ) {
			$url .= '&message=' . $message;
		}

		wp_safe_redirect( $url );
		die();
	}

	/**
	 * @since 4.02.04
	 *
	 * @param int $form_id
	 */
	private static function delete_form_entries( $form_id ) {
		global $wpdb;

		$form_ids = self::get_child_form_ids( $form_id );

		$meta_query  = $wpdb->prepare( "DELETE em.* FROM {$wpdb->prefix}frm_item_metas as em INNER JOIN {$wpdb->prefix}frm_items as e on (em.item_id=e.id) WHERE form_id=%d", $form_id );
		$entry_query = $wpdb->prepare( "DELETE FROM {$wpdb->prefix}frm_items WHERE form_id=%d", $form_id );

		if ( ! empty( $form_ids ) ) {
			$form_query   = ' OR form_id in (' . $form_ids . ')';
			$meta_query  .= $form_query;
			$entry_query .= $form_query;
		}

		$wpdb->query( $meta_query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		return $wpdb->query( $entry_query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * @since 4.02.04
	 *
	 * @param int $form_id
	 * @param bool|string $implode
	 */
	private static function get_child_form_ids( $form_id, $implode = ',' ) {
		$form_ids = FrmProForm::get_child_form_ids( $form_id );
		if ( $implode ) {
			$form_ids = implode( $implode, $form_ids );
		}

		return $form_ids;
	}

	public static function bulk_actions( $action = 'list-form' ) {
		$params = FrmForm::get_admin_params();
		$errors = array();

		if ( $action == 'list-form' ) {
			$request_bulkaction  = FrmAppHelper::get_param( 'bulkaction', '-1', 'request', 'sanitize_text_field' );
			$request_bulkaction2 = FrmAppHelper::get_param( 'bulkaction2', '-1', 'request', 'sanitize_text_field' );

			$bulkaction = $request_bulkaction !== '-1' ? $request_bulkaction : $request_bulkaction2;
		} else {
			$bulkaction = str_replace( 'bulk_', '', $action );
		}

		$items = FrmAppHelper::get_param( 'item-action', '', 'get', 'sanitize_text_field' );
		if ( empty( $items ) ) {
			$errors[] = __( 'No entries were specified', 'formidable-pro' );
		} else {
			$frm_settings = FrmAppHelper::get_settings();

			if ( ! is_array( $items ) ) {
				$items = explode( ',', $items );
			}

			if ( $bulkaction === 'delete' ) {
				if ( ! current_user_can( 'frm_delete_entries' ) ) {
					$errors[] = $frm_settings->admin_permission;
				} elseif ( is_array( $items ) ) {
					foreach ( $items as $item_id ) {
						FrmEntry::destroy( $item_id );
					}
				}
			} elseif ( $bulkaction === 'csv' ) {
				FrmAppHelper::permission_check( 'frm_view_entries' );

				$form_id = $params['form'];
				if ( ! $form_id ) {
					$form = FrmForm::get_published_forms( array(), 1 );
					if ( ! empty( $form ) ) {
						$form_id = $form->id;
					} else {
						$errors[] = __( 'No form was found', 'formidable-pro' );
					}
				}

				if ( $form_id && is_array( $items ) ) {
					echo '<script type="text/javascript">window.onload=function(){location.href="' . esc_url_raw( admin_url( 'admin-ajax.php?form=' . $form_id . '&action=frm_entries_csv&item_id=' . implode( ',', $items ) ) ) . '";}</script>';
				}
			}
		}
		FrmEntriesController::display_list( '', $errors );
	}

	/* Front End CRUD */

	/*
	 * Determine if this is a new entry or if we're editing an old one
	 */
	public static function maybe_editing( $continue, $form_id, $action = 'new' ) {
		$form_submitted = FrmAppHelper::get_param( 'form_id', '', 'get', 'absint' );
		if ( $action === 'new' || $action === 'preview' ) {
			$continue = true;
		} else {
			$continue = ( $form_submitted && (int) $form_id != $form_submitted );
		}

		return $continue;
	}

	public static function check_draft_status( $values, $id ) {
		if ( ! FrmProEntry::is_draft( $id ) || FrmProEntry::is_draft_status( $values['is_draft'] ) ) {
			// if entry draft status is unchanged
			return $values;
		}

		//add the create hooks since the entry is switching draft status
		add_action( 'frm_after_update_entry', 'FrmProEntriesController::add_published_hooks', 2, 2 );

		/**
		 * Do something when a draft entry is officially saved
		 *
		 * @since 3.0.08
		 */
		do_action(
			'frm_after_complete_entry_processed',
			array(
				'entry_id' => $id,
				'form'     => $values['form_id'],
			)
		);

		//change created timestamp
		$values['created_at'] = $values['updated_at'];

		return $values;
	}

	public static function remove_draft_hooks( $entry_id ) {
		if ( ! FrmProEntry::is_draft( $entry_id ) ) {
			return;
		}

		// don't let sub entries remove these hooks
		$entry = FrmEntry::getOne( $entry_id );
		if ( $entry->parent_item_id ) {
			return;
		}

		//remove hooks if saving as draft
		remove_action( 'frm_after_create_entry', 'FrmProEntriesController::set_cookie', 20 );
		remove_action( 'frm_after_create_entry', 'FrmFormActionsController::trigger_create_actions', 20 );
		add_action( 'frm_after_create_entry', 'FrmProFormActionsController::trigger_draft_actions', 10, 2 );

		// trigger after draft save hook
		do_action(
            'frm_after_draft_entry_processed',
            array(
				'entry_id' => $entry_id,
				'form'     => $entry->form_id,
            ) 
        );
	}

	/**
	 * Add the create hooks since the entry is switching draft status.
	 *
	 * @param string $entry_id
	 * @param int    $form_id
	 * @return void
	 */
	public static function add_published_hooks( $entry_id, $form_id ) {
		$is_child = (bool) FrmDb::get_var( 'frm_forms', array( 'id' => $form_id ), 'parent_form_id' );
		do_action( 'frm_after_create_entry', $entry_id, $form_id, compact( 'is_child' ) );
		do_action( 'frm_after_create_entry_' . $form_id, $entry_id, compact( 'is_child' ) );
		remove_action( 'frm_after_update_entry', 'FrmProEntriesController::add_published_hooks', 2 );
		remove_action( 'frm_after_update_entry', 'FrmProFormActionsController::trigger_update_actions', 10 );
	}

	public static function process_update_entry( $params, $errors, $form, $args ) {
		self::maybe_autosave_on_page_turn( $errors, $form );

		/**
		 * Filter hook enables to manipulate the params and make changes to the
		 * entry submission to force the entry to create or update.
		 *
		 * @since 6.5.3
		 *
		 * @param array $params Parameters from FrmForm::get_params.
		 * @param object $form Form.
		 */
		$params = apply_filters( 'frm_pro_process_update_entry', $params, $form );

		if ( $params['action'] === 'create' && FrmFormsController::just_created_entry( $form->id ) ) {
			self::success_after_create( $params, $form );
		} elseif ( $params['action'] === 'update' && empty( $errors ) ) {
			if ( self::entry_previously_saved( $params ) ) {
				return;
			}

			//check if user is allowed to update
			if ( ! FrmProEntriesHelper::user_can_edit( (int) $params['id'], $form ) ) {
				$frm_settings = FrmAppHelper::get_settings();
				wp_die( do_shortcode( $frm_settings->login_msg ) );
			}

			//update, but don't check for confirmation if saving draft
			if ( FrmProFormsHelper::saving_draft() ) {
				FrmEntry::update( $params['id'], $_POST );
				do_action(
                    'frm_after_draft_entry_processed',
                    array(
						'entry_id' => $params['id'],
						'form'     => $form,
                    ) 
                );
				return;
			}

			//don't update if going back
			if ( isset( $_POST[ 'frm_page_order_' . $form->id ] ) || FrmProFormsHelper::going_to_prev( $form->id ) ) {
				return;
			}

			FrmEntry::update( $params['id'], $_POST );

			$success_args = array(
				'action' => $params['action'],
				'id'     => $params['id'],
			);
			if ( $params['action'] !== 'create' && FrmProEntriesHelper::is_new_entry( $params['id'] ) ) {
				$success_args['action'] = 'create';
			}

			self::trigger_redirect( $form, $success_args, $args );
		} elseif ( $params['action'] === 'destroy' ) {
			//if the user who created the entry is deleting it
			self::ajax_destroy( $form->id, false, false );
		}
	}

	private static function success_after_create( $params, $form ) {
		global $frm_vars;
		$entry_id     = $frm_vars['created_entries'][ $form->id ]['entry_id'];
		$params['id'] = $entry_id;
		self::set_cookie( $entry_id, $form->id );
	}

	/**
	 * @param array $params
	 * @return void
	 */
	private static function trigger_redirect( $form, $params, $args ) {
		$is_autosave = FrmAppHelper::get_post_param( 'frm_autosaving', '', 'sanitize_text_field' );
		if ( $is_autosave == 1 ) {
			return;
		}

		FrmFormsController::maybe_trigger_redirect( $form, $params, $args );
	}

	private static function &entry_previously_saved( $params ) {
		global $frm_vars;
		$saved = ( isset( $frm_vars['saved_entries'] ) && in_array( (int) $params['id'], (array) $frm_vars['saved_entries'] ) );
		return $saved;
	}

	/**
	 * @since 2.3
	 */
	private static function maybe_autosave_on_page_turn( $errors, $form ) {
		if ( ! empty( $errors ) || ! is_user_logged_in() ) {
			return;
		}

		// the entry is already getting saved
		$last_page = ! isset( $_POST[ 'frm_page_order_' . $form->id ] );
		if ( $last_page || FrmProFormsHelper::saving_draft() ) {
			return;
		}

		$drafts_allowed = FrmForm::get_option(
            array(
				'form'   => $form,
				'option' => 'save_draft',
            ) 
        );
		if ( $drafts_allowed ) {
			self::autosave_on_page_turn( $form );
		}
	}

	/**
	 * @since 2.3
	 */
	private static function autosave_on_page_turn( $form ) {
		$params                     = $_POST;
		$params['frm_saving_draft'] = true;
		$params['is_draft']         = 1;

		/**
		 * Filter hook enables to manipulate the params and make changes to the
		 * autosave_on_page_turn to force the draft entry to update.
		 *
		 * @since 6.5.4
		 *
		 * @param array $params Parameters from FrmForm::get_params.
		 * @param object $form Form.
		 */
		$params = apply_filters( 'frm_pro_autosave_on_page_turn', $params, $form );

		if ( ! isset( $params['action'] ) ) {
			$params['action'] = $params['frm_action'];
		}

		if ( $params['action'] === 'create' || $params['action'] === 'frm_entries_create' ) {
			global $frm_vars;
			$_POST['frm_autosaving'] = 1;
			if ( empty( $frm_vars['created_entries'][ $form->id ] ) ) {
				$frm_vars['created_entries'][ $form->id ] = array( 'errors' => array() );
			}
			$frm_vars['created_entries'][ $form->id ]['entry_id'] = FrmEntry::create( $params );

		} elseif ( $params['action'] === 'update' || $params['action'] === 'frm_entries_update' ) {
			if ( ! FrmProEntriesHelper::user_can_edit( absint( $params['id'] ), $form ) ) {
				return;
			}

			$entry = FrmEntry::getOne( $params['id'] );
			if ( $entry->is_draft ) {
				$_POST['frm_autosaving'] = 1;
				FrmEntry::update( $entry->id, $params );
			}
		}
	}

	public static function edit_update_form( $params, $fields, $form, $title, $description ) {
		global $frm_vars, $post;

		FrmProFormState::set_initial_value( 'title', $title );
		FrmProFormState::set_initial_value( 'description', $description );

		if ( $post instanceof WP_Post ) {
			FrmProFormState::set_initial_value( 'global_post', $post->ID );
		}

		self::load_wp_editor_assets( $fields, $form );

		$continue = true;
		$args     = array(
			'form'             => $form,
			'fields'           => $fields,
			'show_title'       => $title,
			'show_description' => $description,
			'params'           => $params,
		);

		if ( 'edit' === $params['action'] ) {
			// For initial form load when editing
			self::maybe_show_front_end_editable_entry_on_first_load( $args, $continue );
		} elseif ( 'update' === $params['action'] && $params['posted_form_id'] == $form->id ) {
			// For next/submit/previous/save draft clicks
			self::show_front_end_editable_entry_after_submit_click( $args, $continue );
		} elseif ( 'destroy' === $params['action'] ) {
			self::front_destroy_entry( $form );
		} elseif ( ! empty( $frm_vars['editing_entry'] ) ) {
			// For entry_id=x, initial load only
			self::maybe_show_front_end_editable_entry_with_entry_id_param( $args, $continue );
		} else {
			self::allow_front_create_entry( $form, $continue );
		}

		self::check_form_status_options( $form, $continue );

		self::remove_opposite_continue_to_new_filter( $continue );
	}

	/**
	 * Loads necessary WP Editor assets for Rich Text fields with
	 * AJAX-enabled forms.
	 *
	 * @since 4.06.02
	 *
	 * @param array   $fields Array of fields and its properties.
	 * @param object  $form   Object representing the current Form.
	 */
	private static function load_wp_editor_assets( $fields, $form ) {
		// This is only needed when AJAX submission is enabled for the form.
		if ( ! FrmProForm::is_ajax_on( $form ) || FrmAppHelper::is_admin() ) {
			return;
		}

		foreach ( $fields as $field ) {

			// Check for an `rte` (Rich-text) field.
			$field_type = FrmField::get_option( $field, 'original_type' );
			if ( 'rte' === $field_type ) {
				$field_obj = FrmFieldFactory::get_field_type( 'rte' );
				$field_obj->load_default_rte_script();
				break;
			}
		}
	}

	/**
	 * @since 3.04
	 *
	 * @param object $form
	 */
	private static function check_form_status_options( $form, &$continue ) {
		if ( $continue && ! FrmProForm::is_open( $form ) ) {
			$continue = false;
			if ( isset( $form->options['closed_msg'] ) ) {
				$message = $form->options['closed_msg'];
			} else {
				$default_opts = FrmProFormsHelper::get_default_opts();
				$message      = $default_opts['closed_msg'];
			}
			$message = do_shortcode( $message );
			echo wp_kses_post( $message );
		}
	}

	private static function remove_opposite_continue_to_new_filter( $continue ) {
		if ( $continue === true ) {
			remove_filter( 'frm_continue_to_new', '__return_false', 15 );
			add_filter( 'frm_continue_to_new', '__return_true', 15 );
		} else {
			remove_filter( 'frm_continue_to_new', '__return_true', 15 );
			add_filter( 'frm_continue_to_new', '__return_false', 15 );
		}
	}

	/**
	 * Load form and entry for editing if user has permission and entry exists
	 * Used on initial load only, not on page turns
	 *
	 * @since 2.01.0
	 *
	 * @param array $args (always contains 'form', 'fields' , 'show_title', and 'show_description')
	 * @param bool $continue
	 */
	private static function maybe_show_front_end_editable_entry_on_first_load( $args, &$continue ) {
		global $wpdb, $frm_vars;

		$entry_id  = ! empty( $frm_vars['editing_entry'] ) ? $frm_vars['editing_entry'] : '';
		$entry_key = FrmAppHelper::get_param( 'entry', $entry_id, 'get', 'sanitize_title' );

		$query = array( 'it.form_id' => $args['form']->id );

		if ( $entry_key ) {
			$query_column = is_numeric( $entry_key ) ? 'it.id' : 'it.item_key';
			$query[1]     = array( $query_column => $entry_key );

			$in_form = FrmDb::get_var( $wpdb->prefix . 'frm_items it', $query );

			if ( ! $in_form ) {
				$entry_key = false;
				unset( $query[1] );
			}
			unset( $in_form );
		}

		$entry = FrmProEntriesHelper::user_can_edit( $entry_key, $args['form'] );
		if ( ! $entry ) {
			return;
		}

		if ( ! is_array( $entry ) ) {
			$entry = FrmEntry::getAll( $query, '', 1, true );
		}

		if ( ! empty( $entry ) ) {
			global $frm_vars;
			$entry                     = reset( $entry );
			$frm_vars['editing_entry'] = $entry->id;

			self::show_entry_on_first_load_for_editing( $entry, $args );
			$continue = false;
		}
	}

	/**
	 * Load the form for editing when entry_id=x shortcode is used
	 *
	 * @param array $args (contains form, fields, show_title, and show_description)
	 * @param bool $continue
	 */
	private static function maybe_show_front_end_editable_entry_with_entry_id_param( $args, &$continue ) {
		global $frm_vars;

		$entry_id = false;
		if ( is_numeric( $frm_vars['editing_entry'] ) ) {
			// For entry_id=x in shortcode
			$entry_id = $frm_vars['editing_entry'];
		} elseif ( $frm_vars['editing_entry'] === 'last' ) {
			// For entry_id="last" in shortcode

			// Get the last entry submitted by the current user
			$user_ID  = get_current_user_id();
			$where    = array(
				'user_id' => $user_ID,
				'form_id' => $args['form']->id,
			);
			$entry_id = FrmDb::get_col( 'frm_items', $where, 'id', array(), ' LIMIT 1' );
		}

		if ( ! $entry_id ) {
			return;
		}

		if ( ! FrmProEntriesHelper::user_can_edit( $entry_id, $args['form'] ) ) {
			return;
		}

		$frm_vars['editing_entry'] = $entry_id;
		$entry                     = FrmEntry::getOne( $entry_id, true );

		self::show_entry_on_first_load_for_editing( $entry, $args );

		$continue = false;
	}

	private static function front_destroy_entry( $form ) {
		//if the user who created the entry is deleting it
		self::ajax_destroy( $form->id, false );
	}

	/**
	 * Show the form/entry after clicking Next, Previous, Update, Submit, or Save Draft
	 *
	 * @since 2.01.0
	 *
	 * @param array $args (always contains 'form', 'fields' , 'show_title', 'show_description', and 'params')
	 * @param bool $continue
	 */
	private static function show_front_end_editable_entry_after_submit_click( $args, &$continue ) {
		global $frm_vars;

		// Initialize basic entry info
		$entry_id                  = $args['params']['id'];
		$entry                     = FrmEntry::getOne( $entry_id, true );
		$frm_vars['editing_entry'] = $entry_id;

		// Add to args
		$args['errors'] = self::get_posted_form_errors( $frm_vars, $args['form'] );
		$field_args     = array(
			'parent_form_id'   => $args['form']->id,
			'fields'           => $args['fields'],
			'save_draft_click' => FrmProFormsHelper::saving_draft(),
		);
		$args['values'] = self::setup_entry_values_for_editing( $entry, $field_args );
		self::add_submit_value_to_values( $args['form'], $args['values'] );
		$args['submit_text'] = self::get_submit_button_text_for_editing_entry( $entry, $args['values'], $args['form'] );

		if ( self::update_button_was_clicked( $args ) ) {
			// If Update/Submit was clicked
			if ( FrmProEntriesHelper::user_can_edit( $entry_id, $args['form'] ) ) {
				self::do_on_update_settings( $entry, $args );
			} else {
				// entry is no longer editable after draft is saved
				self::do_on_create_settings( $entry, $args );
			}
		} else {
			// If Save Draft, Next, or Previous was clicked
			$args['show_form']    = true;
			$args['jump_to_form'] = true;
			$args['conf_message'] = self::maybe_get_save_draft_message( $args['form'], $entry_id );
			self::show_front_end_form_with_entry( $entry, $args );
		}

		$continue = false;
	}

	/**
	 * Adds submit value in $form object to $values array.
	 *
	 * @since 4.06.02
	 *
	 * @param object $form   Form object.
	 * @param array  $values Array of values, used to set submit button text, among other things.
	 */
	private static function add_submit_value_to_values( $form, &$values ) {
		if ( isset( $form->submit_value ) ) {
			$values['submit_value'] = $form->submit_value;
			return;
		}

		if ( isset( $form->options['submit_value'] ) ) {
			$values['submit_value'] = $form->options['submit_value'];
		}
	}

	/**
	 * Show the message + form after the first Save Draft click (on front-end only)
	 * Replaces FrmProEntriesController::show_responses
	 *
	 * @since 2.01.0
	 *
	 * @param int $entry_id
	 * @param array $args
	 */
	public static function show_form_after_first_save_draft_click( $entry_id, $args ) {
		global $frm_vars;
		$frm_vars['editing_entry'] = $entry_id;

		$entry                = FrmEntry::getOne( $entry_id );
		$field_args           = array(
			'parent_form_id'   => $args['form']->id,
			'fields'           => $args['fields'],
			'save_draft_click' => true,
		);
		$args['values']       = self::setup_entry_values_for_editing( $entry, $field_args );
		$args['submit_text']  = self::get_submit_button_text_for_editing_entry( $entry, $args['values'], $args['form'] );
		$args['show_form']    = true;
		$args['jump_to_form'] = true;
		$args['errors']       = array();

		self::show_front_end_form_with_entry( $entry, $args );
	}

	/**
	 * Display a success message and possibly the form after single editable entry is submitted
	 * Replaces FrmProEntriesController::show_responses
	 *
	 * @since 2.01.0
	 * @param int $entry_id
	 * @param array $args (always contains 'form', 'fields', 'show_title', and 'show_description')
	 */
	public static function show_form_after_single_editable_entry_submission( $entry_id, $args ) {
		self::show_form_after_first_save_draft_click( $entry_id, $args );
	}

	/**
	 * Get errors when validating server-side
	 *
	 * @since 2.01.0
	 *
	 * @param array $frm_vars
	 * @param object $form
	 * @return array
	 */
	private static function get_posted_form_errors( $frm_vars, $form ) {
		if ( isset( $frm_vars['created_entries'][ $form->id ] ) ) {
			$errors = $frm_vars['created_entries'][ $form->id ]['errors'];
		} else {
			$errors = false;
		}
		return $errors;
	}

	/**
	 * If a draft is being saved, get the save draft message
	 *
	 * @since 2.01.0
	 *
	 * @param object $form
	 * @param int $entry_id
	 * @return string $message
	 */
	private static function maybe_get_save_draft_message( $form, $entry_id ) {
		$message = '';
		if ( FrmProFormsHelper::saving_draft() ) {
			$success_args = array( 'action' => self::get_current_entry_action( $entry_id ) );
			$message      = self::confirmation( 'message', $form, $form->options, $entry_id, $success_args );
		}

		return $message;
	}

	/**
	 * Check to see if user is allowed to create another entry
	 *
	 * @param bool $continue
	 * @return void
	 */
	private static function allow_front_create_entry( $form, &$continue ) {
		$errors = array();
		if ( FrmProFormsHelper::visitor_already_submitted( $form, $errors ) ) {
			echo do_shortcode( reset( $errors ) );
			$continue = false;
		}
	}

	/**
	 * This function should only be used when editing an entry (on front-end only)
	 * Replaces FrmProEntriesController::show_responses
	 *
	 * @since 2.01.0
	 * @param object $entry
	 * @param array $args (always contains 'form', 'fields', 'show_title', and 'show_description')
	 */
	private static function show_entry_on_first_load_for_editing( $entry, $args ) {
		$field_args          = array(
			'parent_form_id' => $args['form']->id,
			'fields'         => $args['fields'],
		);
		$args['values']      = self::setup_entry_values_for_editing( $entry, $field_args );
		$args['submit_text'] = self::get_submit_button_text_for_editing_entry( $entry, $args['values'], $args['form'] );
		$args['errors']      = array();
		$args['show_form']   = true;

		self::show_front_end_form_with_entry( $entry, $args );
	}

	/**
	 * Update the global $frm_vars so CSS and JavaScript gets loaded correctly
	 *
	 * @since 2.01.0
	 *
	 * @param array $args always contains 'form', 'fields', 'show_title', 'show_description', 'values', 'errors',
	 *     'submit_text', and 'show_form'
	 */
	private static function update_global_vars_for_entry_editing( &$args ) {
		if ( ! empty( $args['form']->options['show_form'] ) ) {
			// Do nothing because JavaScript is already loaded
			// Make sure Formidable CSS is loaded
			global $frm_vars;
			if ( $args['values']['custom_style'] ) {
				$frm_vars['load_css'] = true;
			}
		} else {
			self::load_form_scripts(
				array(
					'style' => $args['values']['custom_style'],
				)
			);
		}
	}

	/**
	 * Set up all the necessary data for editing an entry
	 * This is now used in place of FrmAppHelper::setup_edit_vars when editing entries
	 *
	 * @since 2.01.0
	 *
	 * @param object $entry
	 * @param array $args (always contains 'parent_form_id' and 'fields'; if repeating, will contain 'parent_field_id',
	 *     'key_pointer' and 'repeating'; if embedded, will contain in_embed_form)
	 * @return array $values
	 */
	public static function setup_entry_values_for_editing( $entry, $args ) {
		$values = array(
			'id'     => $entry->id,
			'fields' => array(),
		);

		foreach ( array( 'name', 'description' ) as $var ) {
			$default_val    = isset( $entry->{$var} ) ? $entry->{$var} : '';
			$values[ $var ] = FrmAppHelper::get_param( $var, $default_val, 'get', 'wp_kses_post' );
			FrmAppHelper::sanitize_value( 'wp_specialchars_decode', $values[ $var ] );

			unset( $var, $default_val );
		}

		$values['description'] = FrmAppHelper::use_wpautop( $values['description'] );

		$fields = $args['fields'];
		unset( $args['fields'] );
		$values['fields'] = FrmProFieldsController::setup_field_data_for_editing_entry( $entry, $fields, $args );

		FrmProFormsController::setup_form_data_for_editing_entry( $entry, $values );

		$values = FrmEntriesHelper::setup_edit_vars( $values, $entry );

		return $values;
	}

	/**
	 * Get the text on the Submit button when editing an entry
	 * Remember the Submit button may be the Next, Update, or Submit button
	 *
	 * @since 2.01.0
	 *
	 * @param object $entry
	 * @param array $values
	 * @param object $form
	 * @return string $submit_text
	 */
	private static function get_submit_button_text_for_editing_entry( $entry, $values, $form ) {
		global $frm_vars;

		if ( isset( $frm_vars['next_page'][ $form->id ] ) ) {
			// If there is a "Next" page, get the Next button text
			$submit_text = $frm_vars['next_page'][ $form->id ];
			$submit_text = $submit_text->name;
		} elseif ( $entry->is_draft ) {
			// If entry is a draft, get the create button text
			if ( isset( $values['submit_value'] ) ) {
				$submit_text = $values['submit_value'];
			} else {
				$frmpro_settings = FrmProAppHelper::get_settings();
				$submit_text     = $frmpro_settings->submit_value;
			}
		} elseif ( isset( $values['edit_value'] ) ) {
			// If entry is not a draft, get the edit button text
			$submit_text = $values['edit_value'];
		} else {
			$frmpro_settings = FrmProAppHelper::get_settings();
			$submit_text     = $frmpro_settings->update_value;
		}

		return $submit_text;
	}

	/**
	 * Determine whether the "Update" button was clicked
	 *
	 * @since 2.01.0
	 *
	 * @param array $args (always contains 'form', 'fields' , 'show_title', 'show_description', 'params', 'values',
	 *     'errors', and 'submit_text')
	 * @return bool $update_button_was_clicked
	 */
	private static function update_button_was_clicked( &$args ) {
		global $frm_vars;
		$update_button_was_clicked = false;

		$form = $args['form'];

		if ( ! isset( $_POST['item_meta'] ) || $args['errors'] ) {
			// There is no item meta or there are errors, so don't update entry

		} elseif ( isset( $frm_vars['prev_page'][ $form->id ] ) || FrmProFormsHelper::going_to_prev( $form->id ) ) {
			// Back or Next was clicked

		} elseif ( $form->id != FrmAppHelper::get_param( 'form_id', '', 'get', 'absint' ) ) {
			// This form is NOT the one being submitted/updated

		} elseif ( FrmProFormsHelper::saving_draft() ) {
			// Save Draft was clicked

		} else {
			$update_button_was_clicked = true;
		}

		return $update_button_was_clicked;
	}

	/**
	 * Determine whether the form is displayed after edit
	 *
	 * @since 2.01.0
	 * @since 6.1.2 This method is public.
	 *
	 * @param object $form
	 * @return bool $show_form
	 */
	public static function is_form_displayed_after_edit( $form ) {
		$show_form = isset( $form->options['show_form'] ) ? $form->options['show_form'] : true;
		$show_form = apply_filters( 'frm_show_form_after_edit', $show_form, $form );
		return $show_form;
	}

	/**
	 * Determine whether the current entry is being created or updated
	 *
	 * @since 2.01.0
	 *
	 * @param int $entry_id
	 * @return bool
	 */
	private static function get_current_entry_action( $entry_id ) {
		return FrmProEntriesHelper::is_new_entry( $entry_id ) ? 'create' : 'update';
	}

	/**
	 * Get the confirmation method selected for "On Update"
	 *
	 * @since 2.01.0
	 *
	 * @param object $form
	 * @param array $success_args (always includes 'action' )
	 * @return string
	 */
	private static function get_conf_method_after_save( $form, $success_args ) {
		return apply_filters( 'frm_success_filter', 'message', $form, $success_args['action'] );
	}

	/**
	 * Do the "On Update" settings (redirect to URL, show a message, or show content from another page)
	 *
	 * @since 2.01.0
	 *
	 * @param object $entry
	 * @param array $args
	 * $args always contains 'form', 'fields' , 'show_title', 'show_description', 'params', 'values', 'errors', and
	 *     'submit_text'
	 */
	private static function do_on_update_settings( $entry, $args ) {
		$success_args = array( 'action' => self::get_current_entry_action( $entry->id ) );
		$conf_method  = self::get_conf_method_after_save( $args['form'], $success_args );

		$on_submit_args = $args;

		$on_submit_args['entry_id']    = $entry->id;
		$on_submit_args['action']      = $success_args['action'];
		$on_submit_args['conf_method'] = $conf_method;

		FrmFormsController::run_on_submit_actions( $on_submit_args );
	}

	/**
	 * Show the editable form/entry on the front-end.
	 *
	 * @since 2.01.0
	 * @since 6.1.2 This method is public.
	 *
	 * @param object $entry
	 * @param array $args
	 * $args always contains 'form', 'fields', 'show_title', 'show_description', 'values', 'errors', 'submit_text', and
	 *     'show_form'
	 * @return void
	 */
	public static function show_front_end_form_with_entry( $entry, $args ) {
		self::update_global_vars_for_entry_editing( $args );

		// Setup variables for view (maybe do away with this and create a new view)
		$values       = $args['values'];
		$user_ID      = get_current_user_id();
		$frm_settings = FrmAppHelper::get_settings();
		$title        = $args['show_title'];
		$description  = $args['show_description'];
		$id           = $entry->id;
		$errors       = $args['errors'];
		$form         = $args['form'];
		$submit       = $args['submit_text'];
		$show_form    = $args['show_form'];
		$jump_to_form = isset( $args['jump_to_form'] ) ? $args['jump_to_form'] : false;

		$message = isset( $args['conf_message'] ) ? $args['conf_message'] : false;
		if ( $message ) {
			$message = apply_filters( 'frm_main_feedback', $message, $form, $id );
		}

		if ( is_callable( 'FrmFormsController::maybe_load_css' ) ) {
			global $frm_vars;
			FrmFormsController::maybe_load_css( $form, $values['custom_style'], $frm_vars['load_css'] );
		}

		require FrmProAppHelper::plugin_path() . '/classes/views/frmpro-entries/edit-front.php';

		add_filter( 'frm_continue_to_new', 'FrmProEntriesController::maybe_editing', 10, 3 );
	}

	/**
	 * @param stdClass $entry
	 * @param array $args
	 * @return void
	 */
	private static function do_on_create_settings( $entry, $args ) {
		$args['action'] = isset( $args['action'] ) ? $args['action'] : 'create';
		$conf_method    = apply_filters( 'frm_success_filter', 'message', $args['form'], $args['action'] );

		$args['entry_id']    = $entry->id;
		$args['title']       = $args['show_title'];
		$args['description'] = $args['show_description'];
		$args['conf_method'] = $conf_method;

		if ( ! is_array( $conf_method ) ) {
			FrmFormsController::run_success_action( $args );
		} elseif ( 1 === count( $conf_method ) ) {
			FrmFormsController::run_single_on_submit_action( $args, reset( $conf_method ) );
		} else {
			FrmFormsController::run_multi_on_submit_actions( $args );
		}
	}

	/**
	 * @return void
	 */
	public static function ajax_submit_button() {
		global $frm_vars;

		if ( ! empty( $frm_vars['novalidate'] ) ) {
			echo ' formnovalidate="formnovalidate"';
		}
	}

	/**
	 * @since 6.0 This might return array of On Submit actions.
	 */
	public static function get_confirmation_method( $method, $form, $action = 'create' ) {
		if ( FrmProFormsHelper::saving_draft() ) {
			return 'message';
		}

		$entry_id = self::get_entry_id_for_confirmation_action( $form, $action );

		if ( ! $entry_id ) {
			return $method;
		}

		if ( method_exists( 'FrmFormsController', 'get_met_on_submit_actions' ) ) {
			$on_submit_actions = FrmFormsController::get_met_on_submit_actions(
				array(
					'form'     => $form,
					'entry_id' => $entry_id,
				),
				$action
			);

			if ( $on_submit_actions ) {
				return $on_submit_actions;
			}
		}

		$opt    = $action === 'update' ? 'edit_action' : 'success_action';
		$method = ! empty( $form->options[ $opt ] ) ? $form->options[ $opt ] : $method;

		return $method;
	}

	/**
	 * Gets entry ID to process confirmation action.
	 *
	 * @since 6.2
	 *
	 * @param object $form   Form object.
	 * @param string $action Accepts `create` or `update`.
	 * @return false|int
	 */
	private static function get_entry_id_for_confirmation_action( $form, $action = 'create' ) {
		if ( 'update' === $action ) {
			$entry_id = FrmAppHelper::get_param( FrmAppHelper::doing_ajax() ? 'id' : 'entry', 0, 'get', 'intval' );
			if ( ! $entry_id ) {
				$entry_id = FrmAppHelper::get_param( 'id', 0, 'post', 'intval' );
			}
			return $entry_id;
		}

		global $frm_vars;
		if ( isset( $frm_vars['created_entries'][ $form->id ]['entry_id'] ) ) {
			return $frm_vars['created_entries'][ $form->id ]['entry_id'];
		}

		return false;
	}

	/**
	 * @param string   $method
	 * @param stdClass $form
	 * @param int      $entry_id
	 * @param array    $args
	 */
	public static function confirmation( $method, $form, $form_options, $entry_id, $args = array() ) {
		$opt = ! isset( $args['action'] ) || $args['action'] === 'create' ? 'success' : 'edit';

		if ( ( $method === 'page' && is_numeric( $form_options[ $opt . '_page_id' ] ) ) || $method === 'redirect' ) {
			$pass_args                = $args;
			$pass_args['conf_method'] = $method;
			$pass_args['form']        = $form;
			$pass_args['entry_id']    = $entry_id;
			FrmFormsController::run_success_action( $pass_args );
		} else {
			$frm_settings    = FrmAppHelper::get_settings();
			$frmpro_settings = FrmProAppHelper::get_settings();

			$msg     = $opt === 'edit' ? $frmpro_settings->edit_msg : $frm_settings->success_msg;
			$message = isset( $form->options[ $opt . '_msg' ] ) ? $form->options[ $opt . '_msg' ] : $msg;

			// Replace $message with save draft message if we are saving a draft
			FrmProFormsHelper::save_draft_msg( $message, $form );

			$class = 'frm_message';
			return FrmFormsHelper::get_success_message( compact( 'message', 'form', 'entry_id', 'class' ) );
		}
	}

	public static function delete_entry( $post_id ) {
		// Check that installation has occurred
		$db_version = get_option( 'frm_db_version' );
		if ( ! $db_version ) {
			return;
		}

		$entry = FrmDb::get_row( 'frm_items', array( 'post_id' => $post_id ), 'id' );
		self::maybe_delete_entry( $entry );
	}

	public static function trashed_post( $post_id ) {
		$form_id = get_post_meta( $post_id, 'frm_form_id', true );
		if ( ! $form_id ) {
			return;
		}

		$display = FrmProDisplay::get_auto_custom_display( array( 'form_id' => $form_id ) );
		if ( $display ) {
			update_post_meta( $post_id, 'frm_display_id', $display->ID );
		} else {
			delete_post_meta( $post_id, 'frm_display_id' );
		}
	}

	/**
	 * Allow extra parameters in the frm-show-entry shortcode
	 *
	 * @since 3.01.01
	 */
	public static function show_entry_defaults( $atts ) {
		$atts['date_format']        = '';
		$atts['show_image']         = false;
		$atts['size']               = 'full';
		$atts['image_option_size']  = 'thumbnail';
		$atts['show_image_options'] = true;
		$atts['show_filename']      = false;
		$atts['add_link']           = false;
		$atts['summary']            = false; // whether we're trying to display the summary field
		return $atts;
	}

	/**
	 * @param string        $post_type
	 * @param false|WP_Post $post
	 * @return void
	 */
	public static function create_entry_from_post_box( $post_type, $post = false ) {
		if ( ! $post || ! isset( $post->ID ) || $post_type === 'attachment' || $post_type === 'link' ) {
			return;
		}

		global $wpdb, $frm_vars;

		//don't show the meta box if there is already an entry for this post
		$post_entry = FrmDb::get_var( $wpdb->prefix . 'frm_items', array( 'post_id' => $post->ID ) );
		if ( $post_entry ) {
			return;
		}

		//don't show meta box if no forms are set up to create this post type
		$actions = FrmFormAction::get_action_for_form( 0, 'wppost' );
		if ( ! $actions ) {
			return;
		}

		$form_ids = array();
		foreach ( $actions as $action ) {
			if ( $action->post_content['post_type'] == $post_type && $action->menu_order ) {
				$form_ids[] = $action->menu_order;
			}
		}

		if ( empty( $form_ids ) ) {
			return;
		}

		$forms = FrmDb::get_results( 'frm_forms', array( 'id' => $form_ids ), 'id, name' );

		$frm_vars['post_forms'] = $forms;

		if ( current_user_can( 'frm_create_entries' ) ) {
			add_meta_box( 'frm_create_entry', __( 'Create Entry in Form', 'formidable-pro' ), 'FrmProEntriesController::render_meta_box_content', null, 'side' );
		}
	}

	public static function render_meta_box_content( $post ) {
		global $frm_vars;
		$i = 1;

		echo '<p>';
		foreach ( (array) $frm_vars['post_forms'] as $form ) {
			if ( $i != 1 ) {
				echo ' | ';
			}

			++$i;
			echo '<a href="javascript:frmCreatePostEntry(' . (int) $form->id . ',' . (int) $post->ID . ')">' . esc_html( FrmAppHelper::truncate( $form->name, 15 ) ) . '</a>';
			unset( $form );
		}

		echo '</p>';
	}

	/**
	 * Create a Formidable entry with a Post action from a Post.
	 *
	 * @param false|int|string $id
	 * @param false|int|string $post_id
	 */
	public static function create_post_entry( $id = false, $post_id = false ) {
		if ( FrmAppHelper::doing_ajax() ) {
			check_ajax_referer( 'frm_ajax', 'nonce' );
		}

		if ( ! $id ) {
			$id = FrmAppHelper::get_post_param( 'id', '', 'absint' );
		}

		if ( ! $post_id ) {
			$post_id = FrmAppHelper::get_post_param( 'post_id', '', 'absint' );
		}

		if ( ! is_numeric( $id ) || ! is_numeric( $post_id ) ) {
			wp_die();
		}

		$post = get_post( $post_id );

		$created_at         = $post->post_date_gmt;
		$current_mysql_time = current_time( 'mysql', 1 );
		if ( '0000-00-00 00:00:00' === $post->post_date_gmt ) {
			// A draft post does not have a post date set so use the current time instead for the entry.
			$created_at = $current_mysql_time;
		}

		global $wpdb;
		$values = array(
			'description' => __( 'Copied from Post', 'formidable-pro' ),
			'form_id'     => $id,
			'created_at'  => $created_at,
			'updated_at'  => $current_mysql_time,
			'name'        => $post->post_title,
			'item_key'    => FrmAppHelper::get_unique_key( $post->post_name, $wpdb->prefix . 'frm_items', 'item_key' ),
			'user_id'     => $post->post_author,
			'post_id'     => $post->ID,
		);

		$results = $wpdb->insert( $wpdb->prefix . 'frm_items', $values );
		unset( $values );

		if ( ! $results ) {
			wp_die();
		}

		$entry_id      = $wpdb->insert_id;
		$user_id_field = FrmField::get_all_types_in_form( $id, 'user_id', 1 );

		if ( $user_id_field ) {
			$new_values = array(
				'meta_value' => $post->post_author,
				'item_id'    => $entry_id,
				'field_id'   => $user_id_field->id,
				'created_at' => current_time( 'mysql', 1 ),
			);

			$wpdb->insert( $wpdb->prefix . 'frm_item_metas', $new_values );
		}

		$display = FrmProDisplay::get_auto_custom_display(
            array(
				'form_id'  => $id,
				'entry_id' => $entry_id,
            ) 
        );
		if ( $display ) {
			update_post_meta( $post->ID, 'frm_display_id', $display->ID );
		}

		wp_die();
	}

	/**
	 * @return void
	 */
	public static function get_new_vars( $errors = array(), $form = false, $message = '' ) {
		$description = true;
		$title       = false;
		$form        = apply_filters( 'frm_pre_display_form', $form );
		if ( ! $form ) {
			wp_die( esc_html__( 'You are trying to access an entry that does not exist.', 'formidable-pro' ) );
			return;
		}

		$fields = FrmFieldsHelper::get_form_fields( $form->id, $errors );
		$values = $fields ? FrmEntriesHelper::setup_new_vars( $fields, $form ) : array();

		$submit = self::submit_label( compact( 'form', 'values' ) );

		FrmProPageField::add_pagination_hook( $form );

		require FrmProAppHelper::plugin_path() . '/classes/views/frmpro-entries/new.php';
	}

	/**
	 * @since 4.0
	 *
	 * @param array $atts
	 * @return void
	 */
	public static function save_new_entry_button( $atts ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo FrmProFormsHelper::get_draft_button( $atts['form'], 'button-secondary' );
		$submit = self::submit_label( $atts );
		submit_button( $submit, 'primary', '', false );
	}

	/**
	 * @since 4.0
	 *
	 * @param array $atts
	 */
	private static function submit_label( $atts ) {
		global $frm_vars;

		$form         = $atts['form'];
		$values       = $atts['values'];
		$frm_settings = FrmAppHelper::get_settings();

		if ( FrmProFormsHelper::is_final_page( $form->id ) ) {
			$submit = ( isset( $values['submit_value'] ) ? $values['submit_value'] : $frm_settings->submit_value );
			if ( isset( $atts['entry'] ) ) {
				if ( isset( $values['edit_value'] ) ) {
					$edit_label = $values['edit_value'];
				} else {
					$frmpro_settings = FrmProAppHelper::get_settings();
					$edit_label      = $frmpro_settings->update_value;
				}

				$submit = $atts['entry']->is_draft ? $submit : $edit_label;
			}
		} else {
			$submit = $frm_vars['next_page'][ $form->id ];
		}

		if ( is_object( $submit ) ) {
			$submit = $submit->name;
		}

		return $submit;
	}

	/**
	 * Changes the submit label.
	 *
	 * @since 6.9.1
	 *
	 * @param array  $values   Field array.
	 * @param object $field    Field object.
	 * @param int    $entry_id Entry ID.
	 * @return array
	 */
	public static function change_submit_label( $values, $field, $entry_id ) {
		if ( 'submit' !== $values['type'] || ! $entry_id ) {
			return $values;
		}

		$entry = FrmEntry::getOne( $entry_id );
		if ( ! $entry ) {
			return $values;
		}

		$form = FrmForm::getOne( $entry->form_id );
		if ( ! $form ) {
			return $values;
		}

		// Run this hook to copy submit field options into form options.
		$form        = apply_filters( 'frm_pre_display_form', $form );
		$form_values = FrmAppHelper::setup_edit_vars( $form, 'forms' );

		$values['name'] = self::submit_label(
			array(
				'values' => $form_values,
				'entry'  => $entry,
				'form'   => $form,
			)
		);

		return $values;
	}

	/**
	 * @since 4.0
	 *
	 * @param array $atts
	 */
	public static function edit_entry_button( $atts ) {
		if ( $atts['entry']->is_draft ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo FrmProFormsHelper::get_draft_button( $atts['form'], 'button-secondary' );
		}

		$submit = self::submit_label( $atts );
		echo '<button type="submit" class="button button-primary frm-button-primary">' . esc_html( $submit ) . '</button>';

		if ( ! FrmProFormsHelper::is_final_page( $atts['form']->id ) ) {
			echo '<button type="submit" class="frm-button-secondary frm_page_skip hide-no-js" data-page="">' . esc_html__( 'Save', 'formidable-pro' ) . '</button>';
		}
	}

	/**
	 * @param string $message
	 * @return void
	 */
	private static function get_edit_vars( $id, $errors = array(), $message = '' ) {
		$description = true;
		$title       = false;

		$entry = FrmEntry::getOne( $id, true );
		if ( ! $entry ) {
			if ( is_callable( 'FrmAppController::show_error_modal' ) ) {
				FrmAppController::show_error_modal(
					array(
						'title'      => __( 'You can\'t edit the entry', 'formidable-pro' ),
						'body'       => __( 'You are trying to edit an entry that does not exist', 'formidable-pro' ),
						'cancel_url' => admin_url( 'admin.php?page=formidable' ),
					)
				);
				return;
			}

			wp_die( esc_html__( 'You are trying to access an entry that does not exist.', 'formidable-pro' ) );
			return;
		}

		global $frm_vars;
		$frm_vars['editing_entry'] = $id;

		$form = FrmForm::getOne( $entry->form_id );
		$form = apply_filters( 'frm_pre_display_form', $form );

		$fields = FrmFieldsHelper::get_form_fields( $form->id, $errors );
		$values = FrmAppHelper::setup_edit_vars( $entry, 'entries', $fields );

		/**
		 * Allows modifying the list of fields in the form.
		 *
		 * @since 5.0
		 *
		 * @param array $fields Array of fields.
		 * @param array $args   The arguments. Contains `$args`.
		 */
		$values['fields'] = apply_filters( 'frm_fields_in_form_edit', $values['fields'], compact( 'form' ) );

		$submit = self::submit_label( compact( 'form', 'values', 'entry' ) );

		FrmFormsController::maybe_load_css( $form, $values['custom_style'], $frm_vars['load_css'] );

		FrmProPageField::add_pagination_hook( $form );

		$message_code = FrmAppHelper::simple_get( 'frm_entries_updated_message' );
		if ( empty( $message ) && $message_code ) {
			$message = self::translate_link_code_to_message( $message_code );
		}

		require FrmProAppHelper::plugin_path() . '/classes/views/frmpro-entries/edit.php';
	}

	/**
	 * @param mixed    $value
	 * @param string   $tag
	 * @param array    $atts
	 * @param stdClass $field
	 * @return mixed
	 */
	public static function filter_shortcode_value( $value, $tag, $atts, $field ) {
		if ( ! is_null( $value ) ) {
			if ( ! empty( $atts['striphtml'] ) ) {
				self::kses_deep( $atts, $value );
			} elseif ( empty( $atts['keepjs'] ) ) {
				FrmAppHelper::sanitize_value( 'wp_kses_post', $value );
			}
		}

		return self::get_option_label_for_saved_value( $value, $field, $atts );
	}

	/**
	 * @since 2.05.03
	 *
	 * @param array $atts
	 * @return void
	 */
	private static function kses_deep( $atts, &$value ) {
		$allowed_tags = apply_filters( 'frm_striphtml_allowed_tags', array(), $atts );
		if ( is_array( $value ) ) {
			foreach ( $value as $k => $v ) {
				$value[ $k ] = wp_kses( $v, $allowed_tags );
				unset( $k, $v );
			}
		} else {
			$value = wp_kses( $value, $allowed_tags );
		}
	}

	/**
	 * Get the option label from a saved value, if a field has separate values and saved_value="1" is not set
	 *
	 * @since 2.02.14
	 *
	 * @param mixed $value
	 * @param object $field
	 * @param array $atts
	 *
	 * @return array|mixed
	 */
	public static function get_option_label_for_saved_value( $value, $field, $atts = array() ) {
		$show_value  = isset( $atts['show'] ) && $atts['show'] === 'value';
		$saved_value = ! empty( $atts['saved_value'] );
		if ( $saved_value || $value === false || $show_value ) {
			return $value;
		}

		if ( FrmProImages::showing_images( $field, $atts ) ) {
			return FrmProImages::display( $field, $value, $atts );
		}

		$has_separate_option = in_array( $field->type, array( 'radio', 'checkbox', 'select', 'product' ), true ) && FrmField::is_option_true( $field, 'separate_value' );
		if ( ! $has_separate_option ) {
			return $value;
		}

		$f_values = array();
		$f_labels = array();

		$show = isset( $atts['show'] ) && $atts['show'] === 'price' ? 'price' : 'label';

		foreach ( $field->options as $opt_key => $opt ) {
			if ( ! is_array( $opt ) ) {
				continue;
			}

			$f_labels[ $opt_key ] = isset( $opt[ $show ] ) ? $opt[ $show ] : reset( $opt );
			$f_values[ $opt_key ] = isset( $opt['value'] ) ? $opt['value'] : $f_labels[ $opt_key ];
			if ( $f_labels[ $opt_key ] == $f_values[ $opt_key ] ) {
				unset( $f_values[ $opt_key ], $f_labels[ $opt_key ] );
			}
			unset( $opt_key, $opt );
		}

		if ( ! empty( $f_values ) ) {
			if ( is_array( $value ) ) {
				$value = FrmAppHelper::array_flatten( $value, 'reset' );
			}

			foreach ( (array) $value as $v_key => $val ) {
				if ( in_array( $val, $f_values ) ) {
					$opt = array_search( $val, $f_values );
					if ( is_array( $value ) ) {
						$value[ $v_key ] = $f_labels[ $opt ];
					} else {
						$value = $f_labels[ $opt ];
					}
				}
				unset( $v_key, $val );
			}
		}

		return $value;
	}

	/**
	 * Trigger from the frm_display_value_atts hook
	 *
	 * @since 2.0
	 */
	public static function display_value_atts( $atts, $field ) {
		if ( $field->type === 'file' ) {
			$atts['truncate'] = false;
			$atts['html']     = true;
		} elseif ( FrmProImages::has_image_options( $field ) ) {
			$atts['truncate']      = false;
			$atts['show_filename'] = false;

			if ( isset( $atts['show_icon'] ) && ! $atts['show_icon'] ) {
				// For the CSV export.
				$atts['show_image'] = false;
				$atts['sep']        = ', ';
			} elseif ( ! isset( $atts['saved_value'] ) || ! $atts['saved_value'] ) {
				$atts['sep'] = ' ';
			}
		}

		return $atts;
	}

	public static function filter_display_value( $value, $field, $atts = array() ) {
		self::set_display_atts( $field, $atts );

		if ( $atts['type'] === 'return_raw' ) {
			return $value;
		}
		if ( $atts['type'] === 'data' ) {
			self::get_dynamic_value_for_display( $field, $atts, $value );
		} else {
			$atts['return_array'] = true;
			$value                = FrmFieldsHelper::get_unfiltered_display_value( compact( 'value', 'field', 'atts' ) );

			$value = self::get_option_label_for_saved_value( $value, $field, $atts );
			if ( is_array( $value ) ) {
				$sep   = isset( $atts['sep'] ) ? $atts['sep'] : ', ';
				$value = implode( $sep, $value );
			}
		}

		if ( ! $atts['keepjs'] ) {
			FrmAppHelper::sanitize_value( 'wp_kses_post', $value );
		}

		return $value;
	}

	/**
	 * Set a value after all other field-specific formatting has been set.
	 *
	 * @since 4.06.01
	 */
	public static function display_value( $value, $field, $atts ) {
		$value = FrmProCurrencyHelper::maybe_format_currency( $value, $field, $atts );
		return $value;
	}

	private static function set_display_atts( $field, &$atts ) {
		$defaults = array(
			'html'   => 0,
			'type'   => $field->type,
			'keepjs' => 0,
		);
		$atts     = array_merge( $defaults, $atts );

		if ( FrmField::is_image( $field ) ) {
			$atts['html'] = true;
			$atts['sep']  = '';
		} elseif ( isset( $atts['show'] ) && empty( $atts['show'] ) ) {
			unset( $atts['show'] );
		}

		if ( $atts['type'] === 'file' && $atts['html'] && $atts['sep'] === ', ' ) {
			$atts['sep']        = '';
			$atts['show_image'] = true;
			if ( ! isset( $atts['add_link'] ) ) {
				$atts['add_link'] = true;
			}
		}
	}

	private static function get_dynamic_value_for_display( $field, $atts, &$value ) {
		if ( ! is_numeric( $value ) ) {
			if ( ! is_array( $value ) ) {
				$value = explode( $atts['sep'], $value );
			}

			if ( is_array( $value ) ) {
				$new_value = '';
				foreach ( $value as $entry_id ) {
					if ( ! empty( $new_value ) ) {
						$new_value .= $atts['sep'];
					}

					if ( is_numeric( $entry_id ) ) {
						$new_value .= FrmProFieldsHelper::get_data_value( $entry_id, $field, $atts );
					} else {
						$new_value .= $entry_id;
					}
				}
				$value = $new_value;
			}
		} else {
			//replace item id with specified field
			$new_value = FrmProFieldsHelper::get_data_value( $value, $field, $atts );

			if ( FrmProField::is_list_field( $field ) ) {
				$linked_field = FrmField::getOne( $field->field_options['form_select'] );
				if ( $linked_field && $linked_field->type === 'file' ) {
					$old_value = explode( ', ', $new_value );
					$new_value = '';
					foreach ( $old_value as $v ) {
						$new_value .= '<img src="' . esc_url( $v ) . '" class="frm_image_from_url" alt="" />';
						if ( $atts['show_filename'] ) {
							$new_value .= '<br/>' . $v;
						}
						unset( $v );
					}
				} else {
					$new_value = $value;
				}
			}

			$value = $new_value;
		}
	}

	public static function route( $action ) {
		add_filter( 'frm_entry_stop_action_route', '__return_true' );

		add_action( 'frm_load_form_hooks', 'FrmHooksController::trigger_load_form_hooks' );
		FrmAppHelper::trigger_hook_load( 'form' );

		if ( in_array( $action, array( 'create', 'edit', 'update', 'duplicate', 'new' ) ) ) {
			wp_enqueue_style( 'formidable' );
		}

		switch ( $action ) {
			case 'create':
			case 'edit':
			case 'update':
				return self::$action();

			case 'new':
				return self::new_entry();

			default:
				$action = FrmAppHelper::get_param( 'action', '', 'get', 'sanitize_text_field' );
				if ( $action == -1 ) {
					$action = FrmAppHelper::get_param( 'action2', '', 'get', 'sanitize_title' );
				}

				if ( strpos( $action, 'bulk_' ) === 0 ) {
					FrmAppHelper::remove_get_action();
					return self::bulk_actions( $action );
				}

				$message = '';
				$errors  = array();
				switch ( FrmAppHelper::get_param( 'message' ) ) {
					case 'destroy_all':
						$message = __( 'Entries Successfully Deleted', 'formidable-pro' );
						break;

					case 'no_entries_selected':
						$errors[] = __( 'No Entries Selected', 'formidable-pro' );
						break;
				}

				return FrmEntriesController::display_list( $message, $errors );
		}
	}

	/**
	 * @return string The name of the entry listing class
	 */
	public static function list_class() {
		return 'FrmProEntriesListHelper';
	}

	public static function manage_columns( $columns ) {
		global $frm_vars;
		$form_id = FrmForm::get_current_form_id();

		$cb_item                          = array( 'cb' => '<input type="checkbox" />' );
		$columns                          = $cb_item + (array) $columns;
		$columns[ $form_id . '_post_id' ] = __( 'Post', 'formidable-pro' );

		// Draft column moved to lite from version 6.4.2 and renamed to entry statuses.
		if ( version_compare( FrmAppHelper::plugin_version(), '6.4.2', '<' ) ) {
			$columns[ $form_id . '_is_draft' ] = __( 'Draft', 'formidable-pro' );
		}

		$columns[ $form_id . '_parent_item_id' ] = __( 'Parent Entry ID', 'formidable-pro' );

		$frm_vars['cols'] = $columns;

		return $columns;
	}

	public static function row_actions( $actions, $item ) {
		$edit_link = FrmProEntry::admin_edit_link( $item->id );
		if ( current_user_can( 'frm_edit_entries' ) ) {
			$actions['edit'] = '<a href="' . esc_url( $edit_link ) . '">' . __( 'Edit' ) . '</a>';
		}

		if ( current_user_can( 'frm_create_entries' ) ) {
			$duplicate_link       = '?page=formidable-entries&frm_action=duplicate&id=' . $item->id . '&form=' . $item->form_id;
			$actions['duplicate'] = '<a href="' . esc_url( wp_nonce_url( $duplicate_link ) ) . '">' . __( 'Duplicate', 'formidable-pro' ) . '</a>';
		}

		// move delete link to the end of the links
		if ( isset( $actions['delete'] ) ) {
			$delete_link = $actions['delete'];
			unset( $actions['delete'] );
			$actions['delete'] = $delete_link;
		}

		return $actions;
	}

	public static function get_form_results( $atts ) {
		FrmAppHelper::sanitize_value( 'wp_kses_post', $atts );

		$atts = shortcode_atts(
			array(
				'id'          => false,
				'cols'        => 99,
				'style'       => true,
				'fields'      => false,
				'clickable'   => false,
				'user_id'     => false,
				'google'      => false,
				'pagesize'    => 20,
				'sort'        => true,
				'edit_link'   => false,
				'delete_link' => false,
				'page_id'     => false,
				'no_entries'  => __( 'No Entries Found', 'formidable-pro' ),
				'confirm'     => __( 'Are you sure you want to delete that entry?', 'formidable-pro' ),
				'drafts'      => '0',
			),
			$atts
		);

		$atts['form'] = self::get_form( $atts );
		if ( ! $atts['form'] ) {
			return;
		}

		if ( $atts['fields'] ) {
			$atts['fields'] = explode( ',', $atts['fields'] );
		}

		self::get_table_values( $atts );
		if ( empty( $atts['form_cols'] ) ) {
			$contents = '<div class="frm_no_entries">' . __( 'There are no matching fields. Please check your formresults shortcode to make sure you are using the correct form and field IDs.', 'formidable-pro' ) . '</div>';
			return $contents;
		}

		$contents = '';
		self::add_delete_entry_message( $atts, $contents );
		self::setup_edit_link( $atts );
		self::setup_delete_link( $atts );

		$filename = self::set_formresults_filename( $atts );

		self::load_form_scripts( $atts );

		ob_start();
		include FrmProAppHelper::plugin_path() . '/classes/views/frmpro-entries/' . $filename . '.php';
		$contents .= ob_get_contents();
		ob_end_clean();

		if ( ! $atts['google'] && $atts['clickable'] ) {
			$contents = make_clickable( $contents );
		}

		return $contents;
	}

	/**
	 * Get the form for the formresults table
	 *
	 * @since 2.0.09
	 * @param array $atts
	 * @return object
	 */
	private static function get_form( $atts ) {
		if ( ! $atts['id'] ) {
			return false;
		}

		return FrmForm::getOne( $atts['id'] );
	}

	/**
	 * Get entries and fields for formresults
	 *
	 * @since 2.0.09
	 * @param array $atts
	 */
	private static function get_table_values( &$atts ) {
		// Get all fields in the form
		$atts['form_cols'] = FrmField::get_all_for_form( $atts['form']->id, '', 'include' );

		// Get all entries for the form
		$atts['entries'] = self::get_entries_for_table( $atts );

		$subforms_to_include = array();
		$field_count         = 0;
		foreach ( $atts['form_cols'] as $k => $f ) {
			if ( $field_count < $atts['cols'] && self::is_field_needed( $f, $atts, $subforms_to_include ) ) {
				++$field_count;
				self::get_sub_field_values( $f, $atts );
			} else {
				unset( $atts['form_cols'][ $k ] );
			}
		}
	}

	/**
	 * @param array $atts
	 * @return array|false
	 */
	private static function get_entries_for_table( $atts ) {
		$where = array( 'it.form_id' => $atts['form']->id );

		if ( $atts['drafts'] !== 'both' ) {
			$where['it.is_draft'] = (int) $atts['drafts'];
		}

		if ( $atts['user_id'] ) {
			$where['user_id'] = (int) FrmAppHelper::get_user_id_param( $atts['user_id'] );
		}

		$s = FrmAppHelper::get_param( 'frm_search', false, 'get', 'sanitize_text_field' );
		if ( $s ) {
			$new_ids        = FrmProEntriesHelper::get_search_ids( $s, $atts['form']->id, array( 'is_draft' => $atts['drafts'] ) );
			$where['it.id'] = $new_ids;
		}

		if ( isset( $new_ids ) && empty( $new_ids ) ) {
			$entries = false;
		} else {
			$entries = FrmEntry::getAll( $where, '', '', true, false );
		}

		return $entries;
	}

	/**
	 * Check if each field is needed in the formresults table
	 *
	 * @since 2.0.09
	 * @param object $f - field
	 * @param array $atts
	 * @param array $subforms_to_include
	 * @return bool
	 */
	private static function is_field_needed( $f, $atts, &$subforms_to_include ) {
		if ( ! empty( $atts['fields'] ) ) {
			if ( FrmField::is_no_save_field( $f->type ) ) {
				if ( FrmField::is_option_true( $f, 'form_select' ) && ( in_array( $f->id, $atts['fields'] ) || in_array( $f->field_key, $atts['fields'] ) ) ) {
					$subforms_to_include[] = $f->field_options['form_select'];
				}
				return false;
			}

			if ( ! in_array( $f->form_id, $subforms_to_include ) && ! in_array( $f->id, $atts['fields'] ) && ! in_array( $f->field_key, $atts['fields'] ) ) {
				return false;
			}
		} elseif ( FrmField::is_no_save_field( $f->type ) ) {
				return false;
		}

		return true;
	}

	/**
	 * Get values in nested forms (repeating sections and embed form)
	 *
	 * @since 2.0.09
	 * @param object $field
	 * @param array $atts
	 */
	private static function get_sub_field_values( $field, &$atts ) {
		if ( empty( $atts['entries'] ) ) {
			return;
		}

		foreach ( $atts['entries'] as $key => $entry ) {
			if ( ! isset( $entry->metas[ $field->id ] ) || $entry->metas[ $field->id ] == '' ) {
				FrmProEntryMeta::add_repeating_value_to_entry( $field, $atts['entries'][ $key ] );
			}
		}
	}

	/**
	 * If delete_link is set in formresults and frm_action is set to destroy,
	 * check if entry should be deleted when page is loaded
	 *
	 * @param array  $atts
	 * @param string $contents
	 * @return void
	 */
	private static function add_delete_entry_message( $atts, &$contents ) {
		$action = FrmAppHelper::simple_get( 'frm_action', 'sanitize_title' );
		if ( $atts['delete_link'] && $action === 'destroy' ) {
			$delete_message = self::ajax_destroy( false, false, false );
			$delete_message = '<div class="' . esc_attr( $atts['style'] ? FrmFormsHelper::get_form_style_class() : '' ) . '"><div class="frm_message">' . $delete_message . '</div></div>';
			$contents       = $delete_message;
		}
	}

	/**
	 * If edit link is set in formresults, set up values for the edit link
	 *
	 * @since 2.0.09
	 * @param array $atts
	 */
	private static function setup_edit_link( &$atts ) {
		if ( $atts['edit_link'] ) {
			$atts['anchor'] = '';
			if ( ! $atts['page_id'] ) {
				global $post;
				$atts['page_id'] = $post ? $post->ID : 0;
				$atts['anchor']  = '#form_' . $atts['form']->form_key;
			}
			if ( $atts['edit_link'] === '1' ) {
				$atts['edit_link'] = __( 'Edit', 'formidable-pro' );
			}
			$atts['permalink'] = get_permalink( $atts['page_id'] );
		}
	}

	/**
	 * If delete_link is set to true in formresults, set the delete link text
	 *
	 * @since 2.0.09
	 * @param array $atts
	 */
	private static function setup_delete_link( &$atts ) {
		if ( $atts['delete_link'] === '1' ) {
			$atts['delete_link'] = __( 'Delete', 'formidable-pro' );
		}
	}

	/**
	 * Get the filename for the formresults table
	 *
	 * @since 2.0.09
	 * @param array $atts
	 * @return string $filename
	 */
	private static function set_formresults_filename( &$atts ) {
		if ( $atts['google'] ) {
			$filename = 'google_table';
			self::prepare_google_table( $atts );
		} else {
			$atts['fields'] = (array) $atts['fields'];
			$filename       = 'table';
		}
		return $filename;
	}

	/**
	 * @param array $atts
	 * @return void
	 */
	private static function prepare_google_table( $atts ) {
		global $frm_vars;

		$options = array(
			'allowHtml' => true,
			'sort'      => $atts['sort'] ? 'enable' : 'disable',
		);

		if ( $atts['pagesize'] ) {
			$options['page']     = 'enable';
			$options['pageSize'] = (int) $atts['pagesize'];
		}

		if ( $atts['style'] ) {
			$options['cssClassNames'] = array( 'oddTableRow' => 'frm_even' );
		}

		$shortcode_options            = $atts;
		$shortcode_options['form_id'] = $atts['form']->id;
		unset( $shortcode_options['entries'], $shortcode_options['form_cols'], $shortcode_options['form'] );
		unset( $shortcode_options['permalink'], $shortcode_options['anchor'] );

		$graph_vals = array(
			'fields'    => array(),
			'entries'   => array(),
			'options'   => $shortcode_options,
			'graphOpts' => $options,
		);

		if ( $atts['clickable'] ) {
			$graph_vals['options']['no_entries'] = make_clickable( $graph_vals['options']['no_entries'] );
		}

		if ( empty( $atts['entries'] ) ) {
			$atts['entries'] = array();
		}

		$first_loop = true;
		foreach ( $atts['entries'] as $k => $entry ) {
			$this_entry = array(
				'id'    => $entry->id,
				'metas' => array(),
			);

			foreach ( $atts['form_cols'] as $col ) {
				$field_value = isset( $entry->metas[ $col->id ] ) ? $entry->metas[ $col->id ] : false;
				$type        = $col->type;

				$val = FrmEntriesHelper::display_value(
					$field_value,
					$col,
					array(
						'type'          => $type,
						'post_id'       => $entry->post_id,
						'entry_id'      => $entry->id,
						'show_filename' => false,
					)
				);

				if ( $col->type === 'number' ) {
					$val = empty( $val ) ? '0' : $val;
					if ( ! is_numeric( $val ) ) {
						// Repeaters my not be numeric.
						$type = 'text';
					}
				} elseif ( $col->type === 'checkbox' && count( $col->options ) === 1 ) {
					// force boolean values
					$val = ! empty( $val );
				} elseif ( empty( $val ) ) {
					$val = '';
				} else {
					$val = $atts['clickable'] && $col->type !== 'file' ? make_clickable( $val ) : $val;
				}

				$this_entry['metas'][ $col->id ] = $val;

				if ( $first_loop ) {
					// add the fields to graphs on first loop only
					$graph_vals['fields'][] = array(
						'id'            => $col->id,
						'type'          => $type,
						'name'          => $col->name,
						'options'       => $col->options,
						'field_options' => array( 'post_field' => isset( $col->field_options['post_field'] ) ? $col->field_options['post_field'] : '' ),
					);
				}
				unset( $col );
			}

			if ( $atts['edit_link'] && FrmProEntriesHelper::user_can_edit( $entry, $atts['form'] ) ) {
				$this_entry['editLink'] = esc_url_raw(
                    add_query_arg(
                        array(
							'frm_action' => 'edit',
							'entry'      => $entry->id,
                        ),
                        $atts['permalink'] 
                    ) 
                ) . $atts['anchor'];
			}

			if ( $atts['delete_link'] && FrmProEntriesHelper::user_can_delete( $entry ) ) {
				$this_entry['deleteLink'] = esc_url_raw( self::create_delete_link( $entry->id ) );
			}
			$graph_vals['entries'][] = $this_entry;

			$first_loop = false;
			unset( $k, $entry, $this_entry );
		}

		if ( ! isset( $frm_vars['google_graphs'] ) ) {
			$frm_vars['google_graphs'] = array();
		}

		if ( ! isset( $frm_vars['google_graphs']['table'] ) ) {
			$frm_vars['google_graphs']['table'] = array();
		}

		$frm_vars['google_graphs']['table'][] = $graph_vals;
	}

	/**
	 * @since 6.17
	 *
	 * @param int        $entry_id
	 * @param int|string $post_id
	 * @return string
	 */
	public static function create_delete_link( $entry_id, $post_id = '' ) {
		self::$delete_link_created = true;
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}
		return wp_nonce_url( admin_url( 'admin-ajax.php?action=frm_entries_destroy&entry=' . $entry_id . '&redirect=' . $post_id ), 'frm_ajax', 'nonce' );
	}

	/**
	 * Load JS and CSS for a shortcode.
	 *
	 * @param array $atts
	 * @return void
	 */
	private static function load_form_scripts( $atts = array() ) {
		global $frm_vars;

		// Trigger CSS loading
		if ( isset( $atts['style'] ) && $atts['style'] ) {
			$frm_vars['load_css'] = true;
		}

		// Trigger the js load
		$frm_vars['forms_loaded'][] = true;
	}


	public static function get_search( $atts ) {
		$atts = shortcode_atts(
			array(
				'post_id' => '',
				'label'   => __( 'Search', 'formidable-pro' ),
				'style'   => false,
				'views'   => '',
			),
			$atts
		);

		if ( $atts['post_id'] == '' ) {
			global $post;
			if ( $post ) {
				$atts['post_id'] = $post->ID;
			}
		}

		if ( $atts['post_id'] != '' ) {
			$action_link = get_permalink( $atts['post_id'] );
		} else {
			$action_link = '';
		}

		if ( ! empty( $atts['style'] ) ) {
			self::load_form_scripts();

			if ( $atts['style'] == 1 || 'true' == $atts['style'] ) {
				$atts['style'] = FrmStylesController::get_form_style_class( 'with_frm_style', 'default' );
			} else {
				$atts['style'] .= ' with_frm_style';
			}
		}

		ob_start();
		include FrmProAppHelper::plugin_path() . '/classes/views/frmpro-entries/search.php';
		$contents = ob_get_contents();
		ob_end_clean();
		return $contents;
	}

	public static function entry_link_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'id'          => false,
				'field_key'   => 'created_at',
				'type'        => 'list',
				'logged_in'   => true,
				'edit'        => true,
				'class'       => '',
				'link_type'   => 'page',
				'blank_label' => '',
				'param_name'  => 'entry',
				'param_value' => 'key',
				'page_id'     => false,
				'show_delete' => false,
				'confirm'     => __( 'Are you sure you want to delete that entry?', 'formidable-pro' ),
				'drafts'      => false,
				'order'       => '',
				'user_id'     => 'current',
			),
			$atts
		);

		// Keep logged_in for reverse compatibility
		if ( $atts['logged_in'] == false ) {
			$atts['user_id'] = false;
		}

		$user_ID = get_current_user_id();
		if ( ! $atts['id'] || ( $atts['user_id'] && ! $user_ID ) ) {
			return;
		}

		$atts = self::fill_entry_links_atts( $atts );

		$action       = isset( $_GET ) && isset( $_GET['frm_action'] ) ? 'frm_action' : 'action';
		$entry_action = FrmAppHelper::simple_get( $action, 'sanitize_title' );
		$entry_key    = FrmAppHelper::simple_get( 'entry', 'sanitize_title' );

		if ( $entry_action === 'destroy' ) {
			self::maybe_delete_entry( $entry_key );
		}

		$entries = self::get_entry_link_entries( $atts );
		if ( empty( $entries ) ) {
			return;
		}

		$extra_args = array(
			'entry_action' => $entry_action,
			'entry_key'    => $entry_key,
			'current_user' => $user_ID,
		);
		self::maybe_remove_entries_from_list( $extra_args, $atts, $entries );

		$content = array();
		switch ( $atts['type'] ) {
			case 'list':
				self::entry_link_list( $entries, $atts, $content );
				break;
			case 'select':
				self::entry_link_select( $entries, $atts, $content );
				break;
			case 'collapse':
				self::entry_link_collapse( $entries, $atts, $content );
		}

		$content = implode( '', $content );
		return $content;
	}

	/**
	 * @param array $atts
	 */
	private static function fill_entry_links_atts( $atts ) {
		$atts['id'] = (int) $atts['id'];
		if ( $atts['show_delete'] === 1 ) {
			$atts['show_delete'] = __( 'Delete', 'formidable-pro' );
		}
		$atts['label'] = $atts['show_delete'];

		$atts['field'] = false;
		if ( $atts['field_key'] !== 'created_at' ) {
			$atts['field'] = FrmField::getOne( $atts['field_key'] );
			if ( ! $atts['field'] ) {
				$atts['field_key'] = 'created_at';
			}
		}

		if ( ! in_array( $atts['type'], array( 'list', 'collapse', 'select' ), true ) ) {
			$atts['type'] = 'select';
		}

		if ( empty( $atts['confirm'] ) ) {
			$atts['confirm'] = __( 'Are you sure you want to delete that entry?', 'formidable-pro' );
		}

		if ( $atts['user_id'] === 'current' ) {
			$atts['user_id'] = get_current_user_id();
		}

		global $post;
		$atts['permalink'] = get_permalink( $atts['page_id'] ? $atts['page_id'] : $post->ID );

		return $atts;
	}

	private static function get_entry_link_entries( $atts ) {
		$s = FrmAppHelper::get_param( 'frm_search', false, 'get', 'sanitize_text_field' );

		if ( $s ) {
			$entry_ids = FrmProEntriesHelper::get_search_ids(
                $s,
                $atts['id'],
                array(
					'is_draft' => $atts['drafts'],
					'user_id'  => $atts['user_id'],
                ) 
            );
		} else {
			$entry_ids = FrmEntryMeta::getEntryIds(
                array( 'fi.form_id' => (int) $atts['id'] ),
                '',
                '',
                true,
                array(
					'is_draft' => $atts['drafts'],
					'user_id'  => $atts['user_id'],
                ) 
            );
		}

		if ( empty( $entry_ids ) ) {
			return;
		}

		$order = stripos( trim( $atts['order'] ), 'order ' ) === 0 ? $atts['order'] : '';
		$order = $atts['type'] === 'collapse' || $atts['order'] === 'DESC' ? ' ORDER BY it.created_at DESC' : $order;

		$entries = FrmEntry::getAll( array( 'it.id' => $entry_ids ), $order, '', true );

		return $entries;
	}

	/**
	 * Remove deleted entries from the list.
	 * Also remove private, draft, and pending posts if the current user is not the creator or an administrator.
	 *
	 * @since 2.0.18
	 * @param array $extra_args
	 * @param array $atts
	 * @param array $entries
	 */
	private static function maybe_remove_entries_from_list( $extra_args, $atts, &$entries ) {
		$public_entries    = array();
		$post_status_check = array();

		foreach ( $entries as $entry ) {
			// If entry was just deleted, don't show it in list
			if ( $extra_args['entry_action'] === 'destroy' && in_array( $extra_args['entry_key'], array( $entry->item_key, $entry->id ) ) ) {
				continue;
			}

			// If entry has a post, check the post status
			if ( $entry->post_id ) {
				$post_status_check[ $entry->post_id ] = $entry->id;
			}
			$public_entries[ $entry->id ] = $entry;
		}

		$current_user_is_creator_of_all_listed_entries = ( $extra_args['current_user'] && $atts['user_id'] == $extra_args['current_user'] );
		if ( current_user_can( 'administrator' ) || $current_user_is_creator_of_all_listed_entries ) {
			// If the current user is an administrator or the creator of the entry, don't remove private, draft, or pending posts
		} elseif ( ! empty( $post_status_check ) ) {
			global $wpdb;
			$query          = array(
				'post_status !' => 'publish',
				'ID'            => array_keys( $post_status_check ),
			);
			$remove_entries = FrmDb::get_col( $wpdb->posts, $query, 'ID' );
			unset( $query );

			foreach ( $remove_entries as $entry_post_id ) {
				unset( $public_entries[ $post_status_check[ $entry_post_id ] ] );
			}
			unset( $remove_entries );
		}

		$entries = $public_entries;
	}

	/**
	 * @param array $entries
	 * @param array $content
	 * @return void
	 */
	private static function entry_link_list( $entries, $atts, array &$content ) {
		$content[] = '<ul class="frm_entry_ul ' . $atts['class'] . '">' . "\n";

		foreach ( $entries as $entry ) {
			$value = self::entry_link_meta_value( $entry, $atts );
			$link  = self::entry_link_href( $entry, $atts );

			$content[] = '<li><a href="' . esc_url( $link ) . '">' . $value . '</a>';
			if ( ! empty( $atts['show_delete'] ) && FrmProEntriesHelper::user_can_delete( $entry ) ) {
				$content[] = ' <a href="' . esc_url(
                    add_query_arg(
                        array(
							'frm_action' => 'destroy',
							'entry'      => $entry->id,
                        ),
                        $atts['permalink'] 
                    ) 
                ) . '" class="frm_delete_list" data-frmconfirm="' . esc_attr( $atts['confirm'] ) . '">' . $atts['show_delete'] . '</a>' . "\n";
			}
			$content[] = "</li>\n";
		}

		$content[] = "</ul>\n";
	}

	/**
	 * @param array $entries
	 * @param array $atts
	 * @param array $content
	 * @return void
	 */
	private static function entry_link_collapse( $entries, $atts, array &$content ) {
		FrmProStylesController::enqueue_jquery_css();
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'formidable' );
		wp_enqueue_script( 'formidablepro' );

		$content[]  = '<div class="frm_collapse">';
		$year       = '';
		$month      = '';
		$prev_year  = false;
		$prev_month = false;

		foreach ( $entries as $entry ) {
			$value = self::entry_link_meta_value( $entry, $atts );
			$link  = self::entry_link_href( $entry, $atts );

			$timestamp = strtotime( $entry->created_at );
			$new_year  = date_i18n( 'Y', $timestamp );
			$new_month = date_i18n( 'F', $timestamp );
			if ( $new_year != $year ) {
				if ( $prev_year ) {
					if ( $prev_month ) {
						$content[] = '</ul></div>';
					}
					$content[]  = '</div>';
					$prev_month = false;
				}
				$class     = $prev_year ? ' frm_hidden' : '';
				$triangle  = $prev_year ? 'e' : 's';
				$content[] = "\n" . '<div class="frm_year_heading frm_year_heading_' . esc_attr( $atts['id'] ) . '">
					<span class="ui-icon ui-icon-triangle-1-' . esc_attr( $triangle ) . '"></span>' . "\n" .
					'<a>' . sanitize_text_field( $new_year ) . '</a></div>' . "\n" .
					'<div class="frm_toggle_container' . esc_attr( $class ) . '">' . "\n";
				$prev_year = true;
			}

			if ( $new_month != $month ) {
				if ( $prev_month ) {
					$content[] = '</ul></div>';
				}
				$class      = $prev_month ? ' frm_hidden' : '';
				$triangle   = $prev_month ? 'e' : 's';
				$content[]  = '<div class="frm_month_heading frm_month_heading_' . esc_attr( $atts['id'] ) . '">
					<span class="ui-icon ui-icon-triangle-1-' . esc_attr( $triangle ) . '"></span>' . "\n" .
					'<a>' . sanitize_text_field( $new_month ) . '</a>' . "\n" . '</div>' . "\n" .
					'<div class="frm_toggle_container frm_month_listing' . esc_attr( $class ) . '"><ul>' . "\n";
				$prev_month = true;
			}
			$content[] = '<li><a href="' . esc_url( $link ) . '">' . $value . '</a>';

			if ( $atts['show_delete'] && FrmProEntriesHelper::user_can_delete( $entry ) ) {
				$content[] = ' <a href="' . esc_url(
                    add_query_arg(
                        array(
							'frm_action' => 'destroy',
							'entry'      => $entry->id,
                        ),
                        $atts['permalink'] 
                    ) 
                ) . '" class="frm_delete_list" data-frmconfirm="' . esc_attr( $atts['confirm'] ) . '">' . $atts['show_delete'] . '</a>' . "\n";
			}
			$content[] = "</li>\n";
			$year      = $new_year;
			$month     = $new_month;
		}

		if ( $prev_year ) {
			$content[] = '</div>';
		}
		if ( $prev_month ) {
			$content[] = '</ul></div>';
		}
		$content[] = '</div>';
	}

	/**
	 * @param array $entries
	 * @return void
	 */
	private static function entry_link_select( $entries, $atts, array &$content ) {
		global $post;

		$content[]   = '<select id="frm_select_form_' . esc_attr( $atts['id'] ) . '" name="frm_select_form_' . esc_attr( $atts['id'] ) . '" class="' . esc_attr( $atts['class'] ) . '" onchange="location=this.options[this.selectedIndex].value;">' . "\n";
		$content[]   = '<option value="' . esc_attr( get_permalink( $post->ID ) ) . '">' . $atts['blank_label'] . '</option>' . "\n";
		$entry_param = FrmAppHelper::simple_get( 'entry', 'sanitize_title' );

		foreach ( $entries as $entry ) {
			$value = self::entry_link_meta_value( $entry, $atts );
			$link  = self::entry_link_href( $entry, $atts );

			$content[] = '<option value="' . esc_url( $link ) . '" ' . selected( $entry_param, $entry->item_key, false ) . '>' . esc_html( $value ) . "</option>\n";
		}

		$content[] = "</select>\n";
		if ( $atts['show_delete'] && $entry_param ) {
			$content[] = " <a href='" . esc_url(
                add_query_arg(
                    array(
						'frm_action' => 'destroy',
						'entry'      => $entry_param,
                    ),
                    $atts['permalink'] 
                ) 
            ) . "' class='frm_delete_list' data-frmconfirm='" . esc_attr( $atts['confirm'] ) . "'>" . $atts['show_delete'] . "</a>\n";
		}
	}

	private static function entry_link_meta_value( $entry, $atts ) {
		$value = '';

		if ( $atts['field_key'] && $atts['field_key'] !== 'created_at' ) {
			if ( $entry->post_id && ( ( $atts['field'] && $atts['field']->field_options['post_field'] ) || $atts['field']->type === 'tag' ) ) {
				$meta  = false;
				$value = FrmProEntryMetaHelper::get_post_value(
					$entry->post_id,
					$atts['field']->field_options['post_field'],
					$atts['field']->field_options['custom_field'],
					array(
						'type'    => $atts['field']->type,
						'form_id' => $atts['field']->form_id,
						'field'   => $atts['field'],
					)
				);
			} else {
				$meta = isset( $entry->metas[ $atts['field']->id ] ) ? $entry->metas[ $atts['field']->id ] : '';
			}
		} else {
			$meta = reset( $entry->metas );
		}

		self::entry_link_value( $entry, $atts, $meta, $value );

		return $value;
	}

	private static function entry_link_value( $entry, $atts, $meta, &$value ) {
		if ( 'created_at' != $atts['field_key'] && $meta ) {
			if ( is_object( $meta ) ) {
				$value = $meta->meta_value;
			} else {
				$value = $meta;
			}
		}

		if ( '' == $value ) {
			$value = date_i18n( get_option( 'date_format' ), strtotime( $entry->created_at ) );
			return;
		}

		$new_atts = array(
			'type'          => $atts['field']->type,
			'display_type'  => $atts['type'],
			'show_filename' => false,
		);

		$value = FrmEntriesHelper::display_value( $value, $atts['field'], $new_atts );
	}

	private static function entry_link_href( $entry, $atts ) {
		$args = array(
			$atts['param_name'] => 'key' === $atts['param_value'] ? $entry->item_key : $entry->id,
		);

		if ( $atts['edit'] ) {
			$args['frm_action'] = 'edit';
		}

		if ( $atts['link_type'] === 'scroll' ) {
			$link = '#' . $entry->item_key;
		} elseif ( $atts['link_type'] === 'admin' ) {
			$link = add_query_arg( $args, FrmAppHelper::get_server_value( 'REQUEST_URI' ) );
		} else {
			$link = add_query_arg( $args, $atts['permalink'] );
		}

		return $link;
	}

	/**
	 * @param array $atts
	 * @return string
	 */
	public static function entry_edit_link( $atts ) {
		global $post, $frm_vars, $wpdb;
		$atts = shortcode_atts(
			array(
				'id'             => ( isset( $frm_vars['editing_entry'] ) ? $frm_vars['editing_entry'] : false ),
				'label'          => __( 'Edit', 'formidable-pro' ),
				'cancel'         => __( 'Cancel', 'formidable-pro' ),
				'class'          => '',
				'page_id'        => ( $post ? $post->ID : 0 ),
				'html_id'        => false,
				'prefix'         => '',
				'form_id'        => false,
				'title'          => '',
				'fields'         => array(),
				'exclude_fields' => array(),
				'start_page'     => 1,
			),
			$atts
		);

		$link     = '';
		$entry_id = $atts['id'] && is_numeric( $atts['id'] ) ? $atts['id'] : FrmAppHelper::get_param( 'entry', false, 'get', 'sanitize_text_field' );

		if ( ! $entry_id && $atts['id'] === 'current' ) {
			if ( ! empty( $frm_vars['editing_entry'] ) && is_numeric( $frm_vars['editing_entry'] ) ) {
				$entry_id = $frm_vars['editing_entry'];
			} elseif ( $post ) {
				$entry_id = FrmDb::get_var( $wpdb->prefix . 'frm_items', array( 'post_id' => $post->ID ) );
			}
		}

		if ( ! $entry_id ) {
			return '';
		}

		if ( ! $atts['form_id'] ) {
			$atts['form_id'] = (int) FrmDb::get_var( $wpdb->prefix . 'frm_items', array( 'id' => $entry_id ), 'form_id' );
		}

		// If user is not allowed to edit then don't show the link.
		if ( ! FrmProEntriesHelper::user_can_edit( $entry_id, $atts['form_id'] ) ) {
			return self::maybe_get_settings_link_to_enable_editing( $atts['form_id'] );
		}

		if ( empty( $atts['prefix'] ) ) {
			$link = add_query_arg(
                array(
					'frm_action' => 'edit',
					'entry'      => $entry_id,
                ),
                get_permalink( $atts['page_id'] ) 
            );

			if ( $atts['label'] ) {
				$link = '<a href="' . esc_url( $link ) . '" class="' . esc_attr( $atts['class'] ) . '">' . $atts['label'] . '</a>';
			}

			return $link;
		}

		$action          = $_POST && isset( $_POST['frm_action'] ) ? 'frm_action' : 'action';
		$form_action     = FrmAppHelper::get_post_param( $action, '', 'sanitize_title' );
		$posted_form_id  = FrmAppHelper::get_post_param( 'form_id', '', 'sanitize_title' );
		$posted_entry_id = FrmAppHelper::get_post_param( 'id', '', 'sanitize_title' );

		if ( $form_action === 'update' && $posted_form_id == $atts['form_id'] && $posted_entry_id == $entry_id ) {
			$errors = isset( $frm_vars['created_entries'][ $atts['form_id'] ] ) && isset( $frm_vars['created_entries'][ $atts['form_id'] ]['errors'] ) ? $frm_vars['created_entries'][ $atts['form_id'] ]['errors'] : array();

			if ( ! empty( $errors ) ) {
				return FrmFormsController::get_form_shortcode(
                    array(
						'id'             => $atts['form_id'],
						'entry_id'       => $entry_id,
						'fields'         => $atts['fields'],
						'exclude_fields' => $atts['exclude_fields'],
                    ) 
                );
			}

			$link .= "<script type='text/javascript'>document.addEventListener('DOMContentLoaded',function(){frmFrontForm.scrollToID('" . esc_js( $atts['prefix'] . $entry_id ) . "');});</script>";
		}

		if ( empty( $atts['title'] ) ) {
			$atts['title'] = $atts['label'];
		}

		if ( ! $atts['html_id'] ) {
			$atts['html_id'] = 'frm_edit_' . $entry_id;
		}

		self::load_form_scripts();

		$data = array(
			'entryid'   => $entry_id,
			'prefix'    => $atts['prefix'],
			'pageid'    => $atts['page_id'],
			'formid'    => $atts['form_id'],
			'cancel'    => $atts['cancel'],
			'edit'      => $atts['label'],
			'startpage' => $atts['start_page'],
		);
		if ( ! empty( $atts['fields'] ) ) {
			$data['fields'] = implode( ',', (array) $atts['fields'] );
		}
		if ( ! empty( $atts['exclude_fields'] ) ) {
			$data['exclude_fields'] = implode( ',', (array) $atts['exclude_fields'] );
		}

		$link .= '<span class="frm_edit_link_container">';
		$link .= '<a href="#" class="frm_inplace_edit frm_edit_link ' . esc_attr( $atts['class'] ) . '" id="' . esc_attr( $atts['html_id'] ) . '" title="' . esc_attr( $atts['title'] ) . '"';
		foreach ( $data as $name => $label ) {
			$link .= ' data-' . str_replace( '_', '', sanitize_title( $name ) ) . '="' . esc_attr( $label ) . '"';
		}
		$link .= '>' . wp_kses_post( $atts['label'] ) . "</a>\n";
		$link .= '</span>';

		return $link;
	}

	/**
	 * Show a link to enable editing entries on the front end if it is disabled and the user can enable it.
	 * This is likely an oversight. The user probably does not know to enable this option, or they forgot this step.
	 *
	 * @since 6.9.1
	 *
	 * @param int|string $form_id
	 * @return string
	 */
	private static function maybe_get_settings_link_to_enable_editing( $form_id ) {
		if ( ! $form_id || ! current_user_can( 'frm_edit_forms' ) ) {
			return '';
		}

		$form = FrmForm::getOne( $form_id );
		if ( ! $form || $form->editable ) {
			return '';
		}

		$output = esc_html__( 'Front-end editing is disabled.', 'formidable-pro' );

		$is_visual_views_preview = FrmAppHelper::doing_ajax() && 'frm_views_process_box_preview' === FrmAppHelper::get_post_param( 'action' );
		if ( ! $is_visual_views_preview ) {
			$url     = admin_url( 'admin.php?page=formidable&frm_action=settings&id=' . absint( $form->id ) . '&t=permissions_settings_settings' );
			$output .= ' <a href="' . esc_url( $url ) . '">' . esc_html__( 'Enable it in form settings' ) . '</a>';
		}

		return $output;
	}

	/**
	 * @param array $atts
	 * @return string
	 */
	public static function entry_update_field( $atts ) {
		global $frm_vars, $frm_update_link_count;

		$atts = shortcode_atts(
			array(
				'id'       => ( isset( $frm_vars['editing_entry'] ) ? $frm_vars['editing_entry'] : false ),
				'field_id' => false,
				'label'    => __( 'Update', 'formidable-pro' ),
				'class'    => '',
				'value'    => '',
				'message'  => '',
				'title'    => '',
			),
			$atts
		);

		if ( ! $atts['field_id'] ) {
			return esc_html__( 'You are missing options in your shortcode. A field_id is required.', 'formidable-pro' );
		}

		$entry_id = $atts['id'] && is_numeric( $atts['id'] ) ? absint( $atts['id'] ) : FrmAppHelper::get_param( 'entry', false, 'get', 'absint' );
		if ( ! $entry_id ) {
			return '';
		}

		$field = FrmField::getOne( $atts['field_id'] );
		if ( ! $field ) {
			return '';
		}

		if ( ! FrmProEntriesHelper::user_can_edit( $entry_id, $field->form_id ) ) {
			return self::maybe_get_settings_link_to_enable_editing( $field->form_id );
		}

		// Check if current value is equal to new value
		$current_val = FrmProEntryMetaHelper::get_post_or_meta_value( $entry_id, $field );
		if ( $current_val == $atts['value'] ) {
			return '';
		}

		self::load_form_scripts();

		if ( ! $frm_update_link_count ) {
			$frm_update_link_count = 0;
		}

		++$frm_update_link_count;

		if ( empty( $atts['title'] ) ) {
			$atts['title'] = $atts['label'];
		}

		$value   = htmlspecialchars( addslashes( $atts['value'] ), ENT_COMPAT );
		$message = htmlspecialchars( addslashes( $atts['message'] ), ENT_COMPAT );
		$onclick = "frmUpdateField({$entry_id},{$field->id},'{$value}','{$message}',{$frm_update_link_count});return false;";

		$html_id = "frm_update_field_{$entry_id}_{$field->id}_{$frm_update_link_count}";
		$class   = esc_attr( 'frm_update_field_link ' . $atts['class'] );
		$title   = esc_attr( $atts['title'] );

		$link = "<a href=\"#\" onclick=\"{$onclick}\" id=\"{$html_id}\" class=\"{$class}\" title=\"{$title}\">{$atts['label']}</a>";

		return $link;
	}

	/**
	 * @param array $atts
	 * @return string
	 */
	public static function entry_delete_link( $atts ) {
		global $post, $frm_vars;
		$atts = shortcode_atts(
			array(
				'id'      => isset( $frm_vars['editing_entry'] ) ? $frm_vars['editing_entry'] : false,
				'label'   => __( 'Delete' ),
				'confirm' => __( 'Are you sure you want to delete that entry?', 'formidable-pro' ),
				'class'   => '',
				'page_id' => $post ? $post->ID : 0,
				'html_id' => false,
				'prefix'  => '',
				'title'   => '',
			),
			$atts
		);

		$entry_id = FrmAppHelper::get_param( 'id', false, 'get', 'sanitize_text_field' );
		$entry_id = $atts['id'] && is_numeric( $atts['id'] ) ? $atts['id'] : ( FrmAppHelper::is_admin() ? $entry_id : FrmAppHelper::get_param( 'entry', false, 'get', 'sanitize_text_field' ) );

		if ( empty( $entry_id ) || ! FrmProEntriesHelper::user_can_delete( $entry_id ) ) {
			// User doesn't have permission to delete this entry
			return '';
		}

		self::load_form_scripts();

		if ( ! empty( $atts['prefix'] ) ) {
			if ( ! $atts['html_id'] ) {
				$atts['html_id'] = 'frm_delete_' . $entry_id;
			}

			$link = '<a href="#" class="frm_ajax_delete frm_delete_link ' . esc_attr( $atts['class'] ) . '" id="' . esc_attr( $atts['html_id'] ) . '" data-deleteconfirm="' . esc_attr( $atts['confirm'] ) . '" data-entryid="' . esc_attr( $entry_id ) . '" data-prefix="' . esc_attr( $atts['prefix'] ) . '">' . $atts['label'] . "</a>\n";
			return $link;
		}

		$link = '';

		// Delete entry now
		$action = FrmAppHelper::get_param( 'frm_action', '', 'get', 'sanitize_title' );
		if ( $action === 'destroy' ) {

			$nonce = FrmAppHelper::simple_get( '_wpnonce' );

			if ( ! wp_verify_nonce( $nonce ) ) {
				$frm_settings = FrmAppHelper::get_settings();
				wp_die( esc_html( $frm_settings->admin_permission ) );
			}

			$entry_key = FrmAppHelper::get_param( 'entry', '', 'get', 'absint' );
			if ( $entry_key && $entry_key == $entry_id ) {
				$link = self::ajax_destroy( false, false, false );
				if ( $link ) {
					$new_link = '<div class="frm_message">' . $link . '</div>';
					if ( empty( $atts['label'] ) ) {
						return;
					}

					if ( $link === self::entry_delete_message() ) {
						return $new_link;
					}

					$link = $new_link;

					unset( $new_link );
				}
			}
		}

		// if the global $post variable is not set ( ex. when the request is coming from an ajax call ).
		if ( empty( $atts['page_id'] ) ) {
			$atts['page_id'] = url_to_postid( FrmAppHelper::get_server_value( 'HTTP_REFERER' ) );
		}
		$delete_link = self::create_delete_link( $entry_id, $atts['page_id'] );

		if ( empty( $atts['label'] ) ) {
			$link .= $delete_link;
		} else {
			if ( empty( $atts['title'] ) ) {
				$atts['title'] = $atts['label'];
			}

			$link .= '<a href="' . esc_url( $delete_link ) . '" class="' . esc_attr( $atts['class'] ) . '" data-frmconfirm="' . esc_attr( $atts['confirm'] ) . '" title="' . esc_attr( $atts['title'] ) . '">' . $atts['label'] . '</a>' . "\n";
		}

		return $link;
	}

	/**
	 * Handle a [frm-field-value] shortcode.
	 *
	 * @param array $sc_atts
	 * @return string
	 */
	public static function get_field_value_shortcode( $sc_atts ) {
		$atts = shortcode_atts(
			array(
				'entry'        => false,
				'field_id'     => false,
				'user_id'      => false,
				'ip'           => false,
				'show'         => '',
				'format'       => '',
				'return_array' => false,
				'default'      => '',
				'truncate'     => false,
				'no_link'      => true,
				'more_text'    => '...',
			),
			$sc_atts
		);

		// Include all user-defined atts as well
		$atts = (array) $atts + (array) $sc_atts;

		// For reverse compatibility
		if ( isset( $atts['entry_id'] ) && ! $atts['entry'] ) {
			$atts['entry'] = $atts['entry_id'];
		}

		if ( ! $atts['field_id'] ) {
			return esc_html__( 'You are missing options in your shortcode. field_id is required.', 'formidable-pro' );
		}

		$field = FrmField::getOne( $atts['field_id'] );
		if ( ! $field ) {
			return $atts['default'];
		}

		$entries = self::get_frm_field_value_entry( $field, $atts );
		if ( ! $entries ) {
			return $atts['default'];
		}

		// Unset $atts['truncate'] so it does not get truncated when get_single_field_value is called.
		$truncate = $atts['truncate'];
		unset( $atts['truncate'] );

		$values = array();
		foreach ( $entries as $entry ) {
			$value = self::get_single_field_value( $entry, $field, $atts );
			if ( $value != '' ) {
				if ( is_array( $value ) ) {
					$value = implode( ',', $value );
				}
				$values[] = $value;
			}
		}

		$value = implode( ', ', $values );
		if ( $value == '' ) {
			$value = $atts['default'];
		}

		if ( FrmAppHelper::is_true( $truncate ) ) {
			$truncate = '50';
		}

		$atts['more_text'] = sanitize_text_field( $atts['more_text'] );
		$atts['truncate']  = $truncate && is_numeric( $truncate ) ? $truncate : '0';

		FrmProContent::trigger_shortcode_atts( $atts, false, array( 'show' => $atts['show'] ), $value );

		return $value;
	}

	/**
	 * @param stdClass $entry
	 * @param stdClass $field
	 * @param array    $atts
	 */
	private static function get_single_field_value( $entry, $field, $atts ) {
		$value            = FrmProEntryMetaHelper::get_post_or_meta_value( $entry, $field, $atts );
		$atts['type']     = $field->type;
		$atts['post_id']  = $entry->post_id;
		$atts['entry_id'] = $entry->id;

		self::add_frm_field_value_atts_for_file_upload_field( $field, $atts );

		$tested_field_types = array( 'time', 'file' );

		if ( in_array( $field->type, $tested_field_types, true ) || ! empty( $atts['format'] ) || ! empty( $atts['show'] ) ) {

			if ( empty( $atts['format'] ) ) {
				unset( $atts['format'] );
			}

			$value = FrmFieldsHelper::get_display_value( $value, $field, $atts );
		} else {
			$value = FrmEntriesHelper::display_value( $value, $field, $atts );
		}

		return $value;
	}

	/**
	 * Add some default attributes for a file upload field in the frm-field-value shortcode
	 *
	 * @since 2.02.11
	 *
	 * @param object $field
	 * @param array $atts
	 */
	private static function add_frm_field_value_atts_for_file_upload_field( $field, &$atts ) {
		if ( $field->type !== 'file' ) {
			return;
		}

		if ( ! isset( $atts['show_filename'] ) ) {
			$atts['show_filename'] = false;
		}

		if ( ! isset( $atts['size'] ) ) {
			$atts['size'] = 'thumbnail';
		}

		// Show the image by default, for reverse compatibility
		if ( ! isset( $atts['html'] ) ) {

			if ( ! isset( $atts['show_image'] ) ) {
				$atts['show_image'] = 1;
			}

			if ( ! isset( $atts['add_link'] ) ) {
				$atts['add_link'] = 1;
			}
		}
	}

	/**
	 * Get entry object for frm_field_value shortcode
	 * Uses user_id, entry, or ip atts to fetch the entry
	 *
	 * @since 2.0.13
	 * @param object $field
	 * @param array $atts
	 * @return array $entry
	 */
	private static function get_frm_field_value_entry( $field, &$atts ) {
		$query = array( 'form_id' => $field->form_id );
		$order = array( 'order_by' => 'created_at DESC' );

		if ( $atts['user_id'] ) {
			// make sure we are not getting entries for logged-out users
			$query['user_id']   = (int) FrmAppHelper::get_user_id_param( $atts['user_id'] );
			$query['user_id !'] = 0;
		}

		if ( $atts['entry'] ) {
			if ( ! is_numeric( $atts['entry'] ) ) {
				$atts['entry'] = FrmAppHelper::simple_get( $atts['entry'], 'sanitize_title', $atts['entry'] );
			}

			if ( empty( $atts['entry'] ) ) {
				return array();
			}

			if ( is_numeric( $atts['entry'] ) ) {
				$query[] = array(
					'or'             => 1,
					'id'             => $atts['entry'],
					'parent_item_id' => $atts['entry'],
				);
			} else {
				$query[] = array( 'item_key' => $atts['entry'] );
			}
		} else {
			// get the latest entry
			$order['limit'] = 1;
		}

		if ( $atts['ip'] ) {
			$use_current = $atts['ip'] === true || $atts['ip'] === '1' || $atts['ip'] === 'current';
			$query['ip'] = $use_current ? FrmAppHelper::get_ip_address() : $atts['ip'];
		}

		$entry = FrmDb::get_results( 'frm_items', $query, 'post_id, id', $order );

		return $entry;
	}

	public static function show_entry_shortcode( $atts ) {
		return FrmEntriesController::show_entry_shortcode( $atts );
	}

	/**
	 * Alternate Row Color for Default HTML
	 *
	 * @return string
	 */
	public static function change_row_color() {
		global $frm_email_col;

		$bg_color = 'bg_color';
		if ( $frm_email_col ) {
			$bg_color     .= '_active';
			$frm_email_col = false;
		} else {
			$frm_email_col = true;
		}

		$bg_color  = FrmStylesController::get_style_val( $bg_color );
		$alt_color = 'background-color:#' . $bg_color . ';';
		return $alt_color;
	}

	/**
	 * @param int $entry_id
	 * @param int $form_id
	 * @return void
	 */
	public static function maybe_set_cookie( $entry_id, $form_id ) {
		if ( ! self::should_set_cookie() ) {
			return;
		}

		if ( isset( $_POST ) && isset( $_POST['frm_skip_cookie'] ) ) {
			self::set_cookie( $entry_id, $form_id );
			return;
		}

		include FrmProAppHelper::plugin_path() . '/classes/views/frmpro-entries/set_cookie.php';
	}

	/**
	 * Check for WordPress constants to avoid setting cookies while importing, doing ajax, using REST, and the CLI.
	 *
	 * @since 5.5.7
	 *
	 * @return bool False if any of the constants are set and true.
	 */
	private static function should_set_cookie() {
		$constants = array( 'WP_IMPORTING', 'DOING_AJAX', 'REST_REQUEST', 'WP_CLI' );

		foreach ( $constants as $constant ) {
			if ( defined( $constant ) && constant( $constant ) ) {
				return false;
			}
		}

		return true;
	}

	/* AJAX */

	public static function wp_ajax_destroy() {
		check_ajax_referer( 'frm_ajax', 'nonce' );

		$echo = true;
		if ( isset( $_REQUEST['redirect'] ) ) {
			// Don't echo if redirecting.
			$echo = false;
		}

		$message = self::ajax_destroy( false, true, $echo );
		if ( ! $echo ) {
			// redirect instead of loading a blank page
			$redirect_url = esc_url_raw( get_permalink( FrmAppHelper::get_param( 'redirect', '', 'request', 'sanitize_text_field' ) ) );
			if ( $message === '' ) {
				$redirect_url = add_query_arg( array( 'frm_entry_delete_message' => 'success' ), $redirect_url );
			}
			wp_redirect( $redirect_url );
			die();
		}

		wp_die();
	}

	/**
	 * @since 6.17
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	public static function maybe_add_entry_delete_message( $content ) {
		if ( ! self::$delete_link_created || 'success' !== FrmAppHelper::simple_get( 'frm_entry_delete_message' ) ) {
			return $content;
		}

		$message = '<div class="' . esc_attr( FrmFormsHelper::get_form_style_class() ) . '"><div class="frm_message">' . self::entry_delete_message() . '</div></div>';

		return $message . $content;
	}

	/**
	 * @param bool  $ajax
	 * @param bool  $echo
	 * @param false $form_id
	 */
	public static function ajax_destroy( $form_id = false, $ajax = true, $echo = true ) {
		global $wpdb, $frm_vars;

		$entry_key = FrmAppHelper::get_param( 'entry', '', 'get', 'sanitize_title' );
		if ( ! $form_id ) {
			$form_id = FrmAppHelper::get_param( 'form_id', '', 'get', 'absint' );
		}

		if ( ! $entry_key ) {
			return;
		}

		if ( isset( $frm_vars['deleted_entries'] ) && is_array( $frm_vars['deleted_entries'] ) && in_array( $entry_key, $frm_vars['deleted_entries'] ) ) {
			return;
		}

		if ( is_numeric( $entry_key ) ) {
			$where = array( 'id' => $entry_key );
		} else {
			$where = array( 'item_key' => $entry_key );
		}

		$entry = FrmDb::get_row( $wpdb->prefix . 'frm_items', $where, 'id, form_id, is_draft, user_id' );
		unset( $where );

		if ( ! $entry || ( $form_id && $entry->form_id != (int) $form_id ) ) {
			return;
		}

		$message = self::maybe_delete_entry( $entry );
		if ( $message && ! is_numeric( $message ) ) {
			if ( $echo ) {
				echo '<div class="frm_message">' . esc_html( $message ) . '</div>';
			}
			return;
		}

		if ( empty( $frm_vars['deleted_entries'] ) ) {
			$frm_vars['deleted_entries'] = array();
		}
		$frm_vars['deleted_entries'][] = $entry->id;

		if ( $ajax && $echo ) {
			$message = 'success';
			echo 'success';
		} elseif ( ! $ajax ) {
			$message = apply_filters( 'frm_delete_message', self::entry_delete_message(), $entry );

			if ( $echo ) {
				echo '<div class="frm_message">' . esc_html( $message ) . '</div>';
			}
		} else {
			$message = '';
		}

		return $message;
	}

	/**
	 * Returns entry delete message from settings.
	 *
	 * @since 6.17
	 *
	 * @return string
	 */
	private static function entry_delete_message() {
		$frmpro_settings = FrmProAppHelper::get_settings();
		return $frmpro_settings->entry_delete_message;
	}

	public static function maybe_delete_entry( $entry ) {
		FrmEntry::maybe_get_entry( $entry );

		if ( ! $entry || ! FrmProEntriesHelper::user_can_delete( $entry ) ) {
			return __( 'There was an error deleting that entry', 'formidable-pro' );
		}

		return FrmEntry::destroy( $entry->id );
	}

	public static function send_email() {
		if ( current_user_can( 'frm_view_forms' ) || current_user_can( 'frm_edit_forms' ) || current_user_can( 'frm_edit_entries' ) ) {
			if ( FrmAppHelper::doing_ajax() ) {
				check_ajax_referer( 'frm_ajax', 'nonce' );
			}
			$entry_id = FrmAppHelper::get_param( 'entry_id', '', 'get', 'absint' );
			$form_id  = FrmAppHelper::get_param( 'form_id', '', 'get', 'absint' );

			add_filter( 'frm_echo_emails', '__return_true' );
			ob_start();
			FrmFormActionsController::trigger_actions( 'create', $form_id, $entry_id, 'email' );
			$emails = ob_get_contents();
			ob_end_clean();

			if ( empty( $emails ) ) {
				$emails = __( 'no one', 'formidable-pro' );
			}

			printf( esc_html__( 'Resent to %s', 'formidable-pro' ), esc_html( $emails ) );
			self::suggest_smtp();
		} else {
			esc_html_e( 'Resent to No one! You do not have permission', 'formidable-pro' );
		}
		wp_die();
	}

	/**
	 * Include a link to the SMTP page after an email is resent.
	 *
	 * @since 4.04.04
	 */
	private static function suggest_smtp() {
		$suggest_smtp = class_exists( 'FrmSMTPController' ) && current_user_can( 'activate_plugins' ) && ! function_exists( 'wp_mail_smtp' );
		if ( ! $suggest_smtp ) {
			return;
		}

		$link = admin_url( 'admin.php?page=formidable-smtp' );
		?>
		<p>
			<a href="<?php echo esc_url( $link ); ?>" class="frm_pro_tip">
				<?php FrmAppHelper::icon_by_class( 'frmfont frm_star_full_icon', array( 'aria-hidden' => 'true' ) ); ?>
				<?php esc_html_e( 'Not receiving emails?', 'formidable-pro' ); ?>
				<span class="frm-tip-cta">
					<?php esc_html_e( 'Setup SMTP.', 'formidable-pro' ); ?>
				</span>
			</a>
		</p>
		<?php
	}

	public static function ajax_set_cookie() {
		check_ajax_referer( 'frm_ajax', 'nonce' );
		self::set_cookie();
		wp_die();
	}

	/**
	 * @param false|int|string $entry_id
	 * @param false|int|string $form_id
	 * @return void
	 */
	public static function set_cookie( $entry_id = false, $form_id = false ) {
		if ( headers_sent() ) {
			return;
		}

		if ( ! apply_filters( 'frm_create_cookies', true ) ) {
			return;
		}

		if ( ! $entry_id ) {
			$entry_id = FrmAppHelper::get_param( 'entry_id', '', 'get', 'absint' );
		}

		if ( ! $form_id ) {
			$form_id = FrmAppHelper::get_param( 'form_id', '', 'get', 'absint' );
		}

		$form = FrmForm::getOne( $form_id );
		if ( ! FrmProFormsHelper::check_single_entry_type( $form->options, 'cookie' ) ) {
			return;
		}

		$expiration = isset( $form->options['cookie_expiration'] ) ? ( (float) $form->options['cookie_expiration'] * 60 * 60 ) : 30000000;
		$expiration = apply_filters( 'frm_cookie_expiration', $expiration, $form_id, $entry_id );
		setcookie( 'frm_form' . $form_id . '_' . COOKIEHASH, current_time( 'mysql', 1 ), time() + $expiration, COOKIEPATH, COOKIE_DOMAIN, is_ssl() );
	}

	/**
	 * Create an entry when the ajax_submit option is on.
	 *
	 * As of v6.2 Lite now has an ajax_create function.
	 * However, this function includes additional support for Pro-specific features as well.
	 * Those features include:
	 * - forms with multiple pages.
	 * - file fields.
	 * - updating entries (in-place edit).
	 * - include_fields/exclude_fields/get form shortcode options.
	 * - loading scripts for Pro fields (chosen, datepicker, input mask, dropzone).
	 *
	 * @return void
	 */
	public static function ajax_create() {
		if ( ! FrmAppHelper::doing_ajax() || ! isset( $_POST['form_id'] ) ) {
			// normally, this function would be triggered with the wp_ajax hook, but we need it fired sooner
			return;
		}

		$allowed_actions = array( 'frm_entries_create', 'frm_entries_update' );
		if ( ! in_array( FrmAppHelper::get_post_param( 'action', '', 'sanitize_title' ), $allowed_actions, true ) ) {
			// allow ajax creating and updating
			return;
		}

		$response = array(
			'errors'  => array(),
			'content' => '',
			'pass'    => false,
		);

		$form_id = FrmAppHelper::get_post_param( 'form_id', 0, 'absint' );
		if ( ! $form_id ) {
			echo json_encode( $response );
			wp_die();
		}

		$form = FrmForm::getOne( $form_id );
		if ( ! $form ) {
			echo json_encode( $response );
			wp_die();
		}

		$is_ajax_on     = FrmProForm::is_ajax_on( $form );
		$no_ajax_fields = $is_ajax_on ? false : array( 'file' );
		$errors         = FrmEntryValidate::validate( wp_unslash( $_POST ), $no_ajax_fields );

		if ( ! $errors ) {
			if ( $is_ajax_on || self::should_force_ajax_submit( (int) $form->id ) ) {
				global $frm_vars;
				$frm_vars['ajax']       = true;
				$frm_vars['css_loaded'] = true;

				self::maybe_include_exclude_fields( $form->id );

				// don't load scripts if we are going backwards in the form
				$going_backwards = FrmProFormsHelper::going_to_prev( $form->id );

				// save the entry if there is not another page or when saving a draft
				if ( ( ! isset( $_POST[ 'frm_page_order_' . $form->id ] ) && ! $going_backwards ) || FrmProFormsHelper::saving_draft() ) {
					$processed = true;
					FrmEntriesController::process_entry( $errors, true );
				} else {
					self::maybe_autosave_on_page_turn( $errors, $form );
					$response['page'] = FrmProFormsHelper::get_the_page_number( $form->id );
				}

				$get = FrmProFormState::get_from_request( 'get', array() );
				if ( $get ) {
					FrmProAppController::set_get( $get );
				}

				self::maybe_include_exclude_fields( $form->id );
				$title                = FrmProFormState::get_from_request( 'title', false );
				$description          = FrmProFormState::get_from_request( 'description', false );
				$response['content'] .= FrmFormsController::show_form( $form->id, '', $title, $description );

				// trigger the footer scripts if there is a form to show
				if ( $errors || ! isset( $processed ) || ! empty( $frm_vars['forms_loaded'] ) ) {
					ob_start();
					FrmProFormsController::print_ajax_scripts( $going_backwards ? 'none' : '' );
					FrmProFormsController::footer_js();
					$response['content'] .= ob_get_contents();
					ob_end_clean();

					// Mark the end of added footer content
					$response['content'] .= '<span class="frm_end_ajax_' . $form->id . '"></span>';
				}
			}
		} else {
			$obj = array();
			foreach ( $errors as $field => $error ) {
				$field_id         = str_replace( 'field', '', $field );
				$error            = self::maybe_modify_ajax_error( $error, $field_id, $form, $errors );
				$obj[ $field_id ] = $error;
			}
			$response['errors'] = $obj;

			$invalid_msg               = FrmFormsHelper::get_invalid_error_message( array( 'form' => $form ) );
			$response['error_message'] = FrmFormsHelper::get_success_message(
				array(
					'message'  => $invalid_msg,
					'form'     => $form,
					'entry_id' => 0,
					'class'    => FrmFormsHelper::form_error_class(),
				)
			);
		}

		$response = self::check_for_failed_form_submission( $response, $form->id );

		if ( FrmProFieldCaptcha::posting_captcha_data() ) {
			$checked = FrmProFieldCaptcha::checked();
			if ( $checked ) {
				$response['recaptcha'] = $checked;
			}
		}

		echo json_encode( $response );
		wp_die();
	}

	/**
	 * In-place edito CAPTCHA validation fails if AJAX submit is not on.
	 * To avoid errors, make sure a form with CAPTCHA fields forces AJAX submit.
	 *
	 * @since 6.7
	 *
	 * @param int $form_id
	 * @return bool
	 */
	private static function should_force_ajax_submit( $form_id ) {
		$inplace_edit = FrmProFormState::get_from_request( 'inplace_edit', false );
		if ( ! $inplace_edit ) {
			return false;
		}
		return (bool) FrmProFormsHelper::has_field( 'captcha', $form_id, true );
	}

	/**
	 * Confirm that the result of calling FrmFormsController::show_form added the failed message for a duplicate entry to the HTML.
	 * If it did, move the message to the errors key instead of returning the content.
	 *
	 * @since 5.5.4
	 *
	 * @param array      $response
	 * @param int|string $form_id
	 * @return array
	 */
	private static function check_for_failed_form_submission( $response, $form_id ) {
		$frm_settings = FrmAppHelper::get_settings( array( 'current_form' => $form_id ) );

		if ( false !== strpos( $response['content'], $frm_settings->failed_msg ) ) {
			$response['errors']['failed'] = $frm_settings->failed_msg;
			$response['content']          = '';
		}

		return $response;
	}

	/**
	 * If a field has custom HTML for errors, apply it around the message.
	 *
	 * @since 5.0.03
	 *
	 * @param string   $error
	 * @param string   $field_id
	 * @param stdClass $form the form being submitted (not necessarily the field's form when embedded/repeated).
	 * @param array    $errors all errors that were caught in this form submission, passed into the frm_before_replace_shortcodes filter for reference.
	 * @return string
	 */
	private static function maybe_modify_ajax_error( $error, $field_id, $form, $errors ) {
		if ( ! is_callable( 'FrmFieldsController::pull_custom_error_body_from_custom_html' ) ) {
			// this function only exists since formidable lite 5.0.03
			// if the lite version has not been updated, leave the error unmodified.
			return $error;
		}

		$repeater_iteration = false;

		if ( false !== strpos( $field_id, '-' ) ) {
			// repeated fields look like field_id-repeater_id-iteration, so pull the first value for the field id.
			list( $use_field_id, $repeater_iteration ) = explode( '-', $field_id );
		} else {
			$use_field_id = $field_id;
		}

		if ( ! is_numeric( $use_field_id ) ) {
			return $error;
		}

		$use_field = FrmField::getOne( $use_field_id );

		if ( ! $use_field ) {
			return $error;
		}

		$use_field  = FrmFieldsHelper::setup_edit_vars( $use_field );
		$error_body = FrmFieldsController::pull_custom_error_body_from_custom_html( $form, $use_field, $errors );

		if ( false !== $error_body ) {
			// Error key should be field key.
			// If this is a repeater, we include the iteration on the end like field1-2.
			$error_key = $use_field['field_key'];
			if ( false !== $repeater_iteration ) {
				$error_key .= '-' . $repeater_iteration;
			}

			$error = str_replace( '[error]', $error, $error_body );
			$error = str_replace( '[key]', $error_key, $error );
		}

		return $error;
	}

	/**
	 * @param int $form_id
	 * @return void
	 */
	public static function maybe_include_exclude_fields( $form_id ) {
		$include_fields = FrmProFormState::get_from_request( 'include_fields', array() );
		if ( $include_fields ) {
			global $frm_vars;
			$frm_vars['show_fields'] = $include_fields;
		}
	}

	public static function setup_edit_vars( $values ) {
		if ( ! isset( $values['edit_value'] ) ) {
			$values['edit_value'] = $_POST && isset( $_POST['options']['edit_value'] ) ? wp_kses_post( $_POST['options']['edit_value'] ) : __( 'Update', 'formidable-pro' );
		}

		if ( ! isset( $values['edit_msg'] ) ) {
			if ( $_POST && isset( $_POST['options']['edit_msg'] ) ) {
				$values['edit_msg'] = wp_kses_post( $_POST['options']['edit_msg'] );
			} else {
				$frmpro_settings    = FrmProAppHelper::get_settings();
				$values['edit_msg'] = $frmpro_settings->edit_msg;
			}
		}

		return $values;
	}

	public static function edit_entry_ajax() {
		$id             = FrmAppHelper::get_param( 'id', '', 'post', 'absint' );
		$entry_id       = FrmAppHelper::get_param( 'entry_id', 0, 'post', 'absint' );
		$post_id        = FrmAppHelper::get_param( 'post_id', 0, 'post', 'sanitize_title' );
		$fields         = FrmAppHelper::get_param( 'fields', array(), 'post', 'sanitize_text_field' );
		$exclude_fields = FrmAppHelper::get_param( 'exclude_fields', array(), 'post', 'sanitize_text_field' );
		$start_page     = FrmAppHelper::get_param( 'start_page', 1, 'post', 'absint' );

		global $frm_vars;
		$frm_vars['footer_loaded'] = true;
		$frm_vars['inplace_edit']  = true;

		FrmProFormState::set_initial_value( 'inplace_edit', true );

		if ( $entry_id ) {
			$_GET['entry'] = $entry_id;
		}

		if ( $post_id && is_numeric( $post_id ) ) {
			global $post;
			if ( ! $post ) {
				$post = get_post( $post_id );
			}
		}

		FrmProFormsController::mark_jquery_as_loaded();

		$atts = compact( 'id', 'entry_id', 'fields', 'exclude_fields' );

		if ( 1 !== $start_page ) {
			self::maybe_set_page_from_attribute( $id, $start_page );
		} else {
			self::maybe_set_page( $atts );
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo FrmFormsController::get_form_shortcode( $atts );

		FrmProFormsController::print_ajax_scripts( 'all' );

		wp_die();
	}

	/**
	 * @param int $form_id
	 * @param int $start_page
	 * @return void
	 */
	public static function maybe_set_page_from_attribute( $form_id, $start_page ) {
		$start_page_order = self::get_order_of_start_page_attribute( $form_id, $start_page );
		if ( $start_page_order > 1 ) {
			self::set_page( $form_id, $start_page_order );
		}
	}

	/**
	 * @param int $form_id
	 * @param int $page
	 * @return int
	 */
	private static function get_order_of_start_page_attribute( $form_id, $page ) {
		$page_break_orders = self::get_page_break_orders( $form_id );
		$index             = $page - 2; // offset by 2 because the first page break is not a real field and arrays are 0 indexed.
		return array_key_exists( $index, $page_break_orders ) ? $page_break_orders[ $index ] : 1;
	}

	/**
	 * @param int $form_id
	 * @param int $page_break_order
	 */
	private static function set_page( $form_id, $page_break_order ) {
		$_POST[ 'frm_page_order_' . $form_id ] = $page_break_order;
	}

	/**
	 * @param array $atts including keys id (form id), entry_id, fields, exclude_fields.
	 */
	private static function maybe_set_page( $atts ) {
		$page = self::get_page_from_attributes( $atts );
		if ( 1 !== $page ) {
			self::set_page( $atts['id'], $page );
		}
	}

	/**
	 * @param array $atts including keys id (form id), entry_id, fields, exclude_fields.
	 * @return int the page break field order to start on.
	 */
	private static function get_page_from_attributes( $atts ) {
		$page = 1;

		if ( empty( $atts['id'] ) || ( empty( $atts['fields'] ) && empty( $atts['exclude_fields'] ) ) ) {
			// return the first page if information is missing or all fields are present.
			return $page;
		}

		$form_id           = $atts['id'];
		$page_break_orders = self::get_page_break_orders( $form_id );

		if ( ! $page_break_orders ) {
			// stop if there are no page breaks for this form.
			return $page;
		}

		$first_field_order = self::get_order_of_first_field( $atts );

		foreach ( $page_break_orders as $page_break_order ) {
			if ( $page_break_order > $first_field_order ) {
				break;
			}
			$page = $page_break_order;
		}

		return $page;
	}

	/**
	 * @param int $form_id
	 * @return array<int> field orders for all page break fields.
	 */
	private static function get_page_break_orders( $form_id ) {
		return FrmDb::get_col(
			'frm_fields',
			array(
				'type'    => 'break',
				'form_id' => $form_id,
			),
			'field_order',
			array(
				'order_by' => 'field_order',
			)
		);
	}

	/**
	 * Get the field_order of the first field based off of what is included and excluded with field attributes.
	 *
	 * @param array $atts including keys id (form id), entry_id, fields, exclude_fields.
	 * @return int the lowest field_order value from the set of fields.
	 */
	private static function get_order_of_first_field( $atts ) {
		$includes = ! empty( $atts['fields'] ) ? self::create_id_key_condition_pair( $atts['fields'] ) : array();
		$excludes = ! empty( $atts['exclude_fields'] ) ? self::create_id_key_condition_pair( $atts['exclude_fields'], false ) : array();
		$where    = self::build_where_for_first_field_check( $atts['id'], $includes, $excludes );
		$args     = array( 'order_by' => 'field_order' );
		return FrmDb::get_var( 'frm_fields', $where, 'field_order', $args );
	}

	/**
	 * @param array|string $fields
	 * @param bool         $include
	 * @return array
	 */
	private static function create_id_key_condition_pair( $fields, $include = true ) {
		$fields = self::maybe_explode( $fields );
		$ids    = self::pull_ids( $fields );
		$keys   = self::pull_keys( $fields );
		$pair   = array();
		$suffix = $include ? '' : ' not';

		if ( $ids ) {
			$pair[ 'id' . $suffix ] = $ids;
		}

		if ( $keys ) {
			$pair[ 'field_key' . $suffix ] = $keys;
			if ( $ids ) {
				$pair['or'] = 1;
			}
		}

		return $pair;
	}

	/**
	 * @param int $form_id
	 * @param array $includes
	 * @param array $excludes
	 * @return array
	 */
	private static function build_where_for_first_field_check( $form_id, $includes, $excludes ) {
		$where = array();

		if ( $includes ) {
			$where[] = $includes;
		}

		if ( $excludes ) {
			$where[] = $excludes;
		}

		$where[] = array(
			'form_id' => $form_id,
			'type !'  => 'break',
		);

		return $where;
	}

	/**
	 * @param array|string $ids
	 */
	private static function maybe_explode( $ids ) {
		return is_array( $ids ) ? $ids : explode( ',', $ids );
	}

	/**
	 * @param array $values
	 * @return array<int> ids
	 */
	private static function pull_ids( $values ) {
		return array_filter( $values, 'is_numeric' );
	}

	/**
	 * @param array $values
	 * @return array<string> keys
	 */
	private static function pull_keys( $values ) {
		return array_filter(
			$values,
			function ( $value ) {
				return ! is_numeric( $value );
			}
		);
	}

	public static function update_field_ajax() {
		//check_ajax_referer( 'frm_ajax', 'nonce' );

		$entry_id = FrmAppHelper::get_param( 'entry_id', 0, 'post', 'absint' );
		$field_id = FrmAppHelper::get_param( 'field_id', 0, 'post', 'sanitize_title' );
		$value    = FrmAppHelper::get_param( 'value', '', 'post', 'wp_kses_post' );
		FrmAppHelper::sanitize_value( 'wp_specialchars_decode', $value );

		FrmField::maybe_get_field( $field_id );
		if ( $field_id && FrmProEntriesHelper::user_can_edit( $entry_id, $field_id->form_id ) ) {
			$updated = FrmProEntryMeta::update_single_field( compact( 'entry_id', 'field_id', 'value' ) );
			echo esc_html( $updated );
		}

		wp_die();
	}

	public static function redirect_url( $url ) {
		$url = str_replace( array( ' ', '[', ']', '|', '@' ), array( '%20', '%5B', '%5D', '%7C', '%40' ), $url );
		return $url;
	}

	/**
	 * @param stdClass $field
	 * @return bool
	 */
	public static function field_column_is_sortable( $sortable, $field ) {
		if ( ! $sortable && ! empty( $field->field_options['post_field'] ) ) {
			$sortable_options = array( 'post_title', 'post_content', 'post_excerpt', 'post_name', 'post_date', 'post_custom', 'post_status' );
			$sortable         = in_array( $field->field_options['post_field'], $sortable_options, true );
		}
		return $sortable;
	}

	/**
	 * @param string $sort
	 * @param int    $field_id
	 * @param array  $field_options
	 * @return string
	 */
	public static function handle_field_column_sort( $sort, $field_id, $field_options ) {
		if ( '' !== $sort || empty( $field_options['post_field'] ) ) {
			return $sort;
		}

		global $wpdb;

		if ( 'post_custom' === $field_options['post_field'] ) {
			if ( empty( $field_options['custom_field'] ) ) {
				return '';
			}

			$meta_key = sanitize_key( $field_options['custom_field'] );
			return ', (SELECT m.meta_value FROM ' . $wpdb->prefix . 'postmeta m INNER JOIN ' . $wpdb->prefix . 'frm_items i ON i.post_id=m.post_id WHERE m.meta_key = "' . esc_sql( $meta_key ) . '" AND i.id = it.id) as meta_' . $field_id;
		}

		$column = sanitize_key( $field_options['post_field'] );
		return ', (SELECT p.' . $column . ' FROM ' . $wpdb->prefix . 'posts p INNER JOIN ' . $wpdb->prefix . 'frm_items i ON i.post_id=p.ID WHERE i.id = it.id) as meta_' . $field_id;
	}

	/**
	 * AJAX handler for deleting draft entry.
	 *
	 * @since 5.4
	 */
	public static function delete_draft_entry_ajax() {
		check_ajax_referer( 'frm_ajax' );

		$form_id = FrmAppHelper::get_post_param( 'form', 0, 'intval' );
		if ( ! $form_id ) {
			wp_send_json_error();
		}

		$user_id = get_current_user_id();

		$entry_ids = FrmDb::get_col(
			'frm_items',
			array(
				'is_draft' => 1,
				'form_id'  => $form_id,
				'user_id'  => $user_id,
			)
		);

		if ( $entry_ids ) {
			foreach ( $entry_ids as $entry_id ) {
				FrmEntry::destroy( $entry_id );
			}
		}

		wp_send_json_success();
	}

	/**
	 * Add navigation buttons to the "View Entry" page.
	 *
	 * @since 6.4.1
	 *
	 * @param array $args Associative array with 'id' for entry ID and 'form' for form object.
	 * @return void
	 */
	public static function add_show_page_navigation( $args ) {
		$id   = $args['id'];
		$form = $args['form'];

		FrmProEntriesHelper::get_entry_navigation( $id, $form->id, 'show' );
	}

	/**
	 * Since v6.7.1 of Lite most HTML is stripped from front end input.
	 * Since Pro includes rich text fields, we should allow the safe tags.
	 *
	 * @since 6.8
	 *
	 * @param array $allowed_html
	 * @return array
	 */
	public static function allow_rich_text_field_tags( $allowed_html ) {
		$allowed_html['h1']         = array();
		$allowed_html['h2']         = array();
		$allowed_html['h3']         = array();
		$allowed_html['h4']         = array();
		$allowed_html['h5']         = array();
		$allowed_html['h6']         = array();
		$allowed_html['pre']        = array();
		$allowed_html['hr']         = array();
		$allowed_html['blockquote'] = array();
		$allowed_html['code']       = array();
		$allowed_html['del']        = array(
			'datetime' => true,
		);
		$allowed_html['ins']        = array(
			'datetime' => true,
		);
		return $allowed_html;
	}
}
