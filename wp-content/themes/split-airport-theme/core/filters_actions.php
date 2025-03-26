<?php
/***********************************************************************
** Filters and actions part 
***********************************************************************/

add_filter( 'upload_mimes', 'cc_mime_types');
add_filter( 'tiny_mce_plugins', 'disable_emojis_tinymce' );
add_filter( 'wp_resource_hints', 'disable_emojis_remove_dns_prefetch', 10, 2 );
add_filter( 'tiny_mce_plugins', 'disable_emojis_tinymce' );
add_filter( 'wp_resource_hints', 'disable_emojis_remove_dns_prefetch', 10, 2 );
add_filter( 'frm_field_label_seen', 'split_formidable_customize_html', 11, 3);

add_action( 'login_head', 'split_remove_login_shake' );
add_action( 'admin_head', 'fix_svg_thumb_display');
add_action( 'login_enqueue_scripts', 'lf_login_logo');
add_action( 'acf/init', 'lf_acf_init');
add_action( 'widgets_init', 'split_widgets_init');
add_action( 'after_setup_theme', 'split_after_setup_theme_function', 0);
add_action( 'wp_enqueue_scripts', 'split_scripts_and_styles');
add_action( 'get_footer', 'split_scripts_and_styles_footer');
add_action( 'init', 'split_init_action' );
add_action( 'admin_enqueue_scripts', 'split_load_admin_style' );

if( WEBSITE_TYPE == 0 ):
    // disable gutenberg
    add_filter( 'use_block_editor_for_post', '__return_false', 10);
    add_filter( 'use_block_editor_for_post_type', '__return_false', 10);
    add_action( 'wp_print_styles', 'split_deregister_styles', 100 );
endif;

/***********************************************************************
** Functions part
***********************************************************************/

/**
 * disable_emojis_remove_dns_prefetch
 *
 * @return void
 */
if ( !function_exists('disable_emojis_remove_dns_prefetch') ):

    /**
     * Remove emoji CDN hostname from DNS prefetching hints.
     *
     * @param array $urls URLs to print for resource hints.
     * @param string $relation_type The relation type the URLs are printed for.
     * @return array Difference betwen the two arrays.
     */
    function disable_emojis_remove_dns_prefetch( $urls, $relation_type ) {
        if ( 'dns-prefetch' == $relation_type ) {
        /** This filter is documented in wp-includes/formatting.php */
        $emoji_svg_url = apply_filters( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/' );
    
        $urls = array_diff( $urls, array( $emoji_svg_url ) );
    }
   
    return $urls;
   }

endif;

/**
 * disable_emojis_tinymce
 *
 * @return void
 */
if ( !function_exists('disable_emojis_tinymce') ):

    /**
     * Filter function used to remove the tinymce emoji plugin.
     * 
     * @param array $plugins 
     * @return array Difference betwen the two arrays
     */
    function disable_emojis_tinymce( $plugins ) {
        if ( is_array( $plugins ) ) {
        return array_diff( $plugins, array( 'wpemoji' ) );
        } else {
        return array();
        }
    }

endif;

/**
 * split_deregister_styles
 *
 * @return void
 */
if ( !function_exists('split_init_action') ):

    //Disable gutenberg style in Front
    function split_init_action() {
        remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
        remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
        remove_action( 'wp_print_styles', 'print_emoji_styles' );
        remove_action( 'admin_print_styles', 'print_emoji_styles' ); 
        remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
        remove_filter( 'comment_text_rss', 'wp_staticize_emoji' ); 
        remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
    }

endif;

/**
 * split_deregister_styles
 *
 * @return void
 */
if ( !function_exists('split_deregister_styles') ):

    //Disable gutenberg style in Front
    function split_deregister_styles() {
        wp_dequeue_style( 'wp-block-library' );
    }

endif;

/**
 * split_remove_login_shake
 *
 * @return void
 */
if ( !function_exists('split_remove_login_shake') ):

    // remove error shaking
	function split_remove_login_shake() {
		remove_action( 'login_head', 'wp_shake_js', 12 );
	}

endif;

/**
 * cc_mime_types
 *
 * @param array $mimes
 * @return void
 */
if (!function_exists('cc_mime_types')):

    function cc_mime_types($mimes) {
        $mimes['svg'] = 'image/svg+xml';
        return $mimes;
    }

endif;

/**
 * fix_svg_thumb_display
 *
 * @return void
 */
if (!function_exists('fix_svg_thumb_display')):

    function fix_svg_thumb_display() {
        echo '
					<style>
					td.media-icon img[src$=".svg"], img[src$=".svg"].attachment-post-thumbnail {
					width: 100% !important;
					height: auto !important;
					}
					</style>
				';
    }

endif;

/**
 * Wp-Login Logo Change
 *
 * @return void
 */
if( !function_exists('lf_login_logo') && function_exists('get_field') ):

    function lf_login_logo(){ ?>
            <style type="text/css">
                #login h1 a, .login h1 a {
                    background-image: url(<?php echo(esc_url(get_field('logo','option'))); ?>);
                    width: 100% !important;
                    background-size: contain;
                    max-height: 150px;
                    padding-bottom: 30px;
                }

            </style>
		<?php
    }

