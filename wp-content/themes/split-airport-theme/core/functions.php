<?php

if ( ! function_exists( 'split_posted_on' ) ) :
	/**
	 * Prints HTML with meta information for the current post-date/time and author.
	 */
	function split_posted_on() {
		$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
		if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
			$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
		}

		$time_string = sprintf( $time_string,
			esc_attr( get_the_date( 'c' ) ),
			esc_html( get_the_date() ),
			esc_attr( get_the_modified_date( 'c' ) ),
			esc_html( get_the_modified_date() )
		);

		$posted_on = sprintf(
			esc_html_x( 'Posted on %s', 'post date', 'split' ),
			'<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . $time_string . '</a>'
		);

		$byline = sprintf(
			esc_html_x( 'by %s', 'post author', 'split' ),
			'<span class="author vcard"><a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author() ) . '</a></span>'
		);

		echo '<span class="posted-on">' . $posted_on . '</span><span class="byline"> ' . $byline . '</span>'; // WPCS: XSS OK.

	}
endif;

if ( ! function_exists( 'split_entry_footer' ) ) :
	/**
	 * Prints HTML with meta information for the categories, tags and comments.
	 */
	function split_entry_footer() {
		// Hide category and tag text for pages.
		if ( 'post' == get_post_type() ) {
			/* translators: used between list items, there is a space after the comma */
			$categories_list = get_the_category_list( esc_html__( ', ', 'split' ) );
			if ( $categories_list ) {
				printf( '<span class="cat-links">' . esc_html__( 'Posted in %1$s', 'split' ) . '</span>', $categories_list ); // WPCS: XSS OK.
			}

			/* translators: used between list items, there is a space after the comma */
			$tags_list = get_the_tag_list( '', esc_html__( ', ', 'split' ) );
			if ( $tags_list ) {
				printf( '<span class="tags-links">' . esc_html__( 'Tagged %1$s', 'split' ) . '</span>', $tags_list ); // WPCS: XSS OK.
			}
		}

		if ( ! is_single() && ! post_password_required() && ( comments_open() || get_comments_number() ) ) {
			echo '<span class="comments-link">';
			comments_popup_link( esc_html__( 'Leave a comment', 'split' ), esc_html__( '1 Comment', 'split' ), esc_html__( '% Comments', 'split' ) );
			echo '</span>';
		}

		edit_post_link( esc_html__( 'Edit', 'split' ), '<span class="edit-link">', '</span>' );
	}
endif;

if ( ! function_exists( 'split_post_navigation' ) ) :
	/**
	 * Lf Post Navigation
	 */
	function split_post_navigation($custom_query = false) {
		?>
		<div class="nav-links">
		<?php
		global $wp_query;

        if( $custom_query ):
            $wp_query = $custom_query;
        endif;

		$big = 999999999; // need an unlikely integer

		echo paginate_links( array(
				'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
				'format' => '?paged=%#%',
				'current' => max( 1, get_query_var('paged') ),
				'total' => $wp_query->max_num_pages,
				'prev_text'          => __('<i class="icon-angle-left" aria-hidden="true"></i>'),
				'next_text'          => __('<i class="icon-angle-right" aria-hidden="true"></i>')
		) );
		?>
	</div>
	<?php 
	}
endif;

if ( ! function_exists( 'split_share_links' ) ) :
	/**
	 * Lf Share links
	 **/

	function split_share_links() {
		?>
		<div class="share-buttons">
			<div class="share-links-wrapper">
				<!-- Facebook -->
				<a href="https://www.facebook.com/sharer/sharer.php?u=<?php the_permalink(); ?>" title="Share on Facebook" target="_blank" class="btn btn-facebook">
					<i class="fa fa-facebook-square transition-all-05"></i>
				</a>
				<!-- Twitter -->
				<a href="http://twitter.com/home?status=<?php the_permalink(); ?>" title="Share on Twitter" target="_blank" class="btn btn-twitter">
					<i class="fa fa-twitter-square transition-all-05"></i>
				</a>
				<!-- LinkedIn -->
				<a href="http://www.linkedin.com/shareArticle?mini=true&url=&title=&summary=<?php the_permalink(); ?>" title="Share on LinkedIn" target="_blank" class="btn btn-linkedin">
					<i class="fa fa-linkedin-square transition-all-05"></i>
				</a>
				<!-- Pinterest -->
				<a href="http://pinterest.com/pin/create/button/?url=<?php the_permalink(); ?>]&description=<?php the_title(); ?>" class="btn btn-pinterest" target="_blank" title="Share on Pinterest">
					<i class="fa fa-pinterest-square transition-all-05"></i>
				</a>
			</div>
		</div>
		<?php
	}
endif;

if ( ! function_exists( 'split_archive_title' ) ) :
	/**
	 * @param string $before_title
	 * @param string $after_title
	 * @return string
	 */
	function split_archive_title( $before_title = "", $after_title = "" ) {

		if ( is_category() ) {
			$title = $before_title . single_cat_title( '', false ) . $after_title;
		} elseif ( is_tag() ) {
			$title = $before_title . single_tag_title( '', false ) . $after_title;
		} elseif ( is_author() ) {
			$title = $before_title . get_the_author() . $after_title;
		} elseif ( is_year() ) {
			$title = $before_title . get_the_date( _x( 'Y', 'yearly archives date format' ) )  . $after_title;
		} elseif ( is_month() ) {
			$title = $before_title . get_the_date( _x( 'F Y', 'monthly archives date format' ) )  . $after_title;
		} elseif ( is_day() ) {
			$title = $before_title . get_the_date( _x( 'F j, Y', 'daily archives date format' ) )  . $after_title;
		} elseif( is_tax() ){
			$title = $before_title . single_term_title('', false) . $after_title;
		} elseif ( is_post_type_archive() ) {
			$title = $before_title . post_type_archive_title( '', false )  . $after_title;
		} elseif( is_single() ) {
			$title = $before_title . get_the_title() . $after_title;
		} else{
			$title = $before_title . '' . $after_title;
		}

		return $title;

	}
endif;

if ( ! function_exists( 'get_footer_widget_class' ) ) :

	function get_footer_widget_class( $option ){

		$classes = explode( ',', $option );
		$orgclasses = array();

		foreach ( $classes as $class ) {
			$exploded = explode( '/', $class );

			if( isset( $exploded[1] ) && intval( $exploded[1] ) != 0 ){
				$orgclasses[] = 12 / intval( $exploded[1] );
			} else {
				$orgclasses[] = 12;
			}
		}
		return $orgclasses;
	}

endif;

