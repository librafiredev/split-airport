<?php

use WPML\FP\Obj;

class WPML_Package_Helper {
	const PREFIX_BATCH_STRING = 'batch-string-';

	private $default_language;
	private $last_registered_string_id;
	protected $registered_strings;
	private $package_cleanup;
	private $package_factory;

	private $cache_group;

	function __construct() {
		$this->registered_strings = array();
		$this->cache_group        = 'string_package';
		$this->package_cleanup    = new WPML_ST_Package_Cleanup();
		$this->package_factory    = new WPML_ST_Package_Factory();
	}

	public function set_package_factory( WPML_ST_Package_Factory $factory ) {
		$this->package_factory = $factory;
	}

	/**
	 * @param int $package_id
	 */
	protected function delete_package( $package_id ) {
		// delete the strings and the translations

		$this->delete_package_strings( $package_id );

		$tm = new WPML_Package_TM( $this->package_factory->create( $package_id ) );
		$tm->delete_translation_jobs();
		$tm->delete_translations();

		global $wpdb;
		$delete_query   = "DELETE FROM {$wpdb->prefix}icl_string_packages WHERE id=%d";
		$delete_prepare = $wpdb->prepare( $delete_query, $package_id );
		$wpdb->query( $delete_prepare );

		// Delete translation files.
		$package = $tm->get_package();
		$domain  = $package->kind_slug . '-' . $package->name;
		// See Manager->getFilepath() method.
		$domain = str_replace( '/', '-', $domain );

		do_action( 'wpml_st_refresh_domain', $domain );
	}

	/**
	 * @param int $package_id
	 *
	 * @return array
	 */
	protected function get_strings_ids_from_package_id( $package_id ) {
		global $wpdb;
		$string_ids_query   = "SELECT id FROM {$wpdb->prefix}icl_strings WHERE string_package_id=%d";
		$string_ids_prepare = $wpdb->prepare( $string_ids_query, $package_id );
		$string_ids         = $wpdb->get_col( $string_ids_prepare );

		return $string_ids;
	}

	/**
	 * @param int $package_id
	 */
	protected function delete_package_strings( $package_id ) {
		$strings = $this->get_strings_ids_from_package_id( $package_id );

		foreach ( $strings as $string_id ) {
			do_action( 'wpml_st_delete_all_string_data', $string_id );
		}
	}

	protected function loaded() {
		$this->default_language = icl_get_default_language();
	}

	/**
	 * @param string             $string_value
	 * @param string             $string_name
	 * @param array|WPML_Package $package
	 * @param string             $string_title
	 * @param string             $string_type
	 */
	final function register_string_action( $string_value, $string_name, $package, $string_title, $string_type ) {
		$this->register_string_for_translation( $string_value, $string_name, $package, $string_title, $string_type );

		return $this->last_registered_string_id;
	}

	/**
	 * @param int                               $default
	 * @param \stdClass|\WPML_Package|array|int $package
	 * @param string                            $string_name
	 * @param string                            $string_value
	 *
	 * @return bool|int|mixed
	 */
	function string_id_from_package_filter( $default, $package, $string_name, $string_value ) {
		$string_id = $this->get_string_id_from_package( $package, $string_name, $string_value );
		if ( ! $string_id ) {
			return $default;
		}

		return $string_id;
	}

	function string_title_from_id_filter( $default, $string_id ) {
		global $wpdb;

		$string_title = false;

		if ( $string_id ) {
			$string_title_query = 'SELECT title FROM ' . $wpdb->prefix . 'icl_strings WHERE id=%d';
			$string_title_sql   = $wpdb->prepare( $string_title_query, array( $string_id ) );

			$string_title = $wpdb->get_var( $string_title_sql );
		}

		if ( ! $string_title ) {
			$string_title = $default;
		}

		return $string_title;
	}

