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

        if( wp_is_mobile() ):
            $image = get_field('image_mobile');
        else:
            $image = get_field('image');
        endif;
    
    ?>

    <section class="text-links-wrapper" style="background-image: url(<?php echo get_template_directory_uri() ?>/assets/images/dots-pattern.svg);">
        <div class="text-links-top">
            <div class="container">
                <div class="text-links-top-inner">

                    <div class="text-links-left">

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

                    </div><!-- .text-links-left -->

                    <?php if( have_rows('items') ): ?>

                        <div class="text-links-right">

                            <div class="text-links-items">
                                <?php while ( have_rows('items') ) : the_row();
                                
                                    $link = get_sub_field('link');
                                ?>
                                <?php if( $link ):
                                    $link_url = $link['url'];
                                    $link_title = $link['title'];
                                    $link_target = $link['target'] ? $link['target'] : '_self';
                                ?>
                                        <div class="text-links-item">
                                            <a href="<?php echo esc_url($link_url); ?>" target="<?php echo esc_attr($link_target); ?>">
                                                <div class="text-links-item-icon">
                                                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/info.svg" alt="info" />
                                                </div><!-- .text-links-item-icon -->
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

        <?php if( !empty($image) ): ?>
        
            <div class="text-links-image">
                <?php echo ( isset($image['ID']) )? wp_get_attachment_image($image['ID'], 'full'):''; ?>
            </div><!-- .text-links-image -->
        
        <?php endif; ?>

    </section><!-- .text-links-wrapper -->
    
<?php endif; ?>