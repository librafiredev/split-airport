<?php 
/*
* Block Name: Text Links
* Post Type: page 
*/

if( isset( $block['data']['preview_image_help'] )  ) :
    echo '<img src="'. $block['data']['preview_image_help'] .'" style="width:100%; height:auto;">';
else: ?>

    <?php 
    
        $title = get_field('title');
        $text = get_field('text');

        $image = get_field('image');
        $image_mobile = get_field('image_mobile');

        if ( empty($image_mobile) ) {
            $image_mobile = $image;
        }

        $has_left_items = count(get_field('items_left') ?: []) > 0 ? true : false;
    
    ?>

    <section class="text-links-wrapper <?php echo $has_left_items ? "text-links-has-left" : "text-links-no-left" ?>" style="background-image: url(<?php echo get_template_directory_uri() ?>/assets/images/dots-pattern.svg);">
        <div class="text-links-top">
            <div class="container">
                <div class="text-links-top-inner">

                    <div class="<?php echo $has_left_items ? 'text-links-full' : 'text-links-left'; ?>">

                        <?php if( $title ): ?>

                            <div class="text-links-title">
                                <h2>
                                    <?php echo $title; ?>
                                </h2>
                            </div><!-- .text-links-title -->
                            
                        <?php endif; ?>

                        <?php if( $text ): ?>

                            <div class="text-links-text">
                                <p>
                                    <?php echo $text; ?>
                                </p>
                            </div><!-- .text-links-text -->
                            
                        <?php endif; ?>

                    </div>

                    <?php if( have_rows('items_left') ): ?>
                        <div class="text-links-left-items">
                            <?php while ( have_rows('items_left') ) : the_row(); 
                                $icon = get_sub_field('icon');
                                    $link = get_sub_field('link');
                                ?>
                                <?php if( $link ):
                                    $link_url = $link['url'];
                                    $link_title = $link['title'];
                                    $link_target = $link['target'] ? $link['target'] : '_self';
                                ?>
                                    <div class="text-links-item">
                                        <a href="<?php echo esc_url($link_url); ?>" target="<?php echo esc_attr($link_target); ?>">
                                            
                                            <?php if( !empty($icon) ): ?>
                                                <div class="text-links-item-icon">
                                                    <?php echo ( isset($icon['ID']) )? wp_get_attachment_image($icon['ID'], 'full'):''; ?>
                                                </div>
                                            <?php endif; ?>
                                            <div class="text-links-item-text">
                                                <span><?php echo esc_html($link_title); ?></span>
                                            </div><!-- .text-links-item-text -->
                                            <div class="text-links-item-arrow">
                                                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/arrow.svg" alt="arrow" />
                                            </div><!-- .text-links-item-arrow -->
                                        </a>
                                    </div><!-- .text-links-item -->
                                <?php endif; ?>
                            <?php endwhile; ?>
                        </div>
                    <?php endif; ?>

                    <?php if( have_rows('items') ): ?>

                        <div class="text-links-right">

                            <div class="text-links-items">
                                <?php while ( have_rows('items') ) : the_row();
                                    $icon = get_sub_field('icon');
                                    $link = get_sub_field('link');
                                ?>
                                <?php if( $link ):
                                    $link_url = $link['url'];
                                    $link_title = $link['title'];
                                    $link_target = $link['target'] ? $link['target'] : '_self';
                                ?>
                                        <div class="text-links-item">
                                            <a href="<?php echo esc_url($link_url); ?>" target="<?php echo esc_attr($link_target); ?>">
                                                
                                                <?php if( !empty($icon) ): ?>
                                                    <div class="text-links-item-icon">
                                                        <?php echo ( isset($icon['ID']) )? wp_get_attachment_image($icon['ID'], 'full'):''; ?>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="text-links-item-text">
                                                    <span><?php echo esc_html($link_title); ?></span>
                                                </div><!-- .text-links-item-text -->
                                                <div class="text-links-item-arrow">
                                                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/arrow.svg" alt="arrow" />
                                                </div><!-- .text-links-item-arrow -->
                                            </a>
                                        </div><!-- .text-links-item -->
                                    <?php endif; ?>
                                <?php endwhile; ?>
                            </div>

                        </div><!-- .text-links-items -->

                    <?php endif; ?>

                </div><!-- .text-links-top-inner -->
            </div><!-- .container -->
        </div><!-- .text-links-top -->

        <?php if( !empty($image) && !empty($image_mobile) ): ?>
        
            <div class="text-links-image">
                <picture>
                    <source srcset="<?php echo $image_mobile['url']; ?>" media="(max-width: 767px)">
                    <img src="<?php echo $image['url']; ?>">
                </picture>
            </div>
        
        <?php endif; ?>

        

    </section><!-- .text-links-wrapper -->
    
<?php endif; ?>