<?php

class WPML_Config_Update_Integrator {
	/** @var WPML_Config_Update_Log */
	private $log;
	/** @var WPML_Config_Update */
	private $worker;

	/**
	 * @param WPML_Log                $log
	 * @param WPML_Config_Update|null $worker
	 */
	public function __construct( WPML_Log $log, WPML_Config_Update $worker = null ) {
		$this->log    = $log;
		$this->worker = $worker;
	}

	/**
	 * @return WPML_Config_Update
	 */
	public function get_worker() {
		if ( null === $this->worker ) {
			global $sitepress;
			$http         = new WP_Http();
			$this->worker = new WPML_Config_Update( $sitepress, $http, $this->log );
		}

		return $this->worker;
	}

	/**
	 * @param WPML_Config_Update $worker
	 */
	public function set_worker( WPML_Config_Update $worker ) {
		$this->worker = $worker;
	}

	public function add_hooks() {
		add_action( 'update_wpml_config_index', array( $this, 'update_event_cron' ) );
		add_action( 'wp_ajax_update_wpml_config_index', array( $this, 'update_event_ajax' ) );
		add_action( 'after_switch_theme', array( $this, 'update_event' ) );
		add_action( 'activated_plugin', array( $this, 'update_event' ) );
		add_action( 'wpml_setup_completed', array( $this, 'update_event' ) );
		add_action( 'wpml_refresh_remote_xml_config', array( $this, 'update_event' ) );
		add_action( 'wpml_loaded', array( $this, 'handle_requests' ) );
	}

	public function handle_requests() {
		$action_name  = WPML_XML_Config_Log_Notice::NOTICE_ERROR_GROUP . '-action';
		$action_nonce = WPML_XML_Config_Log_Notice::NOTICE_ERROR_GROUP . '-nonce';

		$action = array_key_exists( $action_name, $_GET ) ? $_GET[ $action_name ] : null;
		$nonce  = array_key_exists( $action_nonce, $_GET ) ? $_GET[ $action_nonce ] : null;
		if ( $action && $nonce && wp_verify_nonce( $nonce, $action ) ) {
			if ( 'wpml_xml_update_clear' === $action ) {
				$this->log->clear();
				wp_safe_redirect( $this->log->get_log_url(), 302, 'WPML' );
			}
			if ( 'wpml_xml_update_refresh' === $action ) {
				$this->upgrader_process_complete_event();
			}
		}
	}

	public function update_event() {
		$this->get_worker()
			 ->run();
	}

	public function upgrader_process_complete_event() {
		$this->get_worker()
			 ->run();
	}

	public function update_event_ajax() {
		$nonce = isset( $_POST['_icl_nonce'] ) ? sanitize_text_field( $_POST['_icl_nonce'] ) : '';

		if ( ! wp_verify_nonce( $nonce, 'icl_theme_plugins_compatibility_nonce' ) ) {
			wp_send_json_error( esc_html__( 'Invalid request!', 'sitepress' ), 400 );
			return;
		}

		if ( $this->get_worker()
				  ->run() ) {
			echo esc_html( date_i18n( __( 'F j, Y', 'sitepress' ), get_option( 'wpml_config_index_updated' ) ) . ' ' . date_i18n( __( 'g:i a T', 'sitepress' ), get_option( 'wpml_config_index_updated' ) ) );
		}

		die;
	}

	public function update_event_cron() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$this->update_event();
	}
}