	/**
	 * @param string             $string_value
	 * @param string             $string_name
	 * @param array|WPML_Package $package
	 * @param string             $string_title
	 * @param string             $string_type
	 *
	 * @return string
	 */
	final public function register_string_for_translation( $string_value, $string_name, $package, $string_title, $string_type ) {
		$package    = $this->package_factory->create( $package );
		$package_id = $package->ID;
		if ( ! $package_id ) {

			// Need to create a new record.
			if ( $package->has_kind_and_name() ) {
				$package_id = self::create_new_package( $package );
				$package    = $this->package_factory->create( $package );
			}
		}
		if ( $package_id ) {
			$this->maybe_update_package( $package );
			$tm = new WPML_Package_TM( $package );
			$tm->validate_translations();

			$this->init_package_registered_strings( $package_id );

			$string_name = $package->sanitize_string_name( $string_name );

			$this->set_package_registered_strings( $package_id, $string_type, $string_title, $string_name, $string_value );
			$this->last_registered_string_id = $this->register_string_with_wpml( $package, $string_name, $string_title, $string_type, $string_value );
			$this->package_cleanup->record_register_string( $package, $this->last_registered_string_id );
		}

		// Action called after string is registered.
		do_action( 'wpml_st_string_registered' );

		/**
		 * Fires after a string is registered as part of a string package.
		 *
		 * @since 3.2.10
		 *
		 * @param WPML_Package $package
		 *
		 */
		do_action( 'wpml_st_package_string_registered', $package );

		return $string_value;
	}

	final function get_string_context_from_package( $package ) {
		$package = $this->package_factory->create( $package );

		return $package->get_string_context_from_package();
	}

	/**
	 * @param WPML_Package $package
	 * @param string       $string_name
	 * @param string       $string_title
	 * @param string       $string_type
	 * @param string       $string_value
	 *
	 * @return bool|int|mixed
	 */
	final function register_string_with_wpml( $package, $string_name, $string_title, $string_type, $string_value ) {
		global $wpdb;

		$string_id = $this->get_string_id_from_package( $package, $string_name, $string_value );

		if ( $string_id ) {
			$package_storage = new WPML_ST_Package_Storage( $package->ID, $wpdb );
			$did_update      = $package_storage->update( $string_title, $string_type, $string_value, $string_id );

			if ( $did_update ) {
				$this->flush_cache();
				$package->flush_cache();

				// Action called after package strings are updated.
				do_action( 'wpml_st_string_updated' );
			}
		}

		return $string_id;
	}

	/**
	 * @param string|mixed     $string_value
	 * @param string           $string_name
	 * @param array|object|int $package
	 *
	 * @return string|mixed
	 */
	final function translate_string( $string_value, $string_name, $package ) {
		$result = $string_value;

		if ( is_string( $string_value ) ) {
			/** @var array|stdClass $package */
			$package = is_scalar( $package ) ? [ 'ID' => $package ] : Obj::assoc( 'translate_only', true, $package );
			$package = $this->package_factory->create( $package );

			if ( $package ) {
				$sanitized_string_name = $package->sanitize_string_name( $string_name );

				$result = $package->translate_string( $string_value, $sanitized_string_name );
			}
		}

		return $result;
	}

	final function get_translated_strings( $strings, $package ) {
		$package = $this->package_factory->create( $package );

		return $package->get_translated_strings( $strings );
	}

	final function set_translated_strings( $translations, $package ) {
		$package = $this->package_factory->create( $package );
		$package->set_translated_strings( $translations );
	}

	final function get_translatable_types( $types ) {
		global $wpdb;

		$package_kinds = $wpdb->get_results( "SELECT kind, kind_slug FROM {$wpdb->prefix}icl_string_packages WHERE id>0" );

		// Add any packages found to the $types array
		foreach ( $package_kinds as $package_data ) {
			$package_kind_slug = $package_data->kind_slug;
			$package_kind      = $package_data->kind;
			$kinds_added       = array_keys( $types );
			if ( ! in_array( $package_kind_slug, $kinds_added ) ) {
				$translatable_type                        = new stdClass();
				$translatable_type->name                  = $package_kind_slug;
				$translatable_type->label                 = $package_kind;
				$translatable_type->prefix                = 'package';
				$translatable_type->labels                = new stdClass();
				$translatable_type->labels->singular_name = $package_kind;
				$translatable_type->labels->name          = $package_kind;
				$translatable_type->external_type         = 1;
				$types[ $package_kind_slug ]              = $translatable_type;
			}
		}

		return $types;
	}