if ( ! function_exists( 'the_social_links' ) ) :

	/**
	 * Return or echo social network icons
	 *
	 * @param boolean $echo
	 * @return void
	 */
	function the_social_links( $echo = false ){

		if( function_exists('get_field') ):
		
			$return_html = '<div class="lf-social-wrapper">';
			
			/*------- Variables ------*/
			$facebook_icon = esc_url(get_field('social_facebook_custom_icon', 'option'));
			$facebook_url = esc_url(get_field('social_facebook_url', 'option'));
			$twitter_icon = esc_url(get_field('social_twitter_custom_icon', 'option'));
			$twitter_url = esc_url(get_field('social_twitter_url', 'option'));
			$youtube_icon = esc_url(get_field('social_youtube_custom_icon', 'option'));
			$youtube_url = esc_url(get_field('social_youtube_url', 'option'));
			$linkedIn_icon = esc_url(get_field('social_linkedin_custom_icon', 'option'));
			$linkedIn_url = esc_url(get_field('social_linkedin_url', 'option'));
			$instagram_icon = esc_url(get_field('social_instagram_custom_icon', 'option'));
			$instagram_url = esc_url(get_field('social_instagram_url', 'option'));
			$pinterest_icon = esc_url(get_field('social_pinterest_custom_icon', 'option'));
			$pinterest_url = esc_url(get_field('social_pinterest_url', 'option'));

			/*---------------Icon Checker -------------------*/
			if($facebook_icon!=''): $fb_icon = '<img src='.$facebook_icon.' alt="Facebook">'; else: $fb_icon = '<i class="icon icon-facebook"></i>';endif;
			if($twitter_icon!=''): $tw_icon ='<img src='.$twitter_icon.' alt="Twitter">'; else: $tw_icon = '<i class="icon icon-twitter"></i>';endif;
			if($youtube_icon!=''): $you_icon = '<img src='.$youtube_icon.' alt="YouTube">'; else: $you_icon = '<i class="icon icon-youtube"></i>';endif;
			if($linkedIn_icon!=''): $li_icon = '<img src='.$linkedIn_icon.' alt="LinkedIn">'; else: $li_icon = '<i class="icon icon-linkedin"></i>';endif;
			if($instagram_icon!=''): $inst_icon = '<img src='.$instagram_icon.' alt="Instagram">'; else: $inst_icon ='<i class="icon icon-instagram"></i>';endif;
			if($pinterest_icon!=''): $pt_icon = '<img src='.$pinterest_icon.' alt="Pinterest">'; else: $pt_icon = '<i class="icon icon-pinterest"></i>';endif;

			if($facebook_url!=''){
				$return_html .= '<div class="social-icon-menu-items facebook">';
				$return_html .= '<a href="'.$facebook_url.'" target="_blank">'.$fb_icon.'</a>';
				$return_html .= '</div>';
			}

			if($instagram_url!=''){
				$return_html .= '<div class="social-icon-menu-items instagram">';
				$return_html .= '<a href="'.$instagram_url.'" target="_blank">'.$inst_icon.'</a>';
				$return_html .= '</div>';
			}

			if($twitter_url!=''){
				$return_html .= '<div class="social-icon-menu-items twitter">';
				$return_html .= '<a href="'.$twitter_url.'" target="_blank">'.$tw_icon.'</a>';
				$return_html .= '</div>';
			}

			if($youtube_url!=''){
				$return_html .= '<div class="social-icon-menu-items youtube">';
				$return_html .= '<a href="'.$youtube_url.'" target="_blank">'.$you_icon.'</a>';
				$return_html .= '</div>';
			}

			if($linkedIn_url!=''){
				$return_html .= '<div class="social-icon-menu-items linkedin">';
				$return_html .= '<a href="'.$linkedIn_url.'" target="_blank">'.$li_icon.'</a>';
				$return_html .= '</div>';
			}

			if($pinterest_url!=''){
				$return_html .= '<div class="social-icon-menu-items pinterest">';
				$return_html .= '<a href="'.$pinterest_url.'" target="_blank">'.$pt_icon.'</a>';
				$return_html .= '</div>';
			}

			$return_html .= '</div>';

			if( $echo )
				echo $return_html;
			else
				return $return_html;

		endif;
	}

endif;



/**
 * Create test page on theme activation
 */
if (isset($_GET['activated']) && is_admin()){

    $new_page_title = 'HTML Markup Test page';

	ob_start();
	
	get_template_part('core/data/test', 'data');
	
	$new_page_content = ob_get_contents();
	
    ob_end_clean();

    $new_page_template = ''; //ex. template-custom.php. Leave blank if you don't want a custom page template.
	//don't change the code bellow, unless you know what you're doing
	
    $page_check = get_page_by_title($new_page_title);

    $new_page = array(
        'post_type' => 'page',
        'post_title' => $new_page_title,
        'post_content' => $new_page_content,
        'post_status' => 'draft',
        'post_author' => 1,
	);
	
    if(!isset($page_check->ID)){
        $new_page_id = wp_insert_post($new_page);
        if(!empty($new_page_template)){
            update_post_meta($new_page_id, '_wp_page_template', $new_page_template);
        }
    }
}

/**
 * ACF Link
 */

if ( ! function_exists( 'lf_acf_link' ) ) :

	function lf_acf_link($link, $class) {
		if ( $link ) :
			$link_url = $link['url'];
			$link_title = $link['title'];
			$link_target = ( $link['target'] != '' ) ? 'target="' . $link['target'] . '"' : '';
		
			return '<a href="' . $link_url .'" class="' . $class . '"' . $link_target . '>' . $link_title . '</a>';
		endif;
	}

endif;

// ACF Theme options
if( function_exists('acf_add_options_page') ) {
    acf_add_options_page(
        array(
            'page_title'    => __('Site options', 'split'),
            'menu_title'    => __('Site options', 'split'),
            'menu_slug'     => 'acf-options',
            'capability'    => 'manage_options',
            'redirect'      => false
        )
    );
}

// remove customizer options
function split_customizer_changes( $wp_customize ) {
    $wp_customize->remove_panel("widgets");
    $wp_customize->remove_section( 'header_image');
	$wp_customize->remove_section( 'title_tagline');
    $wp_customize->remove_panel( 'nav_menus');
    $wp_customize->remove_section( 'static_front_page');
}
add_action( "customize_register", "split_customizer_changes", 50 );

// load favicon from theme options
function add_my_favicon() {
	if( function_exists('get_field') ):
    	echo '<link rel="shortcut icon" href="' . esc_url( get_field('favicon','option') ) . '" />';
	endif;
}
add_action( 'wp_head', 'add_my_favicon' ); //front end
add_action( 'admin_head', 'add_my_favicon' ); //admin end

