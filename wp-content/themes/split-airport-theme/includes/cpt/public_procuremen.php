<?php
function register_public_procurement_cpt() {
    $singular = 'Public Procurement';
    $plural = 'Public Procurements';

    $slug = str_replace(' ', '-', strtolower($singular)); // public-procurement

    $labels = array(
        'name'               => __( $plural, 'split' ),
        'singular_name'      => __( $singular, 'split' ),
        'add_new'            => _x( 'Add New', 'split', 'split' ),
        'add_new_item'       => __( 'Add New ' . $singular, 'split' ),
        'edit'               => __( 'Edit', 'split' ),
        'edit_item'          => __( 'Edit ' . $singular, 'split' ),
        'new_item'           => __( 'New ' . $singular, 'split' ),
        'view'               => __( 'View ' . $singular, 'split' ),
        'view_item'          => __( 'View ' . $singular, 'split' ),
        'search_term'        => __( 'Search ' . $plural, 'split' ),
        'parent'             => __( 'Parent ' . $singular, 'split' ),
        'not_found'          => __( 'No ' . $plural . ' found', 'split' ),
        'not_found_in_trash' => __( 'No ' . $plural . ' in Trash', 'split' ),
    );

    $args = array(
        'labels'             => $labels,
        'hierarchical'       => false,
        'public'             => true,
        'show_in_menu'       => true,
        'show_in_nav_menus'  => true,
        'has_archive'        => true,
        'rewrite'            => array('slug' => $slug),
        'menu_icon'          => 'dashicons-media-spreadsheet',
        'supports'           => array( 'title' ),
    );

    register_post_type($slug, $args);
}
add_action('init', 'register_public_procurement_cpt');

function hide_slug_meta_box_for_public_procurement() {
    remove_meta_box('slugdiv', 'public-procurement', 'normal');
}
add_action('add_meta_boxes', 'hide_slug_meta_box_for_public_procurement');