	/**
	 * @param  WPML_Package|null        $item
	 * @param  int|WP_Post|WPML_Package $package
	 * @param  string                   $type
	 *
	 * @return null|WPML_Package
	 */
	final public function get_translatable_item( $item, $package, $type = 'package' ) {
		if ( $type === 'package' || explode( '_', is_null( $type ) ? '' : $type )[0] === 'package' ) {
			$tm = new WPML_Package_TM( $item );

			return $tm->get_translatable_item( $package );
		}

		return $item;
	}

	final function get_post_title( $title, $package_id ) {
		$package = $this->package_factory->create( $package_id );
		if ( $package ) {
			$title = $package->kind . ' - ' . $package->title;
		}

		return $title;
	}

	final function get_editor_string_name( $name, $package ) {
		$package    = $this->package_factory->create( $package );
		$package_id = $package->ID;
		$title      = $this->get_editor_string_element( $name, $package_id, 'title' );
		if ( $title && $title != '' ) {
			$name = $title;
		} elseif (
			\WPML\FP\Str::includes( self::PREFIX_BATCH_STRING, $name )
			&& ( $string = $this->get_st_string_by_batch_name( $name ) )
		) {
			$name = $this->empty_if_md5( $string->get_name() )
				?: ( $string->get_gettext_context() ?: ( $string->get_context() ?: $name ) );
		}

		return $name;
	}

	private function empty_if_md5( $str ) {
		return preg_replace( '#^((.+)( - ))?([a-z0-9]{32})$#', '$2', $str );
	}

	/**
	 * @param string $batch_string_name
	 *
	 * @return WPML_ST_String|null
	 * @throws \WPML\Auryn\InjectionException
	 */
	private function get_st_string_by_batch_name( $batch_string_name ) {
		$string_id = (int) \WPML\FP\Str::replace( self::PREFIX_BATCH_STRING, '', $batch_string_name );
		if ( $string_id ) {
			$string_factory = WPML\Container\make( WPML_ST_String_Factory::class );

			return $string_factory->find_by_id( $string_id );
		}

		return null;
	}

	final function get_editor_string_style( $style, $field_type, $package ) {
		$package       = $this->package_factory->create( $package );
		$package_id    = $package->ID;
		$element_style = $this->get_editor_string_element( $field_type, $package_id, 'type' );
		if ( $element_style ) {
			$style = 0;
			if ( defined( $element_style ) ) {
				$style = constant( $element_style );
			}
		}

		return $style;
	}

	final public function get_element_id_from_package_filter( $default, $package_id ) {
		global $wpdb;
		$element_id_query   = 'SELECT name FROM ' . $wpdb->prefix . 'icl_string_packages WHERE ID=%d';
		$element_id_prepare = $wpdb->prepare( $element_id_query, $package_id );
		$element_id         = $wpdb->get_var( $element_id_prepare );
		if ( ! $element_id ) {
			$element_id = $default;
		}

		return $element_id;
	}

	final public function get_package_type( $type, $post_id ) {
		$package = $this->package_factory->create( $post_id );
		if ( $package ) {
			return $this->get_package_context( $package );
		} else {
			return $type;
		}
	}

	final public function get_package_type_prefix( $type, $post_id ) {
		if ( $type == 'package' ) {
			$package = $this->package_factory->create( $post_id );
			if ( $package ) {
				$type = $package->get_string_context_from_package();
			}
		}

		return $type;
	}

	/**
	 * @param string        $language_for_element
	 * @param \WPML_Package $current_document
	 *
	 * @return null|string
	 */
	final public function get_language_for_element( $language_for_element, $current_document ) {
		if ( $this->is_a_package( $current_document ) ) {
			global $sitepress;
			$language_for_element = $sitepress->get_language_for_element( $current_document->ID, $current_document->get_translation_element_type() );
		}

		return $language_for_element;
	}

	final protected function get_package_context( $package ) {
		$package = $this->package_factory->create( $package );

		return $package->kind_slug . '-' . $package->name;
	}

	final function delete_packages_ajax() {
		if ( ! $this->verify_ajax_call( 'wpml_package_nonce' ) ) {
			die( 'verification failed' );
		}
		$packages_ids = $_POST['packages'];

		$this->delete_packages( $packages_ids );

		exit;
	}

