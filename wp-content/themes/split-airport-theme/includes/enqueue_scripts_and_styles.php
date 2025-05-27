<?php

/**
 * Enqueue custom scripts and styles.
 */
function custom_scripts_and_styles() {}
add_action('wp_enqueue_scripts', 'custom_scripts_and_styles');

/**
 * Global JS Object
 */

add_action('wp_head', 'global_js_object', 1);

function global_js_object()
{

	$global_js_object = array(
		'ajaxUrl' 			        			=> admin_url('admin-ajax.php'),
		'searchRestUrl' 						=> get_site_url() . '/wp-json/splitAirport/v1/search',
		'flightRestUrl'							=> get_site_url() . '/wp-json/splitAirport/v1/flight',
		'myFlightsRestUrl'						=> get_site_url() . '/wp-json/splitAirport/v1/myflights',
		'checkMyFlightsRestUrl'						=> get_site_url() . '/wp-json/splitAirport/v1/check-my-flights',
		'nonce'									=> wp_create_nonce('security'),
		'restNonce' 							=> wp_create_nonce('wp_rest'),
		'FlightTypeTableStingArrival'   		=> __('Arriving from', 'split-airport'),
		'FlightTypeTableStingDeparture'			=> __('Going to', 'split-airport'),
		'gateTableString'						=> __('Gate', 'split-airport'),
		'earlierFlightsButtonBack'				=> __('Back to current flights', 'split-airport'),
		'earlierFlightsButtonShow'				=> __('Show earlier flights', 'split-airport'),
		'currentLanguage'						=> apply_filters('wpml_current_language', null),
		'unfollowButtonText'                	=> __('Unfollow this flight', 'split-airport'),
		'followButtonText'                		=> __('Follow this flight', 'split-airport'),
		'noMyFlights'							=> __('Your favorites list is empty.', 'split-airport'),
	);

	$global_js_object = json_encode($global_js_object);

	echo "<script>
			const theme = " . $global_js_object . "
		  </script>";
}
