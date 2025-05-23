<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 3.0
 */
class FrmProFieldStar extends FrmFieldType {

	const MAX_STARS = 10;

	/**
	 * @var string
	 * @since 3.0
	 */
	protected $type = 'star';

	/**
	 * @var bool
	 */
	protected $array_allowed = false;

	protected function input_html() {
		return $this->multiple_input_html();
	}

	protected function include_form_builder_file() {
		return FrmProAppHelper::plugin_path() . '/classes/views/frmpro-fields/front-end/star.php';
	}

	protected function field_settings_for_type() {
		$settings = array(
			'unique' => true,
		);

		FrmProFieldsHelper::fill_default_field_display( $settings );
		return $settings;
	}

	protected function extra_field_opts() {
		return array(
			'minnum' => 1,
			'maxnum' => 5,
		);
	}

	protected function new_field_settings() {
		return array(
			'options' => range( 1, 5 ),
		);
	}

	/**
	 * @since 4.0
	 * @param array $args - Includes 'field', 'display', and 'values'
	 */
	public function show_primary_options( $args ) {
		$field = $args['field'];
		include FrmProAppHelper::plugin_path() . '/classes/views/frmpro-fields/back-end/star-options.php';

		parent::show_primary_options( $args );
	}

	public function get_container_class() {
		// Add class to inline Scale field
		$class = '';
		if ( $this->field['label'] == 'inline' ) {
			$class = ' frm_scale_container';
		}
		return $class;
	}

	protected function include_front_form_file() {
		return FrmProAppHelper::plugin_path() . '/classes/views/frmpro-fields/front-end/star.php';
	}

	protected function prepare_display_value( $value, $atts ) {
		if ( ! isset( $atts['html'] ) || ! $atts['html'] ) {
			return $value;
		}

		FrmStylesController::enqueue_style();

		$max     = $this->get_max_star_rating();
		$numbers = $this->get_rounded_decimal( $value );

		ob_start();

		include FrmProAppHelper::plugin_path() . '/classes/views/frmpro-fields/star_disabled.php';

		$contents = ob_get_contents();
		ob_end_clean();

		$this->maybe_fix_sanitized_star_svgs( $contents );

		return $contents;
	}

	/**
	 * The SVG HTML generated in prepare_display_value gets stripped.
	 * This is because it goes through FrmAppHelper::sanitize_value( 'wp_kses_post', $var )
	 * as well as FrmAppHelper::kses( $var, 'all' );
	 * If the result is the sanitized result of $contents after calling both of those functions
	 * then change it back to the string HTML generated in prepare_display_value.
	 *
	 * @since 6.6
	 *
	 * @param string $contents
	 */
	private function maybe_fix_sanitized_star_svgs( $contents ) {
		/**
		 * @param string   $value
		 * @param stdClass $field
		 * @param string   $contents The SVG HTML generated in prepare_display_value.
		 * @param Closure  $filter This is passed so we can remove it when it's finished.
		 * @return string Either the same HTML as before, or the HTML generated in prepare_display_value.
		 */
		$filter = function ( $value, $field ) use ( $contents, &$filter ) {
			$field_id = absint( is_array( $this->field ) ? $this->field['id'] : $this->field->id );
			if ( (int) $field->id !== $field_id ) {
				// Not this field so leave it alone.
				return $value;
			}

			$compare_value = $contents;
			FrmAppHelper::sanitize_value( 'wp_kses_post', $compare_value );
			$compare_value = FrmAppHelper::kses( $compare_value, 'all' );

			if ( $value === $compare_value ) {
				$value = $contents;
			}

			// Clean up as this filter only needs to happen once.
			remove_filter( 'frm_display_value', $filter, 1 );

			return $value;
		};

		add_filter( 'frm_display_value', $filter, 1, 2 );
	}

	/**
	 * Try to determine the maximum star rating value.
	 *
	 * @return int
	 */
	private function get_max_star_rating() {
		$options       = $this->get_field_column( 'options' );
		$field_options = $this->get_field_column( 'field_options' );
		$max_setting   = ! empty( $field_options['maxnum'] ) ? $field_options['maxnum'] : false;

		if ( is_array( $options ) ) {
			$max                 = max( $options );
			$options_are_default = 5 === $max && 5 === count( $options );
			if ( false !== $max_setting && $options_are_default ) {
				$max = $max_setting;
			}
		}

		if ( ! isset( $max ) || ! is_numeric( $max ) ) {
			$max = false !== $max_setting ? $max_setting : 5;
		}

		/**
		 * Filter the maximum number of stars that can be displayed.
		 *
		 * @since 6.17
		 *
		 * @param int   $max_stars The maximum number of stars.
		 * @param int   $max       The maximum number of stars based on the field options.
		 * @param array $field     The field array.
		 */
		$max_limit = apply_filters( 'frm_pro_max_star_rating', static::MAX_STARS, $max, $this->field );
		if ( $max > $max_limit ) {
			return $max_limit;
		}
		return $max;
	}

	/**
	 * @param mixed $value
	 * @return array
	 */
	private function get_rounded_decimal( $value ) {
		if ( is_array( $value ) ) {
			$value = 0;
		}

		$numbers = array(
			'decimal' => 0,
			'digit'   => $value,
			'value'   => $value,
		);

		if ( $value != floor( $value ) ) {
			$value = round( $value, 2 );
			list( $numbers['digit'], $numbers['decimal'] ) = explode( '.', $value );

			if ( strlen( $numbers['decimal'] ) === 1 ) {
				// make sure there are two digits after the decimal
				$numbers['decimal'] = $numbers['decimal'] * 10;
			}

			if ( $numbers['decimal'] < 25 ) {
				$numbers['decimal'] = 0;
			} elseif ( $numbers['decimal'] < 75 ) {
				$numbers['decimal'] = 5;
			} else {
				$numbers['decimal'] = 0;
				++$numbers['digit'];
			}

			$numbers['value'] = (float) ( $numbers['digit'] . '.' . $numbers['decimal'] );
		}

		return $numbers;
	}

	/**
	 * @since 4.0.04
	 */
	public function sanitize_value( &$value ) {
		FrmAppHelper::sanitize_value( 'sanitize_text_field', $value );
	}
}
