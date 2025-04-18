<?php

class WPML_Localization {
	/**
	 * @var \wpdb
	 */
	private $wpdb;

	/**
	 * WPML_Localization constructor.
	 *
	 * @param wpdb $wpdb
	 */
	public function __construct( wpdb $wpdb ) {
		$this->wpdb        = $wpdb;
	}

	public function get_theme_localization_stats( $theme_localization_domains = array() ) {
		if ( empty( $theme_localization_domains ) || ! is_array( $theme_localization_domains ) ) {
			$theme_localization_domains = icl_get_sub_setting( 'st', 'theme_localization_domains' );
		}
		return $this->get_domain_stats( $theme_localization_domains, 'theme' );
	}

	public function get_domain_stats( $localization_domains, $default, $no_wordpress = false, $count_in_progress_as_completed = false ) {
		$results = array();
		if ( $localization_domains ) {
			$domains = array();

			foreach ( (array) $localization_domains as $domain ) {
				if ( ! ( $no_wordpress && 'WordPress' === $domain ) ) {
					$domains[] = $domain ? $domain : $default;
				}
			}
			if ( ! empty( $domains ) ) {
				$sql     = "SELECT context, status, COUNT(id) AS c 
						FROM {$this->wpdb->prefix}icl_strings 
						WHERE context IN (" . wpml_prepare_in( $domains ) . ")
						GROUP BY context, status";

				$results = $this->wpdb->get_results( $sql );
			}
		}

		return $this->results_to_array( $results, $count_in_progress_as_completed );
	}

	public function get_localization_stats( $component_type ) {
		$localization_data = $this->get_localization_data( $component_type );

		$results     = array();
		$all_domains = array();

		foreach ( $localization_data as $component => $localization_domains ) {
			$all_domains = array_merge( $all_domains, array_keys( $localization_domains ) );
		}

		$all_results = $this->get_domain_stats( $all_domains, $component_type, true );
		foreach ( $localization_data as $component => $localization_domains ) {
			$domains = array_keys( $localization_domains );
			foreach ( $domains as $domain ) {
				if ( array_key_exists( $domain, $all_results ) ) {
					$results[ $component ][ $domain ] = $all_results[ $domain ];
				}
			}
		}

		return $results;
	}

	private function get_localization_data( $component_type ) {
		$localization_data = apply_filters( 'wpml_sub_setting', array(), 'st', 'plugin' === $component_type ? 'plugin_localization_domains' : 'theme_localization_domains' );
		if ( ! is_array( current( $localization_data ) ) ) {
			if ( 'plugin' === $component_type ) {
				return array();
			}

			$localization_data = array();
			foreach ( wp_get_themes() as $theme_folder => $theme ) {
				if ( $theme->get( 'TextDomain' ) ) {
					$localization_data[ $theme_folder ] = array( $theme->get( 'TextDomain' ) => 0 );
				}
			}
		}

		return $localization_data;
	}

	public function get_wrong_plugin_localization_stats() {
		$results = $this->wpdb->get_results(
			"
	        SELECT context, status, COUNT(id) AS c
	        FROM {$this->wpdb->prefix}icl_strings
	        WHERE context LIKE ('plugin %')
	        GROUP BY context, status
	    "
		);

		return $this->results_to_array( $results );
	}

	public function get_wrong_theme_localization_stats() {
		$results = $this->wpdb->get_results(
			"
	        SELECT context, status, COUNT(id) AS c
	        FROM {$this->wpdb->prefix}icl_strings
	        WHERE context LIKE ('theme %')
	        GROUP BY context, status
	    "
		);

		$results = $this->results_to_array( $results );

		$theme_path        = TEMPLATEPATH;
		$old_theme_context = 'theme ' . basename( $theme_path );

		unset( $results[ $old_theme_context ] );

		return $results;

	}

	public function does_theme_require_rescan() {

		$theme_path        = TEMPLATEPATH;
		$old_theme_context = 'theme ' . basename( $theme_path );

		/** @var string $sql */
		$sql = $this->wpdb->prepare(
			"
	        SELECT COUNT(id) AS c
	        FROM {$this->wpdb->prefix}icl_strings
	        WHERE context = %s",
			$old_theme_context
		);
		$result = $this->wpdb->get_var( $sql );

		return $result ? true : false;
	}

	public function get_most_popular_domain( $plugin ) {
		$plugin_localization_domains = icl_get_sub_setting( 'st', 'plugin_localization_domains' );

		$most_popular = '';
		$most_count   = 0;

		foreach ( $plugin_localization_domains[ $plugin ] as $name => $count ) {
			if ( $name == 'WordPress' || $name == 'default' ) {
				continue;
			}
			if ( $count > $most_count ) {
				$most_popular = $name;
				$most_count   = $count;
			}
		}

		return $most_popular;
	}
	private function results_to_array( $results, $count_in_progress_as_completed = false ) {
		$stats = array();

		foreach ( $results as $r ) {
			if ( ! isset( $stats[ $r->context ]['complete'] ) ) {
				$stats[ $r->context ]['complete'] = 0;
			}
			if ( ! isset( $stats[ $r->context ]['incomplete'] ) ) {
				$stats[ $r->context ]['incomplete'] = 0;
			}
			if (
				$r->status == ICL_TM_COMPLETE ||
				( $count_in_progress_as_completed && $r->status == ICL_TM_IN_PROGRESS )
			) {
				$stats[ $r->context ]['complete'] += $r->c;
			} else {
				$stats[ $r->context ]['incomplete'] += $r->c;
			}
		}

		return $stats;
	}

	/**
	 * @return string
	 */
	private function getBaseStUrl() {
		return admin_url( 'admin.php?page=' . WPML_ST_FOLDER . '/menu/string-translation.php' );
	}

	/**
	 * @param array  $localization_stats
	 * @param string $localization_file
	 * @param array  $localization_data
	 *
	 * @return array
	 */
	public function getDomainsFromLocalizationStats( $localization_stats, $localization_file, $localization_data ) {
		$domains = array_key_exists( $localization_file, $localization_stats ) ? $localization_stats[ $localization_file ] : false;

		if ( $domains ) {
			return \wpml_collect( $domains )->map( function ( $stats, $domain ) {
				return $this->get_component( $domain, $stats );
			} )->toArray();
		}

		$textDomain = \WPML\FP\Obj::propOr( __( 'No TextDomain', 'wpml-string-translation' ), 'TextDomain', $localization_data );

		return [ $textDomain => $this->get_component( $textDomain, [ 'complete' => 0, 'incomplete' => 0 ] ) ];
	}

	/**
	 * @param string $domain
	 * @param array  $stats
	 *
	 * @return array
	 */
	private function get_component( $domain, array $stats ) {
		return array(
			'translated'              => $stats['complete'],
			'needs_update'            => $stats['incomplete'],
			'needs_update_link'       => add_query_arg(
				array(
					'context' => $domain,
					'status'  => ICL_STRING_TRANSLATION_NOT_TRANSLATED,
				),
				$this->getBaseStUrl()
			),
			'translated_link'         => add_query_arg(
				array(
					'context' => $domain,
					'status'  => ICL_STRING_TRANSLATION_COMPLETE,
				),
				$this->getBaseStUrl()
			),
			'domain_link'             => add_query_arg( array( 'context' => $domain ), $this->getBaseStUrl() ),
			'title_needs_translation' => sprintf( __( 'Translate strings in %s', 'wpml-string-translation' ), $domain ),
			'title_all_strings'       => sprintf( __( 'All strings in %s', 'wpml-string-translation' ), $domain ),
		);
	}
}
