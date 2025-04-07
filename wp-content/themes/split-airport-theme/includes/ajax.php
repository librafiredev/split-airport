<?php

add_action('wp_ajax_nopriv_airlines_search', 'airlines_search');
add_action('wp_ajax_airlines_search', 'airlines_search');

function airlines_search()
{

    if (isset($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'], 'security')) {
        $term = isset($_POST['term']) ? wp_strip_all_tags($_POST['term']) : "";

        $supported_airlines = new WP_Query([
            'post_type'             => 'airline',
            'posts_per_page'        => -1,
            'orderby'               => 'title',
            'order'                 => "ASC",
            's'                     => $term,
            'meta_query' => [
                [
                    'key' => 'type',
                    'value' => 'supported',
                    'compare' => '=',
                ]
            ],
        ]);

        ob_start();


        if ($supported_airlines->have_posts()) {
            while ($supported_airlines->have_posts()) {
                $supported_airlines->the_post();
                get_template_part('template-parts/posts/airline');
            }
        } else {
            get_template_part('template-parts/posts/no-posts');
        }

        $supported_airlines_html = ob_get_clean();

        $unsupported_airlines = new WP_Query([
            'post_type'             => 'airline',
            'posts_per_page'        => -1,
            'orderby'               => 'title',
            'order'                 => "ASC",
            's'                     => $term,
            'meta_query' => [
                [
                    'key' => 'type',
                    'value' => 'unsupported',
                    'compare' => '=',
                ]
            ],
        ]);

        ob_start();

        if ($unsupported_airlines->have_posts()) {
            while ($unsupported_airlines->have_posts()) {
                $unsupported_airlines->the_post();
                get_template_part('template-parts/posts/airline');
            }
        } else {
            get_template_part('template-parts/posts/no-posts');
        }

        $unsupported_airlines_html = ob_get_clean();

        wp_send_json_success([
            'supported_airlines' => $supported_airlines_html,
            'unsupported_airlines' => $unsupported_airlines_html
        ]);
    } else {
        die(__('Security check', 'split-airport'));
    }

    die();
}