// update blogname from theme options
function update_blogname_from_theme_options() {
	if( function_exists('get_field') ):
		$screen = get_current_screen();

		if( $screen->id == 'toplevel_page_acf-options' ):
			
			$site_title = get_field('site_title','option');

			if( $site_title ):
				update_site_option( 'blogname', $site_title );
			endif;

		endif;
	endif;
}
add_action('acf/save_post', 'update_blogname_from_theme_options', 20);

// theme options fields
if( function_exists('acf_add_local_field_group') ):

    acf_add_local_field_group(array(
        'key' => 'group_62c558b7eb471',
        'title' => 'General Options',
        'fields' => array(
            array(
                'key' => 'field_62c56bb4e5a2b',
                'label' => 'Header',
                'name' => '',
                'type' => 'accordion',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'open' => 0,
                'multi_expand' => 0,
                'endpoint' => 0,
            ),
            array(
                'key' => 'field_62c696ff64c46',
                'label' => 'Site title',
                'name' => 'site_title',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
                'maxlength' => '',
            ),
            array(
                'key' => 'field_62c5597ff0b87',
                'label' => 'Logo',
                'name' => 'logo',
                'type' => 'image',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '50',
                    'class' => '',
                    'id' => '',
                ),
                'return_format' => 'url',
                'preview_size' => 'medium',
                'library' => 'all',
                'min_width' => '',
                'min_height' => '',
                'min_size' => '',
                'max_width' => '',
                'max_height' => '',
                'max_size' => '',
                'mime_types' => '',
            ),
            array(
                'key' => 'field_62c6953090d9e',
                'label' => 'Favicon',
                'name' => 'favicon',
                'type' => 'image',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '50',
                    'class' => '',
                    'id' => '',
                ),
                'return_format' => 'url',
                'preview_size' => 'medium',
                'library' => 'all',
                'min_width' => '',
                'min_height' => '',
                'min_size' => '',
                'max_width' => '',
                'max_height' => '',
                'max_size' => '',
                'mime_types' => '',
            ),
            array(
                'key' => 'field_62c56b672ddea',
                'label' => 'Footer',
                'name' => '',
                'type' => 'accordion',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'open' => 0,
                'multi_expand' => 0,
                'endpoint' => 0,
            ),
            array(
                'key' => 'field_62c559d5f0b89',
                'label' => 'Footer Layout',
                'name' => 'footer_layout',
                'type' => 'select',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '50',
                    'class' => '',
                    'id' => '',
                ),
                'choices' => array(
                    '1/1' => '1/1',
                    '1/2, 1/2' => '1/2, 1/2',
                    '1/2, 1/4, 1/4' => '1/2, 1/4, 1/4',
                    '1/4, 1/4, 1/4, 1/4' => '1/4, 1/4, 1/4, 1/4',
                    '1/3, 1/3, 1/3' => '1/3, 1/3, 1/3',
                ),
                'default_value' => '1/4, 1/4, 1/4, 1/4',
                'allow_null' => 0,
                'multiple' => 0,
                'ui' => 0,
                'return_format' => 'value',
                'ajax' => 0,
                'placeholder' => '',
            ),
            array(
                'key' => 'field_62c55a73eac85',
                'label' => 'Footer Column Number',
                'name' => 'footer_column_number',
                'type' => 'select',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '50',
                    'class' => '',
                    'id' => '',
                ),
                'choices' => array(
                    1 => 'One',
                    2 => 'Two',
                    3 => 'Three',
                    4 => 'Four',
                ),
                'default_value' => 4,
                'allow_null' => 0,
                'multiple' => 0,
                'ui' => 0,
                'return_format' => 'value',
                'ajax' => 0,
                'placeholder' => '',
            ),
            array(
                'key' => 'field_62c559a8f0b88',
                'label' => 'Footer Copyright Text',
                'name' => 'footer_copyright_text',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
                'maxlength' => '',
            ),
            array(
                'key' => 'field_62c56b752ddeb',
                'label' => 'Social',
                'name' => '',
                'type' => 'accordion',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'open' => 0,
                'multi_expand' => 0,
                'endpoint' => 0,
            ),
            array(
                'key' => 'field_62c55b37d0911',
                'label' => 'Facebook URL',
                'name' => 'social_facebook_url',
                'type' => 'url',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '50',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'placeholder' => '',
            ),
            array(
                'key' => 'field_62c68d10c04a1',
                'label' => 'Facebook Custom icon',
                'name' => 'social_facebook_custom_icon',
                'type' => 'image',
                'instructions' => '(not required)',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '50',
                    'class' => '',
                    'id' => '',
                ),
                'return_format' => 'url',
                'preview_size' => 'medium',
                'library' => 'all',
                'min_width' => '',
                'min_height' => '',
                'min_size' => '',
                'max_width' => '',
                'max_height' => '',
                'max_size' => '',
                'mime_types' => '',
            ),
            array(
                'key' => 'field_62c55b5e0f2bb',
                'label' => 'Twitter URL',
                'name' => 'social_twitter_url',
                'type' => 'url',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '50',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'placeholder' => '',
            ),
            array(
                'key' => 'field_62c68d7ed613c',
                'label' => 'Twitter Custom icon',
                'name' => 'social_twitter_custom_icon',
                'type' => 'image',
                'instructions' => '(not required)',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '50',
                    'class' => '',
                    'id' => '',
                ),
                'return_format' => 'url',
                'preview_size' => 'medium',
                'library' => 'all',
                'min_width' => '',
                'min_height' => '',
                'min_size' => '',
                'max_width' => '',
                'max_height' => '',
                'max_size' => '',
                'mime_types' => '',
            ),
            array(
                'key' => 'field_62c55b780f2bc',
                'label' => 'Instagram URL',
                'name' => 'social_instagram_url',
                'type' => 'url',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '50',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'placeholder' => '',
            ),
            array(
                'key' => 'field_62c68d94d613d',
                'label' => 'Instagram Custom icon',
                'name' => 'social_instagram_custom_icon',
                'type' => 'image',
                'instructions' => '(not required)',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '50',
                    'class' => '',
                    'id' => '',
                ),
                'return_format' => 'url',
                'preview_size' => 'medium',
                'library' => 'all',
                'min_width' => '',
                'min_height' => '',
                'min_size' => '',
                'max_width' => '',
                'max_height' => '',
                'max_size' => '',
                'mime_types' => '',
            ),
            array(
                'key' => 'field_62c55b880f2bd',
                'label' => 'LinkedIn URL',
                'name' => 'social_linkedin_url',
                'type' => 'url',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '50',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'placeholder' => '',
            ),
            array(
                'key' => 'field_62c68da3d613e',
                'label' => 'LinkedIn Custom icon',
                'name' => 'social_linkedin_custom_icon',
                'type' => 'image',
                'instructions' => '(not required)',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '50',
                    'class' => '',
                    'id' => '',
                ),
                'return_format' => 'url',
                'preview_size' => 'medium',
                'library' => 'all',
                'min_width' => '',
                'min_height' => '',
                'min_size' => '',
                'max_width' => '',
                'max_height' => '',
                'max_size' => '',
                'mime_types' => '',
            ),
            array(
                'key' => 'field_62c55b9a0f2be',
                'label' => 'YouTube URL',
                'name' => 'social_youtube_url',
                'type' => 'url',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '50',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'placeholder' => '',
            ),
            array(
                'key' => 'field_62c68db3d613f',
                'label' => 'YouTube Custom icon',
                'name' => 'social_youtube_custom_icon',
                'type' => 'image',
                'instructions' => '(not required)',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '50',
                    'class' => '',
                    'id' => '',
                ),
                'return_format' => 'url',
                'preview_size' => 'medium',
                'library' => 'all',
                'min_width' => '',
                'min_height' => '',
                'min_size' => '',
                'max_width' => '',
                'max_height' => '',
                'max_size' => '',
                'mime_types' => '',
            ),
            array(
                'key' => 'field_62c568767270e',
                'label' => 'Pinterest URL',
                'name' => 'social_pinterest_url',
                'type' => 'url',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '50',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'placeholder' => '',
            ),
            array(
                'key' => 'field_62c68dc3d6140',
                'label' => 'Pinterest Custom icon',
                'name' => 'social_pinterest_custom_icon',
                'type' => 'image',
                'instructions' => '(not required)',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '50',
                    'class' => '',
                    'id' => '',
                ),
                'return_format' => 'url',
                'preview_size' => 'medium',
                'library' => 'all',
                'min_width' => '',
                'min_height' => '',
                'min_size' => '',
                'max_width' => '',
                'max_height' => '',
                'max_size' => '',
                'mime_types' => '',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'options_page',
                    'operator' => '==',
                    'value' => 'acf-options',
                ),
            ),
        ),
        'menu_order' => 0,
        'position' => 'acf_after_title',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '',
        'show_in_rest' => 0,
    ));
    
