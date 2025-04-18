<?php

/**
 * Class WPML_ST_Verify_Dependencies
 *
 * Checks that the WPML Core plugin is installed and satisfies certain version
 * requirements
 */
class WPML_ST_Verify_Dependencies {

	/**
	 * @param string|false $wpml_core_version
	 * @param string|null  $dependenciesFilepath
	 */
	function verify_wpml( $wpml_core_version, string $dependenciesFilepath = null ) {
		if ( is_null( $dependenciesFilepath ) ) {
			$dependenciesFilepath = WPML_ST_PATH . '/wpml-dependencies.json';
		}

		if ( false === $wpml_core_version ) {
			add_action(
				'admin_notices',
				array(
					$this,
					'notice_no_wpml',
				)
			);
		} elseif ( ! WPML_Core_Version_Check::is_ok( $dependenciesFilepath ) ) {
			add_action( 'admin_notices', array( $this, 'wpml_is_outdated' ) );
		}
	}

	function notice_no_wpml() {
		?>
		<div class="error wpml-admin-notice wpml-st-inactive wpml-inactive">
			<p><?php esc_html_e( 'Please activate WPML Multilingual CMS to have WPML String Translation working.', 'wpml-string-translation' ); ?></p>
		</div>
		<?php
	}

	function wpml_is_outdated() {
		?>
		<div
			class="message error wpml-admin-notice wpml-st-inactive wpml-outdated">
			<p><?php esc_html_e( 'WPML String Translation is enabled but not effective, because WPML is outdated. Please update WPML first.', 'wpml-string-translation' ); ?></p>
		</div>
		<?php
	}
}
