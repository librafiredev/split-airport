<?php

/**
 * Enqueue custom scripts and styles.
 */
function custom_scripts_and_styles()
{

	$theme_options = array();

	if (defined('ACF_GOOGLE_API_KEY')) {
		$theme_options['google_api_key'] = ACF_GOOGLE_API_KEY;
	}

	wp_localize_script('main', 'theme', $theme_options);
}
add_action('wp_enqueue_scripts', 'custom_scripts_and_styles');

/**
 * Global JS Object
 */

add_action('wp_head', 'global_js_object', 1);

function global_js_object()
{

	$global_js_object = array(
		'ajaxUrl' 			        	=> admin_url('admin-ajax.php'),
		'searchRestUrl' 				=> get_site_url( ) . '/wp-json/splitAirport/v1/search',
		'flightRestUrl'					=> get_site_url( ) . '/wp-json/splitAirport/v1/flight',
		'nonce'							=> wp_create_nonce('security'),
		'restNonce' 					=> wp_create_nonce( 'wp_rest' ),
		'FlightTypeTableStingArrival'   => __('Arriving from', 'split-airport'),
		'FlightTypeTableStingDeparture'	=> __('Going to', 'split-airport')
	);

	$global_js_object = json_encode($global_js_object);

	echo "<script>
			const theme = " . $global_js_object . "
		  </script>";
}