endif;

// disable default gutenberg blocks
function split_allowed_block_types() {
    global $pagenow;

    if( $pagenow != 'widgets.php' ):

        global $block_post_type_slugs;
        $post_type = get_post_type();

        global $block_slugs;

        $block_slugs = ( isset($block_post_type_slugs[$post_type]) && $block_post_type_slugs[$post_type] )? $block_post_type_slugs[$post_type]:array();

        if( $post_type == 'post' ):
            $block_slugs[] = 'core/paragraph';
            $block_slugs[] = 'core/heading';
            $block_slugs[] = 'core/list';
            $block_slugs[] = 'core/quote';
            $block_slugs[] = 'core/image';
        endif;

        return $block_slugs;

    endif;
}
add_filter( 'allowed_block_types_all', 'split_allowed_block_types' );

// update assets on save
function update_block_assets($post_ID, $post, $update){
    if( wp_is_post_autosave($post_ID) || ( !has_blocks( $post->post_content ) && $post->post_type != 'page' ) ):
        return;
    endif;

    //if( has_blocks( $post->post_content ) ):
        $blocks = parse_blocks( $post->post_content );

        $counter = 0;
        $critical = $regular = $regular_js = '';
        $block_file_names = array();

        if( !(has_blocks(get_the_content(false, false, $post)) ) || $post->post_type == 'page' ):

            // get header css
            // $src_path = get_template_directory().'/dist/css/parts/critical/header.css';
            // if( file_exists($src_path) ):
            //     $critical .= file_get_contents($src_path);
            // endif;

            // get header js
            $src_path = get_template_directory().'/dist/js/parts/regular/header.min.js';
            if( file_exists($src_path) ):
                $regular_js .= file_get_contents($src_path);
            endif;

            // get global js
            // $src_path = get_template_directory().'/dist/js/parts/regular/global.min.js';
            // if( file_exists($src_path) ):
            //     $regular_js .= file_get_contents($src_path);
            // endif;

            // get global css
            $dist_path = get_template_directory().'/dist/css/parts/regular/global.css';

            if( file_exists($dist_path) ):
                $regular .= file_get_contents($dist_path);
            endif;

            // get header css
            $dist_path = get_template_directory().'/dist/css/parts/regular/header.css';

            if( file_exists($dist_path) ):
                $regular .= file_get_contents($dist_path);
            endif;

        endif;

        if( $blocks ):

            foreach($blocks as $key => $block):

                if( isset($block['blockName']) && $block['blockName'] ):

                    $block_file_name = str_replace('acf/','',$block['blockName']);
                    $block_file_name = str_replace('---','_',$block_file_name);
                    $block_file_name = strpos($block_file_name, 'block-') === 0 ? substr($block_file_name, 6) : $block_file_name;

                    if( !in_array($block_file_name, $block_file_names) ):
                        
                        // css
                        $src_path = get_template_directory().'/dist/css/parts/blocks/'.$block_file_name.'.css';

                        if( file_exists($src_path) ):
                            if( $counter < CRITICAL_CSS_THRESHOLD ):
                                $critical .= file_get_contents($src_path);
                            else:
                                $regular .= file_get_contents($src_path);
                            endif;

                        endif;

                        // js
                        $src_path = get_template_directory().'/dist/js/parts/blocks/'.$block_file_name.'.min.js';

                        if( file_exists($src_path) ):
                            $regular_js .= file_get_contents($src_path);
                        endif;

                        
                        $block_file_names[] = $block_file_name;
                        
                    endif;
                    $counter++;
                endif;
                
            endforeach;

        endif;

        if( !(has_blocks(get_the_content(false, false, $post)) ) || $post->post_type == 'page' ):

            // get footer css
            $src_path = get_template_directory().'/dist/css/parts/critical/footer.css';

            if( file_exists($src_path) ):
                $critical .= file_get_contents($src_path);
            endif;

            $dist_path = get_template_directory().'/dist/css/parts/regular/footer.css';

            if( file_exists($dist_path) ):
                $regular .= file_get_contents($dist_path);
            endif;

        endif;

        // create folders if missing
        $folder_path = get_template_directory().'/dist/';
        if( !file_exists($folder_path) ):
            mkdir($folder_path);
        endif;

        $folder_path = get_template_directory().'/dist/css/';
        if( !file_exists($folder_path) ):
            mkdir($folder_path);
        endif;

        $folder_path = get_template_directory().'/dist/css/critical/';
        if( !file_exists($folder_path) ):
            mkdir($folder_path);
        endif;

        $folder_path = get_template_directory().'/dist/css/regular/';
        if( !file_exists($folder_path) ):
            mkdir($folder_path);
        endif;

        // fix paths for critical css
        // $critical = str_replace('url(../../../','url('.get_template_directory_uri().'/',$critical);
        $critical = str_replace('url(/','url('.get_template_directory_uri().'/',$critical);

        $dist_path = get_template_directory().'/dist/css/critical/'.$post_ID.'.css';
        lf_file_put_contents($dist_path, $critical);

        $dist_path = get_template_directory().'/dist/css/regular/'.$post_ID.'.css';
        lf_file_put_contents($dist_path, $regular);

        if( !(has_blocks(get_the_content(false, false, $post)) ) || $post->post_type == 'page' ):
            
            // js
            // get footer js
            $src_path = get_template_directory().'/dist/js/parts/regular/footer.min.js';
            if( file_exists($src_path) ):
                $regular_js .= file_get_contents($src_path);
            endif;

            // get global js
            // skip blog to prevent duplicates
            if( get_option( 'page_for_posts' ) != $post_ID ):
                $src_path = get_template_directory().'/dist/js/parts/regular/global.min.js';
                if( file_exists($src_path) ):
                    $regular_js .= file_get_contents($src_path);
                endif;
            endif;

        endif;

        // create folders if missing
        $folder_path = get_template_directory().'/dist/js/';
        if( !file_exists($folder_path) ):
            mkdir($folder_path);
        endif;
        
        $folder_path = get_template_directory().'/dist/js/regular/';
        if( !file_exists($folder_path) ):
            mkdir($folder_path);
        endif;

        $dist_path = get_template_directory().'/dist/js/regular/'.$post_ID.'.min.js';
        lf_file_put_contents($dist_path, $regular_js);

    //endif;
}
add_action("save_post", 'update_block_assets', 10, 3);

