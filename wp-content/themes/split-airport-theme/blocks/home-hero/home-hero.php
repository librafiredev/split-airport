<?php 
/*
* Block Name: Home Hero
* Post Type: page 
*/

if( isset( $block['data']['preview_image_help'] )  ) :
    echo '<img src="'. $block['data']['preview_image_help'] .'" style="width:100%; height:auto;">';
else: ?>

    <?php 

        $title = get_field('title');
        $background = get_field('background');

    ?>

    <section class="home-hero-wrapper" <?php echo ( $background )? 'style="background-image:url('. $background .')"':''; ?>>
        <div class="container">
            <div class="home-hero-inner">

                <?php if( $title ): ?>

                    <div class="home-hero-title">
                        <h1>
                            <?php echo $title; ?>
                        </h1>
                    </div><!-- .home-hero-title -->
                    
                <?php endif; ?>

                <div class="home-hero-search">
                </div><!-- .home-hero-search -->

                <?php if( have_rows('items') ): ?>
                
                    <div class="home-hero-items">
                
                        <?php while ( have_rows('items') ) : the_row(); 

                            $index = get_row_index(); 
                            $icon = get_sub_field('icon');
                            $link = get_sub_field('link');

                            if( $link ): 
                                $link_url = $link['url'];
                                $link_title = $link['title'];
                                $link_target = $link['target'] ? $link['target'] : '_self';

                                ?>

                                <?php if( $index == 1 ): 
                                    $index_open = true;
                                ?>
                                    <div class="home-hero-item-two">
                                <?php endif; ?>
                    
                                <div class="home-hero-item">
                                    <a href="<?php echo esc_url($link_url); ?>" target="<?php echo esc_attr($link_target); ?>">

                                        <?php if( !empty($icon) ): ?>
                                        
                                            <div class="home-hero-item-icon">
                                                <?php echo ( isset($icon['ID']) )? wp_get_attachment_image($icon['ID'], 'full'):''; ?>
                                            </div><!-- .home-hero-item-icon -->

                                            <div class="home-hero-item-text">
                                                <span><?php echo esc_html($link_title); ?></span>
                                            </div><!-- .home-hero-item-text -->
                                        
                                        <?php endif; ?>
    
                                    </a>
                                </div><!-- .home-hero-item -->

                                <?php if( $index == 2 ): 
                                    $index_open = false;
                                ?>
                                    </div><!-- .home-hero-item-two -->
                                <?php endif; ?>
                            
                            <?php endif; ?>
                
                        <?php endwhile; ?>

                        <?php if( $index_open ): 
                            $index_open = false;
                        ?>
                            </div><!-- .home-hero-item-two -->
                        <?php endif; ?>
                
                    </div><!-- .home-hero-items -->
                
                <?php endif; ?>

            </div><!-- .home-hero-inner -->
        </div><!-- .container -->
    </section><!-- .home-hero-wrapper-->
    
<?php endif; ?>