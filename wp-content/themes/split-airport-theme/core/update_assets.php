<?php

/**
 * Register a custom menu page.
 */
function register_regenrate_assets_menu_page() {
    add_submenu_page( 
        'tools.php', 
        __( 'Regenerate Assets', 'split' ), 
        __( 'Regenerate Assets', 'split' ),
        'manage_options', 
        'regenerate-assets', 
        'regenerate_assets_output'
    );
}

add_action( 'admin_menu', 'register_regenrate_assets_menu_page' );

function regenerate_assets_output() {
    $current_admin_page = admin_url( "admin.php?page=".$_GET["page"] );
    
    ?>
    <div class="wrap">
        <h1><?php _e( 'Regenerate Assets', 'split' ) ?></h1>
        <a href="<?php echo $current_admin_page.'&regenerate=true'?>" class="page-title-action" style="margin: 10px 0;display: inline-block;"><?php _e( 'Regenerate', 'split' ); ?></a>
    </div>

    <?php
}

/**
 * Regenerate Assets.
 */
function regenerate_assets() {
    if (isset($_GET['regenerate']) && $_GET['regenerate'] == true) {

        global $wpdb;
        $prefix = $wpdb->prefix;

        // Get all custom post types
        $args = array(
            'public'   => true,
            '_builtin' => false,
        );

        $output = 'names';
        $post_types = get_post_types($args, $output);

        // Add build ones
        $post_types['page'] = 'page';
        $post_types['post'] = 'post';

        if ($post_types) {
            foreach ($post_types as $post_type) {

                if ($post_type) {

                    $sql = $wpdb->prepare("SELECT * FROM ".$prefix."posts WHERE post_type='%s'", $post_type);
                    $posts = $wpdb->get_results($sql);

                    if ($posts) {
                        foreach ($posts as $post_object) {
                            call_user_func('update_block_assets', $post_object->ID, $post_object, true);
                        }
                    }
                }
            }
        }

        call_user_func('update_block_assets_reload');
    }
}
add_action('init', 'regenerate_assets');