// update assets on reload dev purposes
function update_block_assets_reload(){
    $regenerate_mode = isset($_GET['regenerate']) && $_GET['regenerate'] == true ? wp_strip_all_tags($_GET['regenerate']) : false;
    $is_dev = IN_DEVELOPMENT;

    if( $is_dev || $regenerate_mode == true ):

        // pages with ID
        if( is_home() ):
            $data = array(
                'ID' => get_option( 'page_for_posts' ),
            );
        else:
            $data = array(
                'ID' => get_the_ID(),
            );
        endif;

        if( isset($data['ID']) && $data['ID'] > 0 ):
            wp_update_post( $data );
        endif;

        // pages without ID
        $regular_dir_path = get_template_directory().'/dist/css/parts/regular/';
        if( file_exists($regular_dir_path) ):
            $files = array_diff(scandir($regular_dir_path), array('.', '..'));

            if( function_exists('is_woocommerce') ):
                $regular_dir_path = get_template_directory().'/dist/css/parts/regular/woo/';

                if( file_exists($regular_dir_path) ):

                    $files_woo = array_diff(scandir($regular_dir_path), array('.', '..'));

                    // add woo/ to file name
                    foreach( $files_woo as $key => $file_woo ):
                        $files_woo[$key] = 'woo/'.$file_woo;
                    endforeach;

                    $files = array_merge($files, $files_woo);

                endif;

            endif;

            if( $files ):

                // get footer css
                // $dist_path = get_template_directory().'/dist/css/parts/regular/footer.css';

                // if( file_exists($dist_path) ):
                //     $footer_css = file_get_contents($dist_path);
                // else:
                //     $footer_css = '';
                // endif;

                foreach( $files as $key => $file ):

                    if( $file != 'footer.css' && $file != 'woo' ):

                        $dist_path = get_template_directory().'/dist/css/parts/regular/'.$file;

                        if( file_exists($dist_path) ):
                            $file_css = file_get_contents($dist_path);

                            // $file_css_arr = explode('/* footer-separator */', $file_css);
                            // $file_css = $file_css_arr[0].'/* footer-separator */'.$footer_css;

                            lf_file_put_contents($dist_path, $file_css);
                        endif;
                    
                    endif;

                endforeach;
            endif;
        endif;

        // fix paths for critical css
        $critical_dir_path = get_template_directory().'/dist/css/parts/critical/';
        if( file_exists($critical_dir_path) ):
            $files = array_diff(scandir($critical_dir_path), array('.', '..'));

            if( function_exists('is_woocommerce') ):
                $regular_dir_path = get_template_directory().'/dist/css/parts/critical/woo/';

                if( file_exists($regular_dir_path) ):

                    $files_woo = array_diff(scandir($regular_dir_path), array('.', '..'));

                    // add woo/ to file name
                    foreach( $files_woo as $key => $file_woo ):
                        $files_woo[$key] = 'woo/'.$file_woo;
                    endforeach;

                    $files = array_merge($files, $files_woo);

                endif;

            endif;

            if( $files ):

                foreach( $files as $key => $file ):
                    
                    $file_path = get_template_directory().'/dist/css/parts/critical/'.$file;

                    if( file_exists($file_path) && str_contains($file_path, '.') ):

                        if( str_contains($file_path, '/woo/') ):
                            $file_contents = file_get_contents($file_path);
                            // $file_contents = str_replace('url(../../../../../','url('.get_template_directory_uri().'/',$file_contents);
                            $file_contents = str_replace('url(/','url('.get_template_directory_uri().'/',$file_contents);
                            lf_file_put_contents($file_path, $file_contents);
                        else:
                            $file_contents = file_get_contents($file_path);
                            // $file_contents = str_replace('url(../../../../','url('.get_template_directory_uri().'/',$file_contents);
                            $file_contents = str_replace('url(/','url('.get_template_directory_uri().'/',$file_contents);
                            lf_file_put_contents($file_path, $file_contents);
                        endif;

                    endif;
                    
                endforeach;

            endif;

        endif;

        // fix image paths for regular css
        $regular_paths = array();

        // all css
        $regular_paths[] = get_template_directory().'/dist/css/all/all.css';
        $regular_paths[] = get_template_directory().'/dist/css/all/critical/all.css';

        // regular parts
        $regular_parts_path = get_template_directory().'/dist/css/parts/regular/';

        if( file_exists($regular_parts_path) ):
            $regular_parts_files = array_diff(scandir($regular_parts_path), array('.', '..'));

            if( $regular_parts_files ):

                foreach( $regular_parts_files as $key => $regular_parts_file ):
                    $regular_paths[] = $regular_parts_path.$regular_parts_file;
                endforeach;
                
            endif;
        endif;

        // woo
        if( function_exists('is_woocommerce') ):

            $regular_woo_path = get_template_directory().'/dist/css/parts/regular/woo/';

            if( file_exists($regular_woo_path) ):
                $regular_parts_files = array_diff(scandir($regular_woo_path), array('.', '..'));
    
                if( $regular_parts_files ):
    
                    foreach( $regular_parts_files as $key => $regular_parts_file ):
                        $regular_paths[] = $regular_woo_path.$regular_parts_file;
                    endforeach;
                    
                endif;
            endif;

        endif;

        // regular
        $regular_path = get_template_directory().'/dist/css/regular/';

        if( file_exists($regular_path) ):
            $regular_parts_files = array_diff(scandir($regular_path), array('.', '..'));

            if( $regular_parts_files ):

                foreach( $regular_parts_files as $key => $regular_parts_file ):
                    $regular_paths[] = $regular_path.$regular_parts_file;
                endforeach;
                
            endif;
        endif;

        // fix paths
        if( $regular_paths ):
            foreach( $regular_paths as $key => $regular_path ):

                if( file_exists($regular_path) && is_file($regular_path) ):

                    $file_contents = file_get_contents($regular_path);
                    $file_contents = str_replace('url(/','url('.get_template_directory_uri().'/',$file_contents);
                    lf_file_put_contents($regular_path, $file_contents);

                endif;

            endforeach;
        endif;

    endif;
}
add_action("wp", 'update_block_assets_reload');