endif;

/**
 * lf_acf_init
 *
 * @return void
 */

if (!function_exists('lf_acf_init')):

    /*
     * Google Maps API Key ACF
     * */
    function lf_acf_init() {

        
        if( defined('ACF_GOOGLE_API_KEY') ){
            acf_update_setting('google_api_key', ACF_GOOGLE_API_KEY);
        } else{
            if( is_admin() ){
                add_action( 'admin_notices', function(){
                    ?>
                    <div class="notice notice-error is-dismissible">
                        <p><?php _e( "Please define ACF_GOOGLE_API_KEY in wp-config.php: <strong>define('ACF_GOOGLE_API_KEY', 'YOUR_API_KEY_HERE');</strong>", 'split' ); ?></p>
                    </div>
                    <?php
                } );
            }
        }
    }

endif;

if (!function_exists('split_widgets_init')):

    /**
     * Register widget area.
     *
     * @link http://codex.wordpress.org/Function_Reference/register_sidebar
     */
    function split_widgets_init() {
        register_sidebar(array(
            'name' => esc_html__('Sidebar', 'split'),
            'id' => 'sidebar-1',
            'description' => '',
            'before_widget' => '<aside id="%1$s" class="widget %2$s">',
            'after_widget' => '</aside>',
            'before_title' => '<h4 class="widget-title">',
            'after_title' => '</h4>',
        ));
        register_sidebar(array(
            'name' => esc_html__('Footer 1', 'split'),
            'id' => 'footer-1',
            'description' => '',
            'before_widget' => '<aside id="%1$s" class="widget %2$s">',
            'after_widget' => '</aside>',
            'before_title' => '<h4 class="widget-title">',
            'after_title' => '</h4>',
        ));
        register_sidebar(array(
            'name' => esc_html__('Footer 2', 'split'),
            'id' => 'footer-2',
            'description' => '',
            'before_widget' => '<aside id="%1$s" class="widget %2$s">',
            'after_widget' => '</aside>',
            'before_title' => '<h4 class="widget-title">',
            'after_title' => '</h4>',
        ));
        register_sidebar(array(
            'name' => esc_html__('Footer 3', 'split'),
            'id' => 'footer-3',
            'description' => '',
            'before_widget' => '<aside id="%1$s" class="widget %2$s">',
            'after_widget' => '</aside>',
            'before_title' => '<h4 class="widget-title">',
            'after_title' => '</h4>',
        ));
        register_sidebar(array(
            'name' => esc_html__('Footer 4', 'split'),
            'id' => 'footer-4',
            'description' => '',
            'before_widget' => '<aside id="%1$s" class="widget %2$s">',
            'after_widget' => '</aside>',
            'before_title' => '<h4 class="widget-title">',
            'after_title' => '</h4>',
        ));
    }

endif;

if (!function_exists('after_setup_theme_function')):
    /**
     * Sets up theme defaults and registers support for various WordPress features.
     *
     * Note that this function is hooked into the after_setup_theme hook, which
     * runs before the init hook. The init hook is too late for some features, such
     * as indicating support for post thumbnails.
     */

    function split_after_setup_theme_function() {

        /*
         * Make theme available for translation.
         * Translations can be filed in the /languages/ directory.
         * If you're building a theme based on Split, use a find and replace
         * to change 'split' to the name of your theme in all the template files
         */
        load_theme_textdomain('split', get_template_directory() . '/languages');

		/**
		 * Set the content width in pixels, based on the theme's design and stylesheet.
		 *
		 * Priority 0 to make it available to lower priority callbacks.
		 *
		 * @global int $content_width
		 */
        $GLOBALS['content_width'] = apply_filters('split_content_width', 1170);

        // Add theme support for custom header image
        add_theme_support('custom-header', array(
            'flex-width' => true,
            'width' => 260,
            'flex-height' => true,
            'height' => 100,
            'header-selector' => '.site-title a',
            'header-text' => false,

        ));

        /*
         * Switch default core markup for search form, comment form, and comments
         * to output valid HTML5.
         */
        add_theme_support(
            'html5',
            array(
                'search-form',
                'comment-form',
                'comment-list',
                'gallery',
                'caption',
            )
        );

        /*
         * Enable support for Post Formats.
         * See http://codex.wordpress.org/Post_Formats
         */
        add_theme_support(
            'post-formats',
            array(
                'aside',
                'image',
                'video',
                'quote',
                'link',
            )
        );

        /*
         * Enable support for WooCommerce
         * See http://codex.wordpress.org/Post_Formats
         */
        add_theme_support('woocommerce');

        // Add default posts and comments RSS feed links to head.
        add_theme_support('automatic-feed-links');

        /*
         * Let WordPress manage the document title.
         * By adding theme support, we declare that this theme does not use a
         * hard-coded <title> tag in the document head, and expect WordPress to
         * provide it for us.
         */
        add_theme_support('title-tag');

        /*
         * Enable support for Post Thumbnails on posts and pages.
         *
         * @link http://codex.wordpress.org/Function_Reference/add_theme_support#Post_Thumbnails
         */
		add_theme_support('post-thumbnails');
		
		// This theme uses wp_nav_menu() in one location.
		register_nav_menus(
			array(
				'primary' => esc_html__( 'Primary Menu', 'split' ),
			)
		);

	}
	
	if ( !function_exists('split_scripts_and_styles') ) :

		function split_scripts_and_styles() {

			// wp_enqueue_style('split-style', get_stylesheet_uri());
			// wp_enqueue_style('lf-css', get_stylesheet_directory_uri() . '/dist/style.css', array(), ASSETS_VERSION);

			// wp_enqueue_script('vendor-main', get_template_directory_uri() . '/assets/js/vendor.min.js', array('jquery'), '1.0',true);
			// wp_enqueue_script('main', get_template_directory_uri() . '/dist/scripts.js', array('jquery'), ASSETS_VERSION, true);
            
			// Only uncomment this if theme needs to have comments enabled
			if (is_singular() && comments_open() && get_option('thread_comments')) {
				wp_enqueue_script('comment-reply');
			}

            // disable Gutenberg CSS on frontend
            wp_dequeue_style( 'wp-block-library' );
            wp_dequeue_style( 'wp-block-library-theme' );
            wp_dequeue_style( 'wc-blocks-style' ); // Remove WooCommerce block CSS
            wp_dequeue_style( 'classic-theme-styles' );

            // remove plugins css from head
            wp_dequeue_style( 'formidable' );
            wp_dequeue_style( 'woocommerce-layout' );
            wp_dequeue_style( 'woocommerce-smallscreen' );
            wp_dequeue_style( 'woocommerce-general' );
		}

	endif;

	if ( !function_exists('split_scripts_and_styles_footer') ) :

		function split_scripts_and_styles_footer() {
            wp_enqueue_style( 'formidable' );
            wp_enqueue_style( 'woocommerce-layout' );
            wp_enqueue_style( 'woocommerce-smallscreen' );
            wp_enqueue_style( 'woocommerce-general' );
		}

	endif;

