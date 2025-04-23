<?php

function split_register_tenders_category() {
    $singular = 'Tender Category';
	$plural = 'Tender Categories';
	
    $slug = str_replace( ' ', '-', strtolower( $singular ) );

    $labels = array(
        'name'              => _x( $plural, 'taxonomy general name' ),
        'singular_name'     => _x( 'Category', 'taxonomy singular name' ),
        'search_items'      => __( 'Search ' . $plural ),
        'all_items'         => __( 'All ' . $plural ),
        'parent_item'       => __( 'Parent ' . $singular ),
        'parent_item_colon' => __( 'Parent:' . $singular ),
        'edit_item'         => __( 'Edit ' . $singular ),
        'update_item'       => __( 'Update ' . $singular ),
        'add_new_item'      => __( 'Add New ' . $singular ),
        'new_item_name'     => __( 'New ' . $singular ),
        'menu_name'         => __( $plural ),
    );

    $args = array(
        'labels' => $labels,
        'hierarchical'  => true,
        'public'        => true,
        'show_admin_column' => true,
	);
	
    register_taxonomy( $slug, 'tender', $args );
}
add_action( 'init', 'split_register_tenders_category', 0 );

function split_register_tenders_post_type() {
    $singular = 'Tender';
	$plural = 'Tenders';
	
    $slug = str_replace( ' ', '-', strtolower( $singular ) );

    $labels = array(
        'name' 			      => __( $plural, 'split' ),
        'singular_name' 	  => __( $singular, 'split' ),
        'add_new' 		      => _x( 'Add New', 'split', 'split' ),
        'add_new_item'  	  => __( 'Add New ' . $singular, 'split' ),
        'edit'		          => __( 'Edit', 'split' ),
        'edit_item'	          => __( 'Edit ' . $singular, 'split' ),
        'new_item'	          => __( 'New ' . $singular, 'split' ),
        'view' 			      => __( 'View ' . $singular, 'split' ),
        'view_item' 		  => __( 'View ' . $singular, 'split' ),
        'search_term'   	  => __( 'Search ' . $plural, 'split' ),
        'parent' 		      => __( 'Parent ' . $singular, 'split' ),
        'not_found'           => __( 'No ' . $plural .' found', 'split' ),
        'not_found_in_trash'  => __( 'No ' . $plural .' in Trash', 'split' ),
    );

    $args = array(
        'labels'              => $labels,
        'hierarchical'        => false,
        'public'              => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'has_archive'         => true,
        'rewrite'             => array('slug' => $slug),
        'menu_icon'           => 'dashicons-feedback',
        'supports'            => array( 'title', 'thumbnail', 'editor' ),
    );

    register_post_type( $slug, $args );
}

add_action( 'init', 'split_register_tenders_post_type' );
