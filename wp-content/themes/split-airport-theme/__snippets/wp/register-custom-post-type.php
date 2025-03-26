<?php

function split_register_post_type() {
    $singular = 'Custom post type name'; // Book
	$plural = 'Custom post type names';  // Books
	
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
        'menu_icon'           => '',
        'supports'            => array( 'title', 'thumbnail', 'editor' )
    );

    register_post_type( $slug, $args );
}

add_action( 'init', 'split_register_post_type' );