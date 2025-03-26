<?php

function create_blocks_init() {

    // Check function exists.
    if( function_exists('acf_register_block_type') ):

        global $block_slugs;
        $block_slugs = array();

        global $block_post_type_slugs;
        $block_post_type_slugs = array();

        $block_path = get_template_directory() . '/blocks/';

        if( file_exists($block_path) ):
            
            $block_files = array_diff(scandir($block_path), array('.', '..'));

            if( $block_files ):

                foreach( $block_files as $key => $block_file ):

                    $block_file_name = $block_file_title = $block_file;
                    $block_file_slug = str_replace('_','---',$block_file_name);
                    $block_file_slug = 'block-'.$block_file_slug;
                    // $block_file_ext = pathinfo($block_file, PATHINFO_EXTENSION);

                    if( file_exists($block_path.$block_file.'/'.$block_file.'.php') ):
                    // if( $block_file_ext == 'php' ):

                        $block_file_data = get_file_data($block_path.$block_file.'/'.$block_file.'.php', array('block_name' => 'Block Name', 'post_type' => 'Post Type'));

                        if( isset($block_file_data['block_name']) && $block_file_data['block_name'] ):
                            $block_file_title = $block_file_data['block_name'];
                        endif;

                        if( current_user_can('administrator') ):

                            // attach block to post type
                            if( isset($block_file_data['post_type']) && $block_file_data['post_type'] ):
                                $block_post_type = $block_file_data['post_type'];

                                $block_post_type_arr = explode(',', $block_post_type);

                                if( $block_post_type_arr ):
                                    foreach ($block_post_type_arr as $key => $block_post_type_item):
                                        $block_post_type_slugs[trim($block_post_type_item)][] = 'acf/'.$block_file_slug;
                                    endforeach;
                                endif;
                            endif;

                            $block_slugs[] = 'acf/'.$block_file_slug;

                            // create asset files
                            // if( current_user_can('administrator') ):

                                // create folders if missing
                                $folder_path = get_template_directory().'/assets/';
                                if( !file_exists($folder_path) ):
                                    mkdir($folder_path);
                                endif;

                                $folder_path = get_template_directory().'/assets/scss/';
                                if( !file_exists($folder_path) ):
                                    mkdir($folder_path);
                                endif;

                                // $folder_path = get_template_directory().'/assets/scss/blocks/';
                                // if( !file_exists($folder_path) ):
                                //     mkdir($folder_path);
                                // endif;

                                // $scss_path = get_template_directory().'/assets/scss/blocks/'.$block_file_name.'.scss';

                                // if( !file_exists($scss_path) ):
                                //     file_put_contents($scss_path, '');
                                // endif;

                                // create folders if missing
                                $folder_path = get_template_directory().'/assets/js/';
                                if( !file_exists($folder_path) ):
                                    mkdir($folder_path);
                                endif;

                                // $folder_path = get_template_directory().'/assets/js/blocks/';
                                // if( !file_exists($folder_path) ):
                                //     mkdir($folder_path);
                                // endif;

                                // $js_path = get_template_directory().'/assets/js/blocks/'.$block_file_name.'.js';

                                // if( !file_exists($js_path) ):
                                //     file_put_contents($js_path, '');
                                // endif;
                            // endif;

                        endif;

                        // register a block.
                        $block_args = array(
                            'name'              => $block_file_slug,
                            'title'             => $block_file_title,
                            'render_template'   => 'blocks/'.$block_file_name.'/'.$block_file_name.'.php',
                            'category'          => 'custom',
                            'icon'              => 'block-default',
                            'keywords'          => array($block_file_title),
                            'mode'              => 'edit'
                        );

                        $preview_path = get_template_directory().'/blocks/'.$block_file_name.'/'.$block_file_name.'.png';

                        if( file_exists($preview_path) ):
                            $block_args['example'] = array(
                                'attributes' => array(
                                    'mode' => 'preview',
                                    'data' => array(
                                        'preview_image_help' => get_template_directory_uri().'/blocks/'.$block_file_name.'/'.$block_file_name.'.png',
                                    )
                                )
                            );
                        endif;

                        acf_register_block_type($block_args);

                        if( current_user_can('administrator') ):
                            
                            // create block field group 
                            $group = get_page_by_title('Block: '.$block_file_title, 'OBJECT', 'acf-field-group');

                            if( !$group ):

                                $group_content = array(
                                    'location' => array(
                                        '0' => array(
                                            '0' => array(
                                                'param' => 'block',
                                                'operator' => '==',
                                                'value' => 'acf/'.$block_file_slug
                                            )
                                        )
                                    ),
                                    'position' => 'normal',
                                    'style' => 'default',
                                    'label_placement' => 'top',
                                    'instruction_placement' => 'label',
                                    'hide_on_screen' => '',
                                    'description' => '',
                                    'show_in_rest' => 0
                                );
                                
                                $group_args = array(
                                    'post_date' => current_datetime()->format('Y-m-d H:i:s'),
                                    'post_content' => serialize($group_content),
                                    'post_title' => 'Block: '.$block_file_title,
                                    'post_excerpt' => $block_file_slug,
                                    'post_status' => 'publish',
                                    'comment_status' => 'closed',
                                    'post_name' => 'group_' . uniqid(),
                                    'post_type' => 'acf-field-group'
                                );

                                wp_insert_post( $group_args );
                                
                            endif;

                        endif;

                    // endif;
                    endif;

                endforeach;
                
            endif;

        endif;

        if( current_user_can('administrator') ):
            
            // delete field groups of deleted blocks
            $group_args = array(
                'post_type' => 'acf-field-group',
                'posts_per_page' => -1
            );
            
            $group_posts = get_posts( $group_args);
            
            if( $group_posts && is_array($block_slugs) ):
            
                foreach ($group_posts as $key => $group_post):
            
                    $post_content_arr = unserialize($group_post->post_content);
            
                    if( substr( $group_post->post_title, 0, 7 ) === "Block: " && isset($post_content_arr['location'][0][0]['value']) ):
            
                        if( !in_array($post_content_arr['location'][0][0]['value'],$block_slugs) ):
            
                            wp_trash_post( $group_post->ID );
                            
                        endif;
            
                    endif;
                    
                endforeach;
                
            endif;

        endif;

    endif;
}
add_action('acf/init','create_blocks_init');