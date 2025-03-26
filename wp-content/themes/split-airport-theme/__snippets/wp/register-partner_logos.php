<?php 

/**
 * Up&Up Admin logo
 *
 * @return void
 */
function up3up_remove_admin_wp_logo()
{
   global $wp_admin_bar;
   $wp_admin_bar->remove_menu('wp-logo');
}
add_action('wp_before_admin_bar_render', 'up3up_remove_admin_wp_logo', 0);
add_action( 'admin_bar_menu', function( \WP_Admin_Bar $bar )
{
   $bar->add_menu( array(
         'id'     => 'wpse',
         'parent' => null,
         'group'  => null,
         'title'  => __( '<img style="width: 30px" src="https://www.up3up.it/wp-content/uploads/favicon.ico" />', 'some-textdomain' ),
         'href'   => 'https://www.up3up.it/',
         'meta'   => array(
               'target'   => '_blank',
               'title'    => __( 'Up&Up Support', 'split' ),
               'html'     => '',
               'class'    => 'wpse--item',
               'rel'      => 'up3up',
               'tabindex' => PHP_INT_MAX,
         ),
   ) );
} );

/**
 * WK Admin logo
 *
 * @return void
 */
function wk_remove_admin_wp_logo()
{
   global $wp_admin_bar;
   $wp_admin_bar->remove_menu('wp-logo');
}
add_action( 'wp_before_admin_bar_render', 'wk_remove_admin_wp_logo', 0);
add_action( 'admin_bar_menu', function( \WP_Admin_Bar $bar )
{
   $bar->add_menu( array(
         'id'     => 'wpse',
         'parent' => null,
         'group'  => null,
         'title'  => __( '<img style="width: 130px;" src="https://www.webkrunch.be/media/images/webkrunch-logo-wp.png" />', 'some-textdomain' ),
         'href'   => 'https://www.webkrunch.be',
         'meta'   => array(
               'target'   => '_blank',
               'title'    => __( 'Webkrunch Support', 'split' ),
               'html'     => '',
               'class'    => 'wpse--item',
               'rel'      => 'webkrunch',
               'tabindex' => PHP_INT_MAX,
         ),
   ) );
} );

/**
 * LF Admin logo
 *
 * @return void
 */
function lf_remove_admin_wp_logo()
{
   global $wp_admin_bar;
   $wp_admin_bar->remove_menu('wp-logo');
}

add_action('wp_before_admin_bar_render', 'lf_remove_admin_wp_logo', 0);
add_action( 'admin_bar_menu', function( \WP_Admin_Bar $bar )
{
   $bar->add_menu( array(
         'id'     => 'wpse',
         'parent' => null,
         'group'  => null,
         'title'  => __( '<img style="width: 15px;margin-top: 5px;" src="https://www.librafire.com/lf_content_moved/wp-content/uploads/2017/10/Slider-logo.png" />', 'some-textdomain' ),
         'href'   => 'https://librafire.com',
         'meta'   => array(
               'target'   => '_blank',
               'title'    => __( 'LibraFire Support', 'split' ),
               'html'     => '',
               'class'    => 'wpse--item',
               'rel'      => 'librafire',
               'tabindex' => PHP_INT_MAX,
         ),
   ) );
} );

?>