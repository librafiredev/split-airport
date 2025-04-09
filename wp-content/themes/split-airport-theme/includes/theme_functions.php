<?php 

use SplitAirport\FlightsUpdate;

FlightsUpdate::init();
if( function_exists('acf_add_options_page') ) {

	acf_add_options_page(array(
		'page_title' 	=> 'Warnings',
		'menu_title'	=> 'Warnings',
		'menu_slug' 	=> 'warning-settings',
		'capability'	=> 'edit_posts',
        'icon_url'      => 'dashicons-warning',
		'redirect'		=> false
	));

}

?>