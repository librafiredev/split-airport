<?php 
/*
* Block Name: Text Cards
* Post Type: page 
*/

if( isset( $block['data']['preview_image_help'] )  ) :
    echo '<img src="'. $block['data']['preview_image_help'] .'" style="width:100%; height:auto;">';
else: ?>

    <?php 

        $title = get_field('title');
        $text = get_field('text');

    ?>

    <section class="text-cards-wrapper" style="background-image: url(<?php echo get_template_directory_uri() ?>/assets/images/dots-pattern.svg);">

        <div class="container">
            <div class="text-cards-inner">
                <img src="<?php echo get_template_directory_uri() ?>/assets/images/air-line.svg" class="text-cards-air-line" />

                <div class="text-cards-top">

                    <div class="text-cards-top-item">
                        <?php if( $title ): ?>
                            <div class="text-cards-title">
                                <h2>
                                    <?php echo $title; ?>
                                </h2>
                            </div><!-- .text-cards-title -->
                        
                        <?php endif; ?>
                        <?php if( $text ): ?>
                            <div class="text-cards-text">
                                <p>
                                    <?php echo $text; ?>
                                </p>
                            </div><!-- .text-cards-text -->
                        
                        <?php endif; ?>
                    </div>

                </div><!-- .text-cards-top -->

                <?php if( have_rows('items') ): ?>
                
                    <div class="text-cards-items">
                
                        <?php while ( have_rows('items') ) : the_row(); 
                            $index = get_row_index();
                            $image = get_sub_field('image');
                            $image_type = get_sub_field('image_type');
                            $link = get_sub_field('link');

                            if( $link ): 
                                $link_url = $link['url'];
                                $link_title = $link['title'];
                                $link_target = $link['target'] ? $link['target'] : '_self';
                            ?>

                                <div class="text-card-item text-card-item-<?php echo $index; ?> text-card-item-type-<?php echo $image_type; ?>">
                                    <a href="<?php echo esc_url($link_url); ?>" target="<?php echo esc_attr($link_target); ?>">
                                        <div class="text-card-item-bg" <?php echo ( $image_type == 'background' && $image )? 'style="background-image:url('.wp_get_attachment_image_url($image['ID'], 'medium_large').');"': ''; ?> ></div>

                                        <?php if( $image_type != 'background' && $image ): ?>

                                            <div class="text-card-item-img-wrap text-card-item-<?php echo $image_type; ?>">
                                                <?php echo ( isset($image['ID']) )? wp_get_attachment_image($image['ID'], 'full'):''; ?>
                                            </div><!-- .text-card-item-image -->
                                            
                                        <?php endif; ?>

                                        <div class="text-card-item-text">
                                            <span><?php echo esc_html($link_title); ?></span>
                                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/arrow-white.svg" alt="arrow-white" class="text-card-item-arrow" />
                                        </div><!-- .text-card-item-text -->

                                    </a>
                                </div><!-- .text-card-item -->

                            <?php endif; ?>
                
                        <?php endwhile; ?>
                
                    </div><!-- .text-cards-items -->
                
                <?php endif; ?>

            </div><!-- .text-cards-inner -->
        </div><!-- .container -->
    </section><!-- .text-cards-wrapper -->
    
<?php endif; ?>