	final function delete_package_action( $name, $kind ) {
		$package_data['name'] = $name;
		$package_data['kind'] = $kind;

		$package = $this->package_factory->create( $package_data );
		if ( $package && $package->ID && $this->is_a_package( $package ) ) {
			$this->delete_package( $package->ID );
			$this->flush_cache();
		}
	}

	/** @param int $post_id */
	final public function remove_post_packages( $post_id ) {
		$packages = $this->get_post_string_packages( array(), $post_id );

		foreach ( $packages as $package ) {
			$this->delete_package( $package->ID );
		}
	}

	final protected function delete_packages( $packages_ids ) {
		$flush_cache = false;

		foreach ( $packages_ids as $package_id ) {
			$this->delete_package( $package_id );

			$flush_cache = true;
		}
		if ( $flush_cache ) {
			$this->flush_cache();
		}
	}

	final function change_package_lang_ajax() {
		global $wpdb, $sitepress;

		if ( ! $this->verify_ajax_call( 'wpml_package_nonce' ) ) {
			die( 'verification failed' );
		}

		$package_id = $_POST['package_id'];

		$package = $this->package_factory->create( $package_id );
		$package->set_strings_language( $_POST['package_lang'] );

		$package_job = new WPML_Package_TM( $package );
		$package_job->set_language_details( $_POST['package_lang'] );

		$args = json_decode( base64_decode( $_POST['args'] ) );
		$args = array_map( 'sanitize_text_field', (array) $args );

		$package_metabox = new WPML_Package_Translation_Metabox( $package, $wpdb, $sitepress, $args );
		$response        = array(
			'metabox' => $package_metabox->get_metabox_status(),
			'lang'    => $package_metabox->get_package_language_name(),
		);

		wp_send_json( $response );
	}

	/**
	 * @param string $string_name
	 * @param int    $package_id
	 * @param string $column
	 *
	 * @return string
	 */
	private function get_editor_string_element( $string_name, $package_id, $column ) {
		global $wpdb;

		$element_query    = 'SELECT ' . $column . "
						FROM {$wpdb->prefix}icl_strings
						WHERE string_package_id=%d AND name=%s";
		$element_prepared = $wpdb->prepare( $element_query, array( $package_id, $string_name ) );
		$element          = $wpdb->get_var( $element_prepared );

		return $element;
	}

	private function flush_cache() {
		// delete the cache key we use
		wp_cache_delete( 'get_all_packages', $this->cache_group );
	}

	/**
	 * @param WPML_Package $package
	 *
	 * @return int
	 */
	final public static function create_new_package( WPML_Package $package ) {
		$package_id = $package->create_new_package_record();

		$tm = new WPML_Package_TM( $package );

		$tm->update_package_translations( true );

		return (int) $package_id;
	}

	/**
	 * @param int $package_id
	 */
	private function init_package_registered_strings( $package_id ) {
		if ( ! isset( $this->registered_strings[ $package_id ] ) ) {
			$this->registered_strings[ $package_id ] = array( 'strings' => array() );
		}
	}

	/**
	 * @param int    $package_id
	 * @param string $string_type
	 * @param string $string_title
	 * @param string $string_name
	 * @param string $string_value
	 */
	private function set_package_registered_strings( $package_id, $string_type, $string_title, $string_name, $string_value ) {
		$this->registered_strings[ $package_id ]['strings'][ $string_name ] = array(
			'title' => $string_title,
			'kind'  => $string_type,
			'value' => $string_value,
		);
	}

	/**
	 * @param \stdClass|\WPML_Package|array|int $package
	 * @param string                            $string_name
	 * @param string                            $string_value
	 *
	 * @return bool|int|mixed
	 */
	private function get_string_id_from_package( $package, $string_name, $string_value ) {
		if ( ! $package instanceof WPML_Package ) {
			$package = $this->package_factory->create( $package );
		}

		return $package->get_string_id_from_package( $string_name, $string_value );
	}

	final function get_external_id_from_package( $package ) {
		return 'external_' . $package['kind_slug'] . '_' . $package['ID'];
	}

	final function get_string_context( $package ) {
		return sanitize_title_with_dashes( $package['kind_slug'] . '-' . $package['name'] );
	}

