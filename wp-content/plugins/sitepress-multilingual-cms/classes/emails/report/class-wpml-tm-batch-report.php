<?php

/**
 * Class WPML_TM_Batch_Report
 */
class WPML_TM_Batch_Report {
	
	const BATCH_REPORT_OPTION = '_wpml_batch_report';

	/**
	 * @var WPML_TM_Blog_Translators
	 */
	private $blog_translators;

	/**
	 * @var \wpdb
	 */
	private $wpdb;

	/** @var array */
	private $delayed;

	/**
	 * WPML_TM_Batch_Report constructor.
	 *
	 * @param WPML_TM_Blog_Translators $blog_translators
	 * @param \wpdb                    $wpdb
	 */
	public function __construct( WPML_TM_Blog_Translators $blog_translators, \wpdb $wpdb ) {
		$this->blog_translators = $blog_translators;
		$this->wpdb             = $wpdb;
		$this->delayed          = [];
	}

	/**
	 * @param WPML_Translation_Job $job
	 */
	public function set_job( WPML_Translation_Job $job ) {
		$job_fields = $job->get_basic_data();
		if ( ! WPML_User_Jobs_Notification_Settings::is_new_job_notification_enabled( $job_fields->translator_id ) ) {
			return;
		}
		
		$batch_jobs = $batch_jobs_raw = $this->get_jobs();
		$batch_jobs = $this->add_job_to_batch( $batch_jobs, $job, $job_fields );

		if ( $batch_jobs_raw !== $batch_jobs ) {
			update_option( self::BATCH_REPORT_OPTION, $batch_jobs, 'no' );
		}
	}

	/**
	 * @param WPML_Translation_Job $job
	 */
	public function set_job_with_delay( WPML_Translation_Job $job ) {
		$job_fields = $job->get_basic_data();
		if ( ! WPML_User_Jobs_Notification_Settings::is_new_job_notification_enabled( $job_fields->translator_id ) ) {
			return;
		}

		$delayed_batch = $this->delayed;
		$this->delayed = $this->add_job_to_batch( $delayed_batch, $job, $job_fields );
	}

	/**
	 * @param array                $batch
	 * @param WPML_Translation_Job $job
	 * @param object               $job_fields
	 *
	 * @return array
	 */
	private function add_job_to_batch( $batch, $job, $job_fields ) {
		$lang_pair  = $job_fields->source_language_code . '|' . $job_fields->language_code;

		$batch[ (int) $job_fields->translator_id ][ $lang_pair ][] = array(
			'element_id' => isset( $job_fields->original_doc_id ) ? $job_fields->original_doc_id : null,
			'type'       => strtolower( $job->get_type() ),
			'job_id'     => $job->get_id(),
		);

		return $batch;
	}

	/**
	 * @return array
	 */
	public function get_unassigned_jobs() {
		$batch_jobs = $this->get_jobs();
		$unassigned_jobs = array();

		if( array_key_exists( 0, $batch_jobs ) ) {
			$unassigned_jobs = $batch_jobs[0];
		}

		return $unassigned_jobs;
	}

	/**
	 * @return array
	 */
	public function get_unassigned_translators() {
		$assigned_translators = array_keys( $this->get_jobs() );
		$blog_translators = wp_list_pluck( $this->blog_translators->get_blog_translators() , 'ID');

		return array_diff( $blog_translators, $assigned_translators );
	}


	/**
	 * @return array
	 */
	public function get_jobs() {
		$jobs = get_option( self::BATCH_REPORT_OPTION ) ? get_option( self::BATCH_REPORT_OPTION ) : array();

		$jobIds = [];
		foreach ( $jobs as $translatorId => $languagePairs ) {
			if ( ! is_array( $languagePairs ) ) {
				continue;
			}

			foreach ( $languagePairs as $languagePairName => $languagePairItems ) {
				foreach ( $languagePairItems as $languagePairItem ) {
					if ( isset( $languagePairItem['job_id'] ) ) {
						$jobIds[] = $languagePairItem['job_id'];
					}
				}
			}
		}

		if ( empty( $jobIds ) ) {
			return $jobs;
		}

		$jobIdsIn        = wpml_prepare_in( $jobIds, '%d' );
		$automaticJobIds = $this->wpdb->get_col( $this->wpdb->prepare(
			"
			SELECT job_id
			FROM {$this->wpdb->prefix}icl_translate_job
			WHERE job_id IN({$jobIdsIn}) AND automatic = 1
			LIMIT %d
			",
			count( $jobIds )
		) );
		$automaticJobIds = array_map('intval', is_array( $automaticJobIds ) ? $automaticJobIds : [] );

		$filteredJobs = [];
		foreach ( $jobs as $translatorId => $languagePairs ) {
			foreach ( $languagePairs as $languagePairName => $languagePairItems ) {
				foreach ( $languagePairItems as $languagePairItem ) {
					if ( ! isset( $languagePairItem['job_id'] ) || in_array( (int) $languagePairItem['job_id'], $automaticJobIds ) ) {
						continue;
					}

					if ( ! isset( $filteredJobs[ $translatorId ] ) ) {
						$filteredJobs[ $translatorId ] = [];
					}

					if ( ! isset( $filteredJobs[ $translatorId ][ $languagePairName ] ) ) {
						$filteredJobs[ $translatorId ][ $languagePairName ] = [];
					}

					$filteredJobs[ $translatorId ][ $languagePairName ][] = $languagePairItem;
				}
			}
		}

		return $filteredJobs;
	}

	public function process_jobs_with_delay() {
		if ( empty( $this->delayed ) ) {
			return;
		}

		$batch_jobs = $this->get_jobs();

		foreach( $this->delayed as $translator_id => $languagePairs ) {
			foreach ( $languagePairs as $languagePairName => $languagePairItems ) {
				foreach ( $languagePairItems as $languagePairItem ) {
					$batch_jobs[ (int) $translator_id ][ $languagePairName ][] = $languagePairItem;
				}
			}
		}

		$this->delayed = [];

		update_option( self::BATCH_REPORT_OPTION, $batch_jobs, 'no' );
	}

	public function reset_batch_report( $translator_id ) {
		$batch_jobs = $this->get_jobs();
		if ( array_key_exists( $translator_id, $batch_jobs ) ) {
			unset( $batch_jobs[$translator_id] );
		}

		update_option( self::BATCH_REPORT_OPTION, $batch_jobs, 'no' );
	}

	/**
	 * @param int[] $translators_ids
	 */
	public function reset_batch_report_for_translators( $translators_ids ) {
		$batch_jobs      = $this->get_jobs();
		$translators_ids = array_unique( $translators_ids );

		if ( empty( $translators_ids ) ) {
			return;
		}

		foreach ( $translators_ids as $translator_id ) {
			if ( array_key_exists( $translator_id, $batch_jobs ) ) {
				unset( $batch_jobs[$translator_id] );
			}
		}

		update_option( self::BATCH_REPORT_OPTION, $batch_jobs, 'no' );
	}
}