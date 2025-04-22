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

if ( ! function_exists( 'cpt_flexible_pagination' ) ) :

    function cpt_flexible_pagination( $paged = '', $max_page = '' ) {
        $big = 999999999; // need an unlikely integer
        if( ! $paged ) {
            $paged = get_query_var('paged');
        }

        if( ! $max_page ) {
            global $wp_query;
            $max_page = isset( $wp_query->max_num_pages ) ? $wp_query->max_num_pages : 1;
        }
        ?>
        <div class="flexible-post-nav">
            <div class="nav-links">
            <?php
            
            echo paginate_links( array(
                'base'       => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
                'format'     => '?paged=%#%',
                'current'    => max( 1, $paged ),
                'total'      => $max_page,
                'prev_text'          => __('<i class="icon icon-arrow-left" aria-hidden="true"></i>'),
                'next_text'          => __('<i class="icon icon-arrow-right" aria-hidden="true"></i>')
            ) );
            ?>
            </div>
        </div>
        <?php
    }
endif;


if ( ! function_exists( 'get_distinct_year_values_in' ) ) :

    function get_distinct_year_values_in( $meta_key = '', $meta_key_2 = '', $post_type = 'post', $post_status = 'publish' ) {
        global $wpdb;
        
        if( empty( $meta_key ) || empty( $meta_key_2 ) ) {
            return array();
        }
        
        $meta_values = $wpdb->get_col( $wpdb->prepare("
            SELECT DISTINCT SUBSTRING(pm.meta_value, 1, 4) FROM {$wpdb->postmeta} pm 
            LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id 
            WHERE (pm.meta_key = %s or pm.meta_key = %s)
            AND p.post_type = %s 
            AND p.post_status = %s 
            ORDER BY meta_value ASC
        ", $meta_key, $meta_key_2, $post_type, $post_status ) );
        
        return $meta_values;
    }

endif;

?>
