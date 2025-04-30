<?php

add_action('wp_ajax_nopriv_airlines_search', 'airlines_search');
add_action('wp_ajax_airlines_search', 'airlines_search');

function airlines_search()
{

    if (isset($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'], 'security')) {
        $term = isset($_POST['term']) ? wp_strip_all_tags($_POST['term']) : "";

        $airlines = new WP_Query([
            'post_type'      => 'airline',
            'posts_per_page' => -1,
            'orderby'        => [
                'meta_value' => 'ASC',
                'title'      => 'ASC',
            ],
            'meta_key'       => 'type',
            'order'          => 'ASC',
            's'              => $term,
        ]);

        ob_start();


        if ($airlines->have_posts()) {
            while ($airlines->have_posts()) {
                $airlines->the_post();
                get_template_part('template-parts/posts/airline');
            }
        } else {
            get_template_part('template-parts/posts/no-posts');
        }

        $airlines_html = ob_get_clean();

        wp_send_json_success([
            'airlines' =>  $airlines_html,
        ]);
    } else {
        die(__('Security check', 'split-airport'));
    }

    die();
}