// embed critical css
function embed_critical_css() {

    $queried_object = get_queried_object();
    $queried_object_id = ( isset($queried_object->ID) )? $queried_object->ID:0;

    // if flexible
    if( WEBSITE_TYPE == 0 ):

        $dist_path = get_template_directory().'/dist/css/all/critical/all.css';

        if( file_exists($dist_path) ):
            $critical = file_get_contents($dist_path);
            echo '<style>'.$critical.'</style>';
        endif;

    // if gutenberg
    else:

        $critical = '';

        $ID = get_the_ID();

        // if woocommerce
        if( function_exists('is_woocommerce') && ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) ):

            if( is_shop() ):
                $template_name = 'woo/shop';
            elseif( is_product_category() ):
                $template_name = 'woo/product-category';
            elseif( is_product_tag() ):
                $template_name = 'woo/product-tag';
            elseif( is_product() ):
                $template_name = 'woo/product';
            elseif( is_cart() ):
                $template_name = 'woo/cart';
            elseif( is_checkout() ):
                $template_name = 'woo/checkout';
            elseif( is_account_page()):
                $template_name = 'woo/account-page';
            endif;

            // css
            // get header css
            $src_path = get_template_directory().'/dist/css/parts/critical/header.css';
            if( file_exists($src_path) ):
                $critical .= file_get_contents($src_path);
            endif;

            $dist_path = get_template_directory().'/dist/css/parts/critical/'.$template_name.'.css';

            if( file_exists($dist_path) ):
                $critical .= file_get_contents($dist_path);
                echo '<style>'.$critical.'</style>';
            endif;

        // if page with ID
        elseif( ($ID > 1 && !is_search() && !is_archive() && !is_category() && !is_tag() && !is_tax() && !is_single() && !is_home()) || ($ID > 1 && ((is_single() || is_home()) && has_blocks(get_the_content(false, false,$queried_object_id))) ) ):

            // get ID of blog page
            if( is_home() ):
                $queried_object = get_queried_object();
                $ID = ( isset($queried_object) )? $queried_object->ID:0;
            endif;

            // get header css
            $dist_path = get_template_directory().'/dist/css/parts/critical/header.css';

            if( file_exists($dist_path) ):
                $critical .= file_get_contents($dist_path);
            endif;

            // get global css
            $dist_path = get_template_directory().'/dist/css/parts/critical/global.css';

            if( file_exists($dist_path) ):
                $critical .= file_get_contents($dist_path);
            endif;

            $template_path = get_post_meta($ID, '_wp_page_template', true);

            // if page template
            if( strpos($template_path, 'templates/') !== false ):
                $template_name = str_replace('templates/','',$template_path);
                $template_name = str_replace('.php','',$template_name);

                $dist_path = get_template_directory().'/dist/css/parts/critical/'.$template_name.'.css';

                if( file_exists($dist_path) ):
                    $critical .= file_get_contents($dist_path);
                    echo '<style>'.$critical.'</style>';
                endif;
            else:

                $dist_path = get_template_directory().'/dist/css/critical/'.$ID.'.css';

                if( file_exists($dist_path) ):
                    $critical .= file_get_contents($dist_path);
                endif;

                if( has_blocks(get_the_content(false, false,$queried_object_id)) && (is_single() || is_home()) ):
                    global $template; 
                    $template_name = basename($template);
                    $template_name = str_replace('.php','',$template_name);

                    $dist_path = get_template_directory().'/dist/css/parts/critical/'.$template_name.'.css';

                    if( file_exists($dist_path) ):
                        $critical .= file_get_contents($dist_path);
                    endif;
                endif;

                echo '<style>'.$critical.'</style>';

            endif;

        // if page without ID
        else:

            global $template; 
            $template_name = basename($template);
            $template_name = str_replace('.php','',$template_name);

            // get header css
            $src_path = get_template_directory().'/dist/css/parts/critical/header.css';
            if( file_exists($src_path) ):
                $critical .= file_get_contents($src_path);
            endif;

            $dist_path = get_template_directory().'/dist/css/parts/critical/'.$template_name.'.css';

            if( file_exists($dist_path) ):
                $critical .= file_get_contents($dist_path);
                echo '<style>'.$critical.'</style>';
            endif;
            
        endif;

    endif;
}
add_action( 'wp_head', 'embed_critical_css' );

