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
                $flag_path = '';
                switch ($language['language_code']) {
                    case 'hr':
                        $flag_path = 'croatia_flag.svg';
                        break;
                    case 'en':
                        $flag_path = 'eng_flag.svg';
                        break;
                }

                echo '<div class="current-lang">';
                echo '<img src="' . esc_url(get_template_directory_uri() . '/assets/images/' . $flag_path) . '" alt="' . esc_attr($language['native_name']) . '" width="24" height="24">';
                echo '</div>';
            }
        }

        echo '<ul>';
        foreach ($languages as $language) {
            $flag_path = '';
            switch ($language['language_code']) {
                case 'hr':
                    $flag_path = 'croatia_flag.svg';
                    break;
                case 'en':
                    $flag_path = 'eng_flag.svg';
                    break;
            }

            $background = $language['active'] ? '#f3f8fc' : '#ffffff';

            echo '<li style="background-color: ' . $background . ';">';
            echo '<a href="' . esc_url($language['url']) . '" class="language">';
            echo '<img src="' . esc_url(get_template_directory_uri() . '/assets/images/' . $flag_path) . '" alt="' . esc_attr($language['native_name']) . '" width="24" height="24">';
            echo '</a>';
            echo '</li>';
        }
        echo '</ul>';
        echo '</div>';
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
                'prev_text'          => __('<'),
                'next_text'          => __('>')
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
            WHERE (pm.meta_key = %s OR pm.meta_key = %s)
            AND p.post_type = %s 
            AND p.post_status = %s 
            ORDER BY meta_value ASC
        ", $meta_key, $meta_key_2, $post_type, $post_status ) );
        
        return $meta_values;
    }

endif;

function new_excerpt_more($more) {
    return '...';
}

add_filter('excerpt_more', 'new_excerpt_more');

add_action( 'pre_get_posts', function( $query ) {
  if ( is_post_type_archive( 'tender' ) && $query->is_main_query() && !is_admin() ) {
    $dateNow = date('Y-m-d');
    // this has to be done to make sure categories don't repeat on different pages
    $query->set('posts_per_page', -1);
    $query->set('meta_query', array(
        array(
            'key'           => 'end_date',
            'compare'       => '>=',
            'value'         => $dateNow,
            'type'          => 'DATE',
        ),
    ));
  }
});

if( ! function_exists('get_finished_tenders_title') ) :

    function get_finished_tenders_title($term_name) {
        $title_prefix = get_field('tender_category_prefix', 'option');
        $base_title = !empty($title_prefix) ? strtolower($term_name) : $term_name;
        return (!empty($title_prefix) ? ($title_prefix . ' ') : '') . $base_title;
    }

endif;

add_action('init', function() {
    if (!wp_next_scheduled('auto_delete_expired_procurements')) {
        wp_schedule_event(time(), 'daily', 'auto_delete_expired_procurements');
    }
});

// Funkcija koja briše postove sa isteklog roka
add_action('auto_delete_expired_procurements', function() {
    $today = date('Y-m-d');

    $args = [
        'post_type' => 'public-procurement',
        'posts_per_page' => -1,
        'post_status' => 'publish',
    ];

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();

            $items = get_field('procurement_custom_table', $post_id);
            if (!$items || !is_array($items)) {
                continue; // Ako nema stavki, preskoči
            }

            $all_expired = true;

            foreach ($items as $item) {
                $deadline_raw = trim($item['deadline']);
                $deadline_raw = rtrim($deadline_raw, '.');
                $deadline_date = DateTime::createFromFormat('d.m.Y', $deadline_raw);

                if ($deadline_date && $deadline_date->format('Y-m-d') >= $today) {
                    $all_expired = false;
                    break;
                }
            }

            if ($all_expired) {
                wp_delete_post($post_id, true);
            }
        }
        wp_reset_postdata();
    }
});

?>