endif;

if ( !function_exists('split_formidable_customize_html') ) :

    function split_formidable_customize_html( $opt, $key, $field ){
		
		if( !is_admin() ){
			
			if( $field['type'] === 'radio' || $field['type'] === 'checkbox'){

				if( strpos($opt, '<span><span></span><span>' ) <= 0 )
					return "<span><span></span><span>$opt</span></span>";
			}

		} else {
			$current_page = get_current_screen();

			if( $current_page->id != 'toplevel_page_formidable' ){
				if( $field['type'] === 'radio' || $field['type'] === 'checkbox'){

					if( strpos($opt, '<span><span></span><span>' ) <= 0 )
						return "<span><span></span><span>$opt</span></span>";
				}
			}
		}
		
		return $opt;
    }

endif;

if ( !function_exists('split_load_admin_style') ) :

	function split_load_admin_style() {

        if( WEBSITE_TYPE == 1 ):
            // load frontend css to stylize blocks inside dashboard
            global $pagenow;
            if( $pagenow == 'post.php' && ( get_post_type() == 'post' || get_post_type() == 'page' )):
                if( isset($_GET['post']) && $_GET['post'] ):

                    $src_path = get_template_directory().'/dist/css/critical/'.$_GET['post'].'.css';
                    $src_url = get_stylesheet_directory_uri().'/dist/css/critical/'.$_GET['post'].'.css';

                    if( file_exists($src_path) ):
                        wp_enqueue_style( 'lf-css-c', $src_url, false, ASSETS_VERSION );
                    endif;

                    $src_path = get_template_directory().'/dist/css/regular/'.$_GET['post'].'.css';
                    $src_url = get_stylesheet_directory_uri().'/dist/css/regular/'.$_GET['post'].'.css';

                    if( file_exists($src_path) ):
                        wp_enqueue_style( 'lf-css-r', $src_url, false, ASSETS_VERSION );
                    endif;

                endif;
                
            endif;

        endif;

		// wp_register_style( 'admin_css', get_stylesheet_directory_uri() . '/assets/admin/css/style.css', false, ASSETS_VERSION );
		// wp_enqueue_style( 'admin_css', get_stylesheet_directory_uri() . '/assets/admin/css/style.css', false, ASSETS_VERSION );
	}

endif;

// add notice in dashboard
function split_add_dashboard_notice(){
    if( !function_exists('acf_register_block_type') ):
         echo '<div class="notice notice-error">
             <p style="font-size:32px;"><b>Advanced Custom Fields PRO</b> plugin is required.</p>
         </div>';
    endif;

    if( IN_DEVELOPMENT ):
        echo '<div class="notice notice-warning">
            <p>Notice: <b>Development mode is enabled.</b></p>
        </div>';
    endif;
}
add_action('admin_notices', 'split_add_dashboard_notice');

// change login logo href 
function split_login_logo_href($url) {
    return home_url( '/' );
}
add_filter( 'login_headerurl', 'split_login_logo_href' );

?>