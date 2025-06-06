<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 4.05
 */
class FrmInboxController {

	/**
	 * Get the HTML for the inbox notice.
	 *
	 * @since 4.05
	 * @since 6.8.4 The $filtered parameter was added.
	 *
	 * @param bool $filtered Set this to false to avoid the frm_inbox_badge filter.
	 * @return string
	 */
	public static function get_notice_count( $filtered = true ) {
		FrmFormMigratorsHelper::maybe_add_to_inbox();

		$inbox = new FrmInbox();
		return $inbox->unread_html( $filtered );
	}

	/**
	 * @since 6.8
	 *
	 * @return array
	 */
	public static function get_inbox_messages() {
		self::add_tracking_request();
		self::remove_free_template_message();

		$inbox              = new FrmInbox();
		$unread_messages    = $inbox->get_messages();
		$dismissed_messages = $unread_messages;

		$inbox->filter_messages( $unread_messages, 'filter' );
		$inbox->filter_messages( $dismissed_messages, 'dismissed' );

		return array(
			'unread'    => array_reverse( $unread_messages ),
			'dismissed' => array_reverse( $dismissed_messages ),
			'user'      => wp_get_current_user(),
		);
	}

	/**
	 * @since 4.05
	 *
	 * @return void
	 */
	public static function dismiss_message() {
		check_ajax_referer( 'frm_ajax', 'nonce' );
		FrmAppHelper::permission_check( 'frm_change_settings' );

		$key = FrmAppHelper::get_param( 'key', '', 'post', 'sanitize_text_field' );
		if ( ! empty( $key ) ) {
			$message = new FrmInbox();
			$message->dismiss( $key );

			if ( $key === 'review' ) {
				$reviews = new FrmReviews();
				$reviews->dismiss_review();
			}

			if ( $key === 'onboarding_wizard' ) {
				// Delete the skipped option or the inbox message will continue to get added.
				delete_option( FrmOnboardingWizardController::ONBOARDING_SKIPPED_OPTION );
			}
		}

		wp_die();
	}

	/**
	 * @since 4.05
	 *
	 * @return void
	 */
	private static function add_tracking_request() {
		$settings = FrmAppHelper::get_settings();
		if ( $settings->tracking ) {
			return;
		}

		$link = admin_url( 'admin.php?page=formidable-settings&t=misc_settings' );

		$message = new FrmInbox();
		$message->add_message(
			array(
				'key'     => 'usage',
				'message' => 'Gathering usage data allows us to improve Formidable. Your forms will be considered as we evaluate new features, judge the quality of an update, or determine if an improvement makes sense. You can always visit the <a href="' . esc_url( $link ) . '">Global Settings</a> and choose to stop sharing data. <a href="https://formidableforms.com/knowledgebase/global-settings-overview/#kb-usage-tracking" target="_blank" rel="noopener noreferrer">Read more about what data we collect</a>.',
				'subject' => __( 'Help Formidable improve with usage tracking', 'formidable' ),
				'cta'     => '<a href="#" class="frm-button-secondary frm_inbox_dismiss">Dismiss</a> <a href="' . esc_url( $link ) . '" class="button-primary frm-button-primary frm_inbox_dismiss">Activate usage tracking</a>',
				'type'    => 'feedback',
			)
		);
	}

	/**
	 * Adds free template design.
	 *
	 * @since 4.10.03
	 *
	 * @return void
	 */
	private static function remove_free_template_message() {
		if ( ! FrmAppHelper::pro_is_installed() ) {
			$message = new FrmInbox();
			$message->dismiss( 'free_templates' );
		}
	}

	/**
	 * @since 4.06
	 * @deprecated 6.16
	 *
	 * @return void
	 */
	public static function dismiss_all_button( $atts ) {
		_deprecated_function( __METHOD__, '6.16' );
	}
}
