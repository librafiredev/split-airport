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

function custom_language_selector() {
    $languages = apply_filters('wpml_active_languages', null, array('skip_missing' => 0));
    if (!empty($languages)) {
        echo '<div class="language-menu">';
        
        // Prikaz aktivnog jezika
        foreach ($languages as $language) {
            if ($language['active']) {
                $name = $language['language_code'] === 'sr' ? 'Srpski' : $language['native_name'];
                echo '<div class="current-lang">';
                echo esc_html(substr($name, 0, 3));
                echo '</div>';
            }
        }

        // Prikaz ostalih jezika
        echo '<ul>';
        foreach ($languages as $language) {
            $name = $language['language_code'] === 'sr' ? 'Srpski' : $language['native_name'];
            echo '<li><a href="' . esc_url($language['url']) . '" class="language">';
            echo esc_html($name);
            echo '</a></li>';
        }
        echo '</ul>'; // Zatvara dropdown
        echo '</div>'; // Zatvara meni jezika
    }
}

?>