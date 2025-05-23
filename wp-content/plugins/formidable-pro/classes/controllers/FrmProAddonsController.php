<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmProAddonsController extends FrmAddonsController {

	/**
	 * Render a conditional action button for a specified plugin
	 *
	 * @since 4.09.01
	 *
	 * @param string $plugin
	 * @param array|string $upgrade_link_args
	 * @return void
	 */
	public static function conditional_action_button( $plugin, $upgrade_link_args ) {
		if ( ! is_callable( self::class . '::get_addon' ) ) {
			// FrmAddonsController may not have this function depending on version.
			return;
		}

		$addon = self::get_addon( $plugin );

		$atts                  = is_array( $upgrade_link_args ) ? $upgrade_link_args : array();
		$atts['addon']         = $addon;
		$atts['license_type']  = self::get_license_type();
		$atts['plan_required'] = FrmFormsHelper::get_plan_required( $addon );
		$atts['upgrade_link']  = FrmAppHelper::admin_upgrade_link( $upgrade_link_args );

		self::show_conditional_action_button( $atts );
	}

	/**
	 * Render a conditional action button for an add on
	 *
	 * @since 4.09
	 *
	 * @param array $atts {
	 *     @type array        $addon
	 *     @type false|string $license_type
	 *     @type string       $plan_required
	 *     @type string       $upgrade_link
	 * }
	 * @return void
	 */
	public static function show_conditional_action_button( $atts ) {
		$addon        = $atts['addon'];
		$upgrade_link = $atts['upgrade_link'];

		if ( ! $addon ) {
			self::addon_upgrade_link( $addon, $upgrade_link );
			return;
		}

		$plugin           = $addon['plugin'];
		$is_installed     = 'installed' === $addon['status']['type'];
		$is_addons_page   = FrmAppHelper::is_admin_page( 'formidable-addons' );
		$class            = self::set_button_class( $atts );
		$additional_class = $is_installed && empty( $addon['activate_url'] ) ? ' frm_hidden' : '';

		if ( $is_addons_page ) {
			self::render_button( esc_html__( 'Deactivate', 'formidable' ), $plugin, $class . ' frm-button-tertiary frm-button-red frm-deactivate-addon' );
		}

		if ( $is_addons_page || $is_installed ) {
			self::render_button( esc_html__( 'Activate', 'formidable' ), $plugin, $class . ' button button-primary frm-button-primary frm-activate-addon' . $additional_class );
		}

		if ( $is_addons_page ) {
			self::render_button( esc_html__( 'Uninstall', 'formidable-pro' ), $plugin, $class . ' frm-button-tertiary frm-button-red frm-uninstall-addon frm-mx-xs' . $additional_class );
		}

		// Render "Install", "Renew Now", or "Upgrade" button.
		$license_type  = $atts['license_type'];
		$plan_required = $atts['plan_required'];

		if ( ! empty( $addon['url'] ) ) {
			if ( $is_addons_page || ! $is_installed ) {
				$additional_class = ! current_user_can( 'activate_plugins' ) ? ' frm_hidden' : '';
				self::render_button( esc_html__( 'Install', 'formidable' ), $addon['url'], $class . ' button button-primary frm-button-primary frm-install-addon' . $additional_class );
			}
		} elseif ( $license_type && strtolower( $license_type ) === strtolower( $plan_required ) ) {
			$upgrade_url = esc_url( FrmAppHelper::admin_upgrade_link( 'addons', 'account/downloads/' ) . '&utm_content=' . $addon['slug'] );
			self::render_button( esc_html__( 'Renew Now', 'formidable' ), $upgrade_url, $class . ' button button-primary frm-button-primary install-now button-secondary frm-button-secondary', $upgrade_url, '_blank' );
		} elseif ( 'not-installed' === $addon['status']['type'] ) {
			self::addon_upgrade_link( $addon, $upgrade_link );
		}
	}

	/**
	 * @since 6.5.1
	 *
	 * @param array $atts
	 *
	 * @return string
	 */
	protected static function set_button_class( $atts ) {
		$class = empty( $atts['class'] ) ? '' : $atts['class'];
		if ( strpos( $class, 'frm-button-' ) === false ) {
			// Only add small class if no other button class.
			$class .= ' frm-button-sm';
		}
		return $class;
	}

	/**
	 * Renders a button with provided text, relationship, classes, and link.
	 *
	 * @param string $text The text to display on the button.
	 * @param string $rel  The rel attribute for the button.
	 * @param string $class Additional classes for styling the button.
	 * @param string $href The href attribute for the button.
	 * @param string $target The target attribute for the button, defaults to '_self'.
	 * @return void
	 */
	private static function render_button( $text, $rel, $class, $href = '#', $target = '_self' ) {
		$attributes = array(
			'href'   => esc_url( $href ),
			'rel'    => esc_attr( $rel ),
			'class'  => 'frm-addon-button ' . esc_attr( $class ),
			'target' => esc_attr( $target ),
		);
		?>
		<a <?php FrmAppHelper::array_to_html_params( $attributes, true ); ?>><?php echo esc_html( $text ); ?></a>
		<?php
	}

	/**
	 * @since 4.06
	 * @since 5.0.03 added $force_type parameter.
	 *
	 * @param bool $force_type return type instead of checking expiration or code so "expired" or "grandfathered" are never returned.
	 * @return string
	 */
	public static function license_type( $force_type = false ) {
		$api    = new FrmFormApi();
		$addons = $api->get_api_info();
		$type   = 'free';

		if ( isset( $addons['error'] ) ) {
			if ( ! $force_type && isset( $addons['error']['code'] ) && $addons['error']['code'] === 'expired' ) {
				return $addons['error']['code'];
			}
			$type = isset( $addons['error']['type'] ) ? $addons['error']['type'] : $type;
		}

		if ( ! is_callable( array( __CLASS__, 'get_pro_from_addons' ) ) ) {
			$pro = isset( $addons['93790'] ) ? $addons['93790'] : array();
		} else {
			$pro = self::get_pro_from_addons( $addons );
		}

		if ( $type === 'free' ) {
			$type = isset( $pro['type'] ) ? $pro['type'] : $type;
			if ( $type === 'free' ) {
				return $type;
			}
		}

		if ( $force_type ) {
			return strtolower( $type );
		}

		if ( isset( $pro['code'] ) && $pro['code'] === 'grandfathered' ) {
			return $pro['code'];
		}

		$expires = isset( $pro['expires'] ) ? $pro['expires'] : '';
		$expired = $expires && $expires < time();
		return $expired ? 'expired' : strtolower( $type );
	}

	/**
	 * @since 5.0.03
	 *
	 * @return string "Basic", "Plus", "Starter", "Business" or "Elite" depending on license type. "Premium" by default if type can not be determined.
	 */
	public static function get_readable_license_type() {
		$license_type = self::license_type( true );
		if ( 0 === strpos( $license_type, 'views-' ) ) {
			// Remove "views-" from license type if it exists.
			$license_type = substr( $license_type, 6 );
		}

		if ( in_array( $license_type, array( 'personal', 'creator' ), true ) ) {
			$license_type = 'plus';
		} elseif ( $license_type === 'free' ) {
			$license_type = 'lite';
		} elseif ( ! in_array( $license_type, array( 'basic', 'elite', 'business', 'plus', 'starter' ), true ) ) {
			$license_type = 'premium';
		}

		return ucfirst( $license_type );
	}

	/**
	 * @since 4.08
	 *
	 * @return false|float False or the number of days until expiration.
	 */
	public static function is_license_expiring() {
		$version_info = self::get_primary_license_info();
		if ( ! isset( $version_info['active_sub'] ) || $version_info['active_sub'] !== 'no' ) {
			// Check for a subscription first.
			return false;
		}

		if ( isset( $version_info['error'] ) || empty( $version_info['expires'] ) ) {
			// It's either invalid or already expired.
			return false;
		}

		$expiration = $version_info['expires'];
		$days_left  = ( $expiration - time() ) / DAY_IN_SECONDS;
		if ( $days_left > 30 || $days_left < 0 ) {
			return false;
		}

		return $days_left;
	}

	/**
	 * Get the timestamp for expiration.
	 *
	 * @since 5.4.2
	 */
	private static function license_expiration() {
		$version_info = self::get_primary_license_info();
		return empty( $version_info['expires'] ) ? '' : $version_info['expires'];
	}

	/**
	 * Print out an renewal message for admin banner if applicable for expired, expiring, and grace period statuses.
	 *
	 * @since 5.4.2
	 *
	 * @return bool True if a message is shown.
	 */
	public static function admin_banner() {
		$status = self::get_license_status();
		if ( self::should_skip_renewal_message( $status ) ) {
			return false;
		}

		$show_close_icon = 'expiring' === $status && current_user_can( 'administrator' );

		if ( 'expired' === $status ) {
			$wrapper_class = 'frm-upgrade-bar';
		} else { // $status is 'expiring' or 'grace'.
			$wrapper_class = 'frm-banner-alert ' . ( 'expiring' === $status ? 'frm_warning_style' : 'frm_error_style' );
		}

		$wrapper_class .= ' frm_previous_install'; // Errors with frm_previous_install do not get hidden on the Form builder page. See issue #3803.

		$utc_medium = self::get_utc_medium_for_license_status( $status );
		?>
		<div class="<?php echo esc_attr( $wrapper_class ); ?>">
			<?php
			FrmAppHelper::icon_by_class( 'frmfont frm_alert_icon' );
			echo '&nbsp;';
			?>
			<span><?php self::message_text_for_license_status( true, $status ); ?></span>

			<a target="_blank" href="<?php echo esc_url( FrmAppHelper::admin_upgrade_link( $utc_medium, 'account/downloads/' ) ); ?>">
				<?php esc_html_e( 'Renew Now', 'formidable-pro' ); ?>
			</a>

			<?php if ( $show_close_icon ) { ?>
				<a style="float: right; margin-right: 30px; --primary-color: var(--dark-grey);" href="<?php echo esc_url( self::get_dismiss_renewal_message_action_url() ); ?>">
					<?php FrmAppHelper::icon_by_class( 'frmfont frm_close_icon', array( 'aria-label' => __( 'Close', 'formidable' ) ) ); ?>
				</a>
			<?php } ?>
		</div>
		<?php

		return true;
	}

	/**
	 * Get the active license status.
	 *
	 * @since 5.4.2
	 *
	 * @return string either 'grace', 'expired', 'expiring', or 'active'.
	 */
	public static function get_license_status() {
		if ( self::is_license_expired() ) {
			return self::check_grace_period() ? 'grace' : 'expired';
		}
		return self::is_license_expiring() ? 'expiring' : 'active';
	}

	/**
	 * Get or echo the message text for active license status.
	 *
	 * @since 5.4.2
	 *
	 * @param bool         $echo
	 * @param false|string $status
	 * @return string|void
	 */
	public static function message_text_for_license_status( $echo = false, $status = false ) {
		if ( false === $status ) {
			$status = self::get_license_status();
		}

		$echo_function = __CLASS__ . '::print_' . $status;

		if ( ! is_callable( $echo_function ) ) {
			$echo_function = function () {};
		}

		return FrmAppHelper::clip( $echo_function, $echo );
	}

	/**
	 * Print grace period message
	 *
	 * @since 5.4.2
	 *
	 * @return void
	 */
	public static function print_grace() {
		echo 'Your account license has expired. Access to pro features will be limited ';

		$grace_period = self::get_grace_period();
		if ( 0 === $grace_period ) {
			echo 'soon.';
			return;
		}

		$time_remaining = FrmAppHelper::human_time_diff( $grace_period );
		echo 'in <strong>' . esc_html( $time_remaining ) . '</strong>.';
	}

	/**
	 * Print expired status message.
	 *
	 * @since 5.4.2
	 *
	 * @return void
	 */
	public static function print_expired() {
		esc_html_e( 'Your account license has expired and is no longer qualified for important security updates.', 'formidable-pro' );
	}

	/**
	 * Print expiring status message.
	 *
	 * @since 5.4.2
	 *
	 * @return void
	 */
	public static function print_expiring() {
		$expires  = self::license_expiration();
		$expiring = FrmAppHelper::human_time_diff( $expires );

		printf(
			/* translators: %s: Duration until license expires (ie 5 days, 1 hour) */
			esc_html__( 'Your account license expires in %s.', 'formidable-pro' ),
			'<strong>' . esc_html( $expiring ) . '</strong>'
		);
	}

	/**
	 * @since 5.4.2
	 *
	 * @param string $status
	 * @return bool
	 */
	private static function should_skip_renewal_message( $status ) {
		// No banner for active status.
		if ( 'active' === $status ) {
			return true;
		}

		// Exit early if the user has dismissed the expiring license warning within the last day.
		if ( 'expiring' === $status ) {
			$dismissed_renewal_message = get_option( 'frm_dismissed_renewal_message' );
			if ( false !== $dismissed_renewal_message && time() - (int) $dismissed_renewal_message < DAY_IN_SECONDS ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @since 5.4.2
	 *
	 * @return string
	 */
	private static function get_dismiss_renewal_message_action_url() {
		return wp_nonce_url( admin_url( 'admin-ajax.php?action=frm_dismiss_renewal_message' ) );
	}

	/**
	 * Dismiss renewal message via AJAX request.
	 *
	 * @return void
	 */
	public static function dismiss_renewal_message() {
		FrmAppHelper::permission_check( 'administrator' );

		if ( ! wp_verify_nonce( FrmAppHelper::simple_get( '_wpnonce', '', 'sanitize_text_field' ) ) ) {
			$frm_settings = FrmAppHelper::get_settings();
			die( esc_html( $frm_settings->admin_permission ) );
		}

		update_option( 'frm_dismissed_renewal_message', time(), 'no' );
		wp_safe_redirect( self::get_after_dismiss_redirect_url() );
	}

	/**
	 * @since 5.4.2
	 *
	 * @return string URL to redirect to after dismissing renewal message.
	 */
	private static function get_after_dismiss_redirect_url() {
		$referer = FrmAppHelper::get_server_value( 'HTTP_REFERER' );
		if ( ! $referer ) {
			return self::get_default_dismiss_redirect_url();
		}

		$parsed = parse_url( $referer );
		if ( ! is_array( $parsed ) || empty( $parsed['query'] ) || empty( $parsed['path'] ) ) {
			return self::get_default_dismiss_redirect_url();
		}

		$parts = explode( '/', $parsed['path'] );
		$path  = end( $parts );
		if ( ! in_array( $path, array( 'edit.php', 'admin.php' ), true ) ) {
			return self::get_default_dismiss_redirect_url();
		}

		$query = $parsed['query'];
		return admin_url( $path . '?' . $query );
	}

	/**
	 * @since 5.4.2
	 *
	 * @return string
	 */
	private static function get_default_dismiss_redirect_url() {
		return admin_url( 'admin.php?page=formidable' );
	}

	/**
	 * @param string $status
	 * @return string
	 */
	public static function get_utc_medium_for_license_status( $status ) {
		return 'expiring' === $status ? 'form-renew' : 'form-expired';
	}

	/**
	 * @since 5.4.2
	 *
	 * @return bool True if within grace period.
	 */
	private static function check_grace_period() {
		$grace_period = self::get_grace_period();
		return 0 === $grace_period || time() < $grace_period;
	}

	/**
	 * @since 5.4.2
	 *
	 * @return int
	 */
	private static function get_grace_period() {
		$info = self::get_primary_license_info();

		foreach ( array( 'grace', 'expires' ) as $key ) {
			if ( ! isset( $info[ $key ] ) || ! is_numeric( $info[ $key ] ) ) {
				return 0;
			}
		}

		$grace   = intval( $info['grace'] );
		$expires = intval( $info['expires'] );

		if ( $grace < $expires ) {
			return 0;
		}

		return $grace;
	}

	/**
	 * @since 4.06.02
	 */
	public static function ajax_multiple_addons() {
		self::install_addon_permissions();

		// Set the current screen to avoid undefined notices.
		global $hook_suffix;
		set_current_screen();

		$free_plugin_supports_current_plugin_var = is_callable( __CLASS__ . '::get_current_plugin' );

		$download_urls = explode( ',', FrmAppHelper::get_param( 'plugin', '', 'post' ) );
		FrmAppHelper::sanitize_value( 'esc_url_raw', $download_urls );

		foreach ( $download_urls as $download_url ) {
			if ( $free_plugin_supports_current_plugin_var ) {
				self::$plugin = $download_url;
			} else {
				$_POST['plugin'] = $download_url;
			}

			if ( strpos( $download_url, 'http' ) !== false ) {
				// Installing.
				self::maybe_show_cred_form();

				$installed = self::install_addon();
				self::maybe_activate_addon( $installed );
			} else {
				// Activating.
				self::maybe_activate_addon( $download_url );
			}
		}

		echo json_encode( __( 'Your plugins have been installed and activated.', 'formidable' ) );

		wp_die();
	}

	/**
	 * @since 5.4.2
	 *
	 * @return bool
	 */
	public static function is_expired_outside_grace_period() {
		return self::is_license_expired() && ! self::check_grace_period();
	}

	/**
	 * @since 5.4.2
	 *
	 * @return bool
	 */
	public static function pro_is_behind_latest_version() {
		$version = FrmProDb::$plug_version;
		$addons  = self::get_primary_license_info();

		if ( ! is_callable( self::class . '::get_pro_from_addons' ) ) {
			return false;
		}

		$pro = self::get_pro_from_addons( $addons );
		if ( ! $pro ) {
			return false;
		}

		return version_compare( $version, $pro['version'], '<' );
	}

	/**
	 * @since 5.5.1
	 *
	 * @return void
	 */
	public static function maybe_disable_form_actions() {
		if ( ! self::is_expired_outside_grace_period() || 'settings' !== FrmAppHelper::get_param( 'frm_action' ) ) {
			return;
		}

		add_filter(
			'frm_registered_form_actions',
			function ( $actions ) {
				self::add_filters_to_disable_registered_actions( $actions );
				return $actions;
			},
			99
		);
	}

	/**
	 * @since 5.5.1
	 *
	 * @param array<string,string> $actions
	 * @return void
	 */
	private static function add_filters_to_disable_registered_actions( $actions ) {
		$keys = array_keys( $actions );

		$lite_actions = self::get_lite_actions();
		foreach ( $keys as $key ) {
			if ( in_array( $key, $lite_actions, true ) ) {
				continue;
			}

			add_filter(
				'frm_' . $key . '_action_options',
				/**
				 * @param array $options
				 * @return array
				 */
				function ( $options ) {
					$options['active'] = false;
					if ( false === strpos( $options['classes'], 'frm_show_upgrade' ) ) {
						$options['classes'] .= ' frm_show_upgrade';
					}
					$options['classes'] .= ' frm_show_expired_modal';
					return $options;
				},
				99
			);
		}
	}

	/**
	 * Get a list of actions that are available in Lite.
	 *
	 * @return string[]
	 */
	private static function get_lite_actions() {
		return array( 'email', 'on_submit', 'payment' );
	}

	/**
	 * @return void
	 */
	public static function before_add_form_action() {
		if ( self::is_expired_outside_grace_period() ) {
			$action_type = FrmAppHelper::get_param( 'type', '', 'post', 'sanitize_text_field' );
			if ( ! in_array( $action_type, self::get_lite_actions(), true ) ) {
				wp_die( -1 );
			}
		}
	}

	/**
	 * @since 6.5.1
	 *
	 * @return void
	 */
	private static function show_warning_overlay_expired_license() {
		if ( ! class_exists( 'FrmOverlayController' ) ) {
			return;
		}

		$overlay_wrapper = new FrmOverlayController(
			array(
				'config-option-name'  => 'expired-license-warning',
				'execution-frequency' => '1 year',
			)
		);

		$overlay_wrapper->open_overlay(
			array(
				'hero_image' => FrmProAppHelper::plugin_url() . '/images/license-warning-overlay/lock.svg',
				'heading'    => esc_html__( 'Heads up! Your license has expired', 'formidable' ),
				'copy'       => esc_html__( 'An active license is needed to access new features, add-ons, plugin updates, and our world class support!', 'formidable' ),
				'buttons'    => array(
					array(
						'url'    => FrmAppHelper::admin_upgrade_link( 'expired-full', 'knowledgebase/manage-licenses-and-sites/renewing-an-expired-license/' ),
						'target' => '_blank',
						'label'  => esc_html__( 'Learn More', 'formidable' ),
					),
					array(
						'url'   => FrmAppHelper::admin_upgrade_link( 'expired-full', 'account/downloads/' ),
						'label' => esc_html__( 'Renew License Now', 'formidable' ),
					),
				),
			)
		);
	}

	/**
	 * @since 6.5.1
	 *
	 * @return void
	 */
	private static function show_warning_overlay_nulled_license( $error = array() ) {
		if ( ! class_exists( 'FrmOverlayController' ) ) {
			return;
		}

		$overlay_wrapper = new FrmOverlayController(
			array(
				'config-option-name'  => 'nulled-license-warning',
				'execution-frequency' => '1 month',
			)
		);

		$copy = sprintf(
			/* translators: %1$s: HTML break line + open link, %2$s: HTML start b tag, %3$s: HTML close b tag & link */
			esc_html__( 'Your version of Formidable Forms has been altered and may contain malware!%1$sSwitch to the official version now for %2$s50%% off%3$s', 'formidable' ),
			'<br/><br/> <a class="frm-meta-tag frm-green-tag frm-nulled-license-green-cta" href="' . FrmAppHelper::admin_upgrade_link( 'nulled-full' ) . '" target="_blank">',
			'<b>',
			'</b></a>'
		);
		if ( isset( $error['message'] ) ) {
			$copy = str_replace( array( 'utm_medium=nulled', '50% off', '<a ', '</a>.' ), array( 'utm_medium=nulled-full', '<b>50% off</b>', '<br/><a class="frm-meta-tag frm-green-tag frm-nulled-license-green-cta" ', '</a>' ), html_entity_decode( $error['message'] ) );
		}

		$overlay_wrapper->open_overlay(
			array(
				'hero_image' => FrmProAppHelper::plugin_url() . '/images/license-warning-overlay/lock.svg',
				'heading'    => esc_html__( 'Heads up! Your plugin has been altered!', 'formidable' ),
				'copy'       => $copy,
				'buttons'    => array(
					array(
						'url'    => FrmAppHelper::admin_upgrade_link( 'nulled-full', 'formidable-forms-pro-nulled/' ),
						'target' => '_blank',
						'label'  => esc_html__( 'Learn More', 'formidable' ),
					),
					array(
						'url'    => FrmAppHelper::admin_upgrade_link( 'nulled-full' ),
						'target' => '_blank',
						'label'  => esc_html__( 'Get 50% Off!', 'formidable' ),
					),
				),
			)
		);
	}

	/**
	 * @since 6.5.1
	 *
	 * @return void
	 */
	public static function show_warning_overlay_for_expired_or_null_license() {
		if ( ! FrmAppHelper::is_full_screen() || ! class_exists( 'FrmOverlayController' ) ) {
			return;
		}

		$license_is_expired = FrmAddonsController::is_license_expired();

		if ( empty( $license_is_expired ) ) {
			return;
		}

		if ( isset( $license_is_expired['type'] ) && 'invalid' === $license_is_expired['type'] ) {
			self::show_warning_overlay_nulled_license( $license_is_expired );
			return;
		}

		self::show_warning_overlay_expired_license();
	}

	/**
	 * @since 4.07
	 * @deprecated 6.12
	 *
	 * @return void
	 */
	public static function renewal_message() {
		_deprecated_function( __METHOD__, '6.12' );
	}
}