// embed regular css and js
function embed_regular_css_js() {

    $queried_object = get_queried_object();
    $queried_object_id = ( isset($queried_object->ID) )? $queried_object->ID:0;

    // if flexible
    if( WEBSITE_TYPE == 0 ):

        // css
        $dist_path = get_template_directory().'/dist/css/all/all.css';
        $dist_uri = get_template_directory_uri().'/dist/css/all/all.css';

        if( file_exists($dist_path) ):
            wp_enqueue_style('lf-css-all', $dist_uri, array(), ASSETS_VERSION);
        endif;

        // js
        $dist_path = get_template_directory().'/dist/js/all/all.min.js';
        $dist_uri = get_template_directory_uri().'/dist/js/all/all.min.js';

        if( file_exists($dist_path) ):
            wp_enqueue_script('lf-js-all', $dist_uri, array('jquery'), ASSETS_VERSION, true);
        endif;

    // if gutenberg
    else:

        $ID = get_the_ID();

        // if woocommerce
        if( function_exists('is_woocommerce') && ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) ):
            
            if( is_shop() ):
                $template_name = 'woo/shop';
            elseif( is_product_category() ):
                $template_name = 'woo/product-category';
            elseif( is_product_tag() ):
                $template_name = 'woo/product-tag';
            elseif( is_product() ):
                $template_name = 'woo/product';
            elseif( is_cart() ):
                $template_name = 'woo/cart';
            elseif( is_checkout() ):
                $template_name = 'woo/checkout';
            elseif( is_account_page()):
                $template_name = 'woo/account-page';
            endif;

            // css
            $dist_path = get_template_directory().'/dist/css/parts/regular/'.$template_name.'.css';
            $dist_uri = get_template_directory_uri().'/dist/css/parts/regular/'.$template_name.'.css';

            if( file_exists($dist_path) ):
                wp_enqueue_style('lf-css-'.$template_name, $dist_uri, array(), ASSETS_VERSION);
            endif;

        // if page with ID
        elseif( ($ID > 1 && !is_search() && !is_archive() && !is_category() && !is_tag() && !is_tax() && !is_single() && !is_home()) || ($ID > 1 && ((is_single() || is_home()) && has_blocks(get_the_content(false, false,$queried_object_id))) ) ):

            // get ID of blog page
            if( is_home() ):
                $ID = ( isset(get_queried_object()->ID) )? get_queried_object()->ID:0;
            endif;

            $template_path = get_post_meta($ID, '_wp_page_template', true);

            // if page template
            if( strpos($template_path, 'templates/') !== false ):
                $template_name = str_replace('templates/','',$template_path);
                $template_name = str_replace('.php','',$template_name);

                // css
                $dist_path = get_template_directory().'/dist/css/parts/regular/'.$template_name.'.css';
                $dist_uri = get_template_directory_uri().'/dist/css/parts/regular/'.$template_name.'.css';
    
                if( file_exists($dist_path) ):
                    wp_enqueue_style('lf-css-'.$template_name, $dist_uri, array(), ASSETS_VERSION);
                endif;

            else:
                // css
                $dist_path = get_template_directory().'/dist/css/regular/'.$ID.'.css';
                $dist_uri = get_template_directory_uri().'/dist/css/regular/'.$ID.'.css';
                if( file_exists($dist_path) ):
                    wp_enqueue_style('lf-css-'.$ID, $dist_uri, array(), ASSETS_VERSION);
                endif;

                if( has_blocks(get_the_content(false, false,$queried_object_id)) && (is_single() || is_home()) ):
                    global $template;

                    $template_name = basename($template);
                    $template_name = str_replace('.php','',$template_name);

                    // css
                    $dist_path = get_template_directory().'/dist/css/parts/regular/'.$template_name.'.css';
                    $dist_uri = get_template_directory_uri().'/dist/css/parts/regular/'.$template_name.'.css';

                    if( file_exists($dist_path) ):
                        wp_enqueue_style('lf-css-'.$template_name, $dist_uri, array(), ASSETS_VERSION);
                    endif;
                endif;

            endif;

        // if page without ID
        else:

            global $template;

            $template_name = basename($template);
            $template_name = str_replace('.php','',$template_name);

            // css
            $dist_path = get_template_directory().'/dist/css/parts/regular/'.$template_name.'.css';
            $dist_uri = get_template_directory_uri().'/dist/css/parts/regular/'.$template_name.'.css';

            if( file_exists($dist_path) ):
                wp_enqueue_style('lf-css-'.$template_name, $dist_uri, array(), ASSETS_VERSION);
            endif;

        endif;

        $ID = get_the_ID();

        // if woocommerce
        if( function_exists('is_woocommerce') && ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) ):

            if( is_shop() ):
                $template_name = 'woo/shop';
            elseif( is_product_category() ):
                $template_name = 'woo/product-category';
            elseif( is_product_tag() ):
                $template_name = 'woo/product-tag';
            elseif( is_product() ):
                $template_name = 'woo/product';
            elseif( is_cart() ):
                $template_name = 'woo/cart';
            elseif( is_checkout() ):
                $template_name = 'woo/checkout';
            elseif( is_account_page()):
                $template_name = 'woo/account-page';
            endif;

            // js
            $dist_path = get_template_directory().'/dist/js/parts/regular/'.$template_name.'.min.js';
            $dist_uri = get_template_directory_uri().'/dist/js/parts/regular/'.$template_name.'.min.js';

            if( file_exists($dist_path) ):
                wp_enqueue_script('lf-js-'.$template_name, $dist_uri, array('jquery'), ASSETS_VERSION, true);
            endif;

        // if page with ID
        elseif( ($ID > 1 && !is_search() && !is_archive() && !is_category() && !is_tag() && !is_tax() && !is_single() && !is_home()) || ($ID > 1 && ((is_single() || is_home()) && has_blocks(get_the_content(false, false,$queried_object_id))) ) ):

            // get ID of blog page
            if( is_home() ):
                $ID = ( isset(get_queried_object()->ID) )? get_queried_object()->ID:0;
            endif;

            $template_path = get_post_meta($ID, '_wp_page_template', true);

            // if page template
            if( strpos($template_path, 'templates/') !== false ):
                $template_name = str_replace('templates/','',$template_path);
                $template_name = str_replace('.php','',$template_name);

                // js
                $dist_path = get_template_directory().'/dist/js/parts/regular/'.$template_name.'.min.js';
                $dist_uri = get_template_directory_uri().'/dist/js/parts/regular/'.$template_name.'.min.js';

                if( file_exists($dist_path) ):
                    wp_enqueue_script('lf-js-'.$template_name, $dist_uri, array('jquery'), ASSETS_VERSION, true);
                endif;

            else:
                // js
                $dist_path = get_template_directory().'/dist/js/regular/'.$ID.'.min.js';
                $dist_uri = get_template_directory_uri().'/dist/js/regular/'.$ID.'.min.js';
                if( file_exists($dist_path) ):
                    wp_enqueue_script('lf-js-'.$ID, $dist_uri, array('jquery'), ASSETS_VERSION, true);
                endif;

                if( has_blocks(get_the_content(false, false,$queried_object_id)) && (is_single() || is_home()) ):
                    global $template;

                    $template_name = basename($template);
                    $template_name = str_replace('.php','',$template_name);

                    // js
                    $dist_path = get_template_directory().'/dist/js/parts/regular/'.$template_name.'.min.js';
                    $dist_uri = get_template_directory_uri().'/dist/js/parts/regular/'.$template_name.'.min.js';

                    if( file_exists($dist_path) ):
                        wp_enqueue_script('lf-js-'.$template_name, $dist_uri, array('jquery'), ASSETS_VERSION, true);
                    endif;

                endif;
            endif;

        // if page without ID
        else:

            global $template;

            $template_name = basename($template);
            $template_name = str_replace('.php','',$template_name);

            // js
            $dist_path = get_template_directory().'/dist/js/parts/regular/'.$template_name.'.min.js';
            $dist_uri = get_template_directory_uri().'/dist/js/parts/regular/'.$template_name.'.min.js';

            if( file_exists($dist_path) ):
                wp_enqueue_script('lf-js-'.$template_name, $dist_uri, array('jquery'), ASSETS_VERSION, true);
            endif;

        endif;

    endif;
}
add_action( 'wp_footer', 'embed_regular_css_js');