	final function get_package_id( $package, $from_cache = true ) {
		global $wpdb;
		static $cache = array();

		if ( $this->is_a_package( $package ) ) {
			$package = object_to_array( $package );
		}

		$key = $this->get_string_context( $package );
		if ( ! $from_cache || ! array_key_exists( $key, $cache ) ) {
			$package_id_query   = "SELECT ID FROM {$wpdb->prefix}icl_string_packages WHERE kind_slug = %s AND name = %s";
			$package_id_prepare = $wpdb->prepare( $package_id_query, array( $package['kind_slug'], $package['name'] ) );
			$package_id         = $wpdb->get_var( $package_id_prepare );
			if ( ! $package_id ) {
				return false;
			}
			$cache[ $key ] = $package_id;
		}

		return $cache[ $key ];
	}

	final public function get_all_packages() {
		global $wpdb;
		$cache_key   = 'get_all_packages';
		$cache_found = false;

		$all_packages = wp_cache_get( $cache_key, $this->cache_group, false, $cache_found );

		if ( ! $cache_found ) {
			$all_packages            = array();
			$all_packages_data_query = "SELECT * FROM {$wpdb->prefix}icl_string_packages";
			$all_packages_data       = $wpdb->get_results( $all_packages_data_query );
			foreach ( $all_packages_data as $package_data ) {
				$package                      = $this->package_factory->create( $package_data );
				$all_packages[ $package->ID ] = $package;
			}
			if ( $all_packages ) {
				wp_cache_set( $cache_key, $all_packages, $this->cache_group );
			}
		}

		return $all_packages;
	}

	final protected function is_a_package( $element ) {
		return is_a( $element, 'WPML_Package' );
	}

	protected function verify_ajax_call( $ajax_action ) {
		return isset( $_POST['wpnonce'] ) && wp_verify_nonce( $_POST['wpnonce'], $ajax_action );
	}

	protected function sanitize_string_name( $string_name ) {
		$string_name = preg_replace( '/[ \[\]]+/', '-', $string_name );

		return $string_name;
	}

	public function refresh_packages() {

		// TODO: deprecated.
		// This is required to support Layouts 1.0
		do_action( 'WPML_register_string_packages', 'layout', array() );
		// TODO: END deprecated.

		do_action( 'wpml_register_string_packages' );
	}

	/**
	 * @param WPML_Package $package
	 */
	private function maybe_update_package( $package ) {
		if ( $package->new_title ) {
			$package->title = $package->new_title;
			$package->update_package_record();
		}
	}

	public function change_language_of_strings( $strings, $lang ) {
		global $wpdb;

		$all_ok = true;

		$strings_in = implode( ',', $strings );

		$string_packages_query = "SELECT DISTINCT string_package_id FROM {$wpdb->prefix}icl_strings WHERE id IN ($strings_in)";
		$package_ids           = $wpdb->get_col( $string_packages_query );

		foreach ( $package_ids as $package_id ) {
			if ( $package_id ) {
				$package = $this->package_factory->create( $package_id );
				if ( ! $package->are_all_strings_included( $strings ) ) {
					$all_ok = false;
					break;
				}
			}
		}

		if ( $all_ok ) {
			$this->set_packages_language( $package_ids, $lang );
		}

		$response            = array();
		$response['success'] = $all_ok;
		if ( ! $all_ok ) {
			$response['error'] = __( 'Some of the strings selected belong to packages. You can only change the language of these strings if all strings in the packages are selected.', 'wpml-string-translation' );
		}

		return $response;
	}

	public function change_language_of_strings_in_domain( $domain, $langs, $to_lang ) {
		global $wpdb;

		if ( ! empty( $langs ) ) {
			foreach ( $langs as &$lang ) {
				$lang = "'" . $lang . "'";
			}
			$langs = implode( ',', $langs );

			$string_packages_query = "SELECT DISTINCT string_package_id FROM {$wpdb->prefix}icl_strings WHERE context='%s' AND language IN ($langs)";
			$string_packages_query = $wpdb->prepare( $string_packages_query, $domain );
			$package_ids           = $wpdb->get_col( $string_packages_query );

			$this->set_packages_language( $package_ids, $to_lang );
		}

	}