// create scss and js files from root and template php files
function create_template_css_files() {
    if( current_user_can('administrator') ):

        // files in theme root
        $files = scandir(get_template_directory());

        if( $files ):

            $files[] = 'global.php';

            foreach ($files as $key => $file):

                if( strpos($file, '.php') !== false ):
                    if( strpos($file, 'home.php') !== false || strpos($file, 'index.php') !== false 
                        || strpos($file, 'single') !== false ||strpos($file, 'singular') !== false 
                        || strpos($file, 'category') !== false || strpos($file, 'tag') !== false 
                        || strpos($file, 'archive') !== false || strpos($file, 'taxonomy') !== false 
                        || strpos($file, 'author') !== false || strpos($file, 'date') !== false 
                        || strpos($file, 'search.') !== false || strpos($file, '404') !== false 
                        || strpos($file, 'header') !== false|| strpos($file, 'footer') !== false 
                        || strpos($file, 'global.php') !== false ):

                        $file = str_replace('.php','',$file);

                        // css
                        if( $file != 'footer' ):     
                            
                            // create folders if missing
                            $folder_path = get_template_directory().'/assets/';
                            if( !file_exists($folder_path) ):
                                mkdir($folder_path);
                            endif;

                            $folder_path = get_template_directory().'/assets/scss/';
                            if( !file_exists($folder_path) ):
                                mkdir($folder_path);
                            endif;

                            // $folder_path = get_template_directory().'/assets/scss/critical/';
                            // if( !file_exists($folder_path) ):
                            //     mkdir($folder_path);
                            // endif;

                            // $scss_path = get_template_directory().'/assets/scss/critical/'.$file.'.scss';

                            // if( !file_exists($scss_path) ):
                            //     file_put_contents($scss_path, '');
                            // endif;
                        endif;

                        // if( $file != 'header' ):

                            // create folders if missing
                            $folder_path = get_template_directory().'/assets/scss/';
                            if( !file_exists($folder_path) ):
                                mkdir($folder_path);
                            endif;

                            $scss_path = get_template_directory().'/assets/scss/'.$file.'.scss';

                            if( !file_exists($scss_path) ):
                                // don't add critial in footer
                                if( strpos($file, 'footer') !== false ):
                                    lf_file_put_contents($scss_path, '@import "global/variables";'.PHP_EOL.'@import "global/mixins";'.PHP_EOL.PHP_EOL);   
                                else:
                                    lf_file_put_contents($scss_path, '@import "global/variables";'.PHP_EOL.'@import "global/mixins";'.PHP_EOL.PHP_EOL.'/* critical:start */ '.PHP_EOL.PHP_EOL.'/* critical:end */ ');   
                                endif;
                            endif;
                        // endif;

                        // js
                        // create folders if missing
                        $folder_path = get_template_directory().'/assets/js/';
                        if( !file_exists($folder_path) ):
                            mkdir($folder_path);
                        endif;

                        $folder_path = get_template_directory().'/assets/js/';
                        if( !file_exists($folder_path) ):
                            mkdir($folder_path);
                        endif;

                        $js_path = get_template_directory().'/assets/js/'.$file.'.js';

                        if( !file_exists($js_path) ):
                            lf_file_put_contents($js_path, '$(function() {'.PHP_EOL.PHP_EOL.PHP_EOL.PHP_EOL.'});');
                        endif;

                    endif;
                endif;

            endforeach;
        endif;

        // files in templates folder
        $files = scandir(get_template_directory().'/templates/');

        if( $files ):
            foreach ($files as $key => $file):

                if( strpos($file, '.php') !== false ):

                    $file = str_replace('.php','',$file);

                    // css
                    // $scss_path = get_template_directory().'/assets/scss/critical/'.$file.'.scss';

                    // if( !file_exists($scss_path) ):
                    //     file_put_contents($scss_path, '');
                    // endif;

                    $scss_path = get_template_directory().'/assets/scss/'.$file.'.scss';

                    if( !file_exists($scss_path) ):
                        lf_file_put_contents($scss_path, '@import "global/variables";'.PHP_EOL.'@import "global/mixins";'.PHP_EOL.PHP_EOL.'/* critical:start */ '.PHP_EOL.PHP_EOL.'/* critical:end */ ');
                    endif;

                    // js
                    $js_path = get_template_directory().'/assets/js/'.$file.'.js';

                    if( !file_exists($js_path) ):
                        lf_file_put_contents($js_path, '$(function() {'.PHP_EOL.PHP_EOL.PHP_EOL.PHP_EOL.'});');
                    endif;

                endif;

            endforeach;
        endif;

        // woocommerce files
        if( function_exists('is_woocommerce') ):

            $files = array('woo/shop','woo/product-category','woo/product-tag','woo/product','woo/cart','woo/checkout','woo/account-page');

            // create folders if missing
            // $folder_path = get_template_directory().'/assets/scss/critical/woo/';
            // if( !file_exists($folder_path) ):
            //     mkdir($folder_path);
            // endif;

            $folder_path = get_template_directory().'/assets/scss/woo/';
            if( !file_exists($folder_path) ):
                mkdir($folder_path);
            endif;

            $folder_path = get_template_directory().'/assets/js/woo/';
            if( !file_exists($folder_path) ):
                mkdir($folder_path);
            endif;

            if( $files ):
                foreach ($files as $key => $file):
    
                    // css
                    // $scss_path = get_template_directory().'/assets/scss/critical/'.$file.'.scss';

                    // if( !file_exists($scss_path) ):
                    //     file_put_contents($scss_path, '');
                    // endif;

                    $scss_path = get_template_directory().'/assets/scss/'.$file.'.scss';

                    if( !file_exists($scss_path) ):
                        lf_file_put_contents($scss_path, '@import "../global/variables";'.PHP_EOL.'@import "../global/mixins";'.PHP_EOL.PHP_EOL.'/* critical:start */ '.PHP_EOL.PHP_EOL.'/* critical:end */ ');
                    endif;

                    // js
                    $js_path = get_template_directory().'/assets/js/'.$file.'.js';

                    if( !file_exists($js_path) ):
                        lf_file_put_contents($js_path, '$(function() {'.PHP_EOL.PHP_EOL.PHP_EOL.PHP_EOL.'});');
                    endif;
    
                endforeach;
            endif;
            
        endif;

    endif;
}
add_action( 'init', 'create_template_css_files');

function lf_file_put_contents($filename, $data){
    file_put_contents($filename, $data);
    chmod($filename, 0664);
}

// year shortcode
function year_shortcode( $atts ){
	return date("Y");
}
add_shortcode( 'year', 'year_shortcode' );