	private function set_packages_language( $package_ids, $lang ) {
		foreach ( $package_ids as $package_id ) {
			if ( $package_id ) {
				$package = $this->package_factory->create( $package_id );
				$package->set_strings_language( $lang );

				$package_job = new WPML_Package_TM( $package );
				$package_job->set_language_details( $lang );
			}
		}
	}

	/**
	 * @param null|array $packages
	 * @param int        $post_id
	 *
	 * @return WPML_Package[]
	 */
	public function get_post_string_packages( $packages, $post_id ) {
		global $wpdb;

		$query       = $wpdb->prepare( "SELECT ID FROM {$wpdb->prefix}icl_string_packages WHERE post_id = %d", $post_id );
		$package_ids = $wpdb->get_col( $query );

		$packages = $packages ? $packages : array();

		foreach ( $package_ids as $package_id ) {
			$packages[ $package_id ] = $this->package_factory->create( $package_id );
		}

		return $packages;
	}

	/**
	 * @param int         $string_id
	 * @param string      $language
	 * @param string|null $value
	 * @param int|bool    $status
	 * @param int|null    $translator_id
	 * @param int|null    $translation_service
	 * @param int|null    $batch_id
	 */
	public function add_string_translation_action( $string_id, $language, $value = null, $status = false, $translator_id = null, $translation_service = null, $batch_id = null ) {
		icl_add_string_translation( $string_id, $language, $value, $status, $translator_id, $translation_service, $batch_id );
	}

	/**
	 * @param mixed     $package
	 * @param int|array $package_data
	 *
	 * @return WPML_Package
	 */
	public function get_string_package( $package, $package_data ) {
		return $this->package_factory->create( $package_data );
	}

	public function start_string_package_registration_action( $package ) {
		$this->package_cleanup->record_existing_strings( $this->package_factory->create( $package ) );
	}

	public function delete_unused_package_strings_action( $package ) {
		$this->package_cleanup->delete_unused_strings( $this->package_factory->create( $package ) );
	}
	
	/**
	 * @param string[] $packages
	 *
	 * @return string[]
	 */
	public function get_active_string_package_kinds( $packages ) {
		$addons = [
			'WPML_PAGE_BUILDERS_VERSION'        => [
				'Block' => [
					'title'  => 'Widget',
					'plural' => 'Widgets',
					'slug'   => 'Block',
				],
			],
			'ACFML_VERSION'                     => [
				'acf-field-group'         => [
					'title'  => 'Widget',
					'plural' => 'Widgets',
					'slug'   => 'acf-field-group',
				],
				'acf-post-type-labels'    => [
					'title'  => 'ACF Custom Post Type',
					'plural' => 'ACF Custom Post Type',
					'slug'   => 'acf-post-type-labels',
				],
				'acf-taxonomy-labels'     => [
					'title'  => 'ACF Custom Taxonomy',
					'plural' => 'ACF Custom Taxonomies',
					'slug'   => 'acf-taxonomy-labels',
				],
				'acf-options-page-labels' => [
					'title'  => 'ACF Option Page',
					'plural' => 'ACF Option Pages',
					'slug'   => 'acf-options-page-labels',
				],
			],
			'WPML_WP_FORMS_VERSION'             => [
				'wpforms'  => [
					'title'  => 'WP Form',
					'plural' => 'WP Forms',
					'slug'   => 'wpforms',
				],
			],
			'GRAVITYFORMS_MULTILINGUAL_VERSION' => [
				'gravity_form'  => [
					'title'  => 'Gravity Form',
					'plural' => 'Gravity Forms',
					'slug'   => 'gravity_form',
				],
			],
			'WPML_NINJA_FORMS_VERSION'          => [
				'ninja-forms'  => [
					'title'  => 'Ninja Form',
					'plural' => 'Ninja Forms',
					'slug'   => 'ninja-forms',
				],
			],
		];

		$isAddonEnabled = function( $value, $key ) {
			return defined( $key );
		};

		$automaticPackages = wpml_collect( $addons )
			->filter( $isAddonEnabled )
			->collapse()
			->all();

		if ( is_array( $packages ) ) {
			return array_merge( $packages, $automaticPackages );
		}

		return $automaticPackages;
	}

}
