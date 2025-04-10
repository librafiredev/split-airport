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
        
        $background_mobile = get_field('background_mobile');
        
        $background = get_field('background');

    ?>

    <div class="hero-home-overflow">
        <section class="home-hero-wrapper">
            <picture>
                <source srcset="<?php echo $background_mobile; ?>" media="(max-width: 767px)">
                <img class="home-hero-bg" src="<?php echo $background; ?>">
            </picture>

            
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
                        <?php get_template_part('template-parts/blocks/arrivals-timetable', 'search'); ?>
                    </div><!-- .home-hero-search -->

                    <?php if( have_rows('items') ): ?>
                    
                        <div class="home-hero-items" style="max-width: <?php echo 182.5 * (count( get_field('items') ) - 1); ?>px;">
                            <svg width="186" height="176" version="1.2" viewBox="0 0 186 176" xmlns="http://www.w3.org/2000/svg" class="svg-hero-cutout-bg"><path class="s0" d="m0 0v176h197.4v-2h-10.8c-4.5 0-9.1-1.9-12.2-5.2l-148.4-163.6c-3.1-3.3-7.4-5.2-11.9-5.2z"/></svg>
                    
                            <div class="home-hero-items-inner">
                                
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
                                        <div class="home-hero-item <?php echo $index > 2 ? 'home-hero-item-one' : ''; ?>">

                                            <a href="<?php echo esc_url($link_url); ?>" target="<?php echo esc_attr($link_target); ?>">
                                                <?php if ( $index == count( get_field('items') ) ): ?>
                                                    <svg version="1.2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 128 148" width="128" height="148" class="svg-hero-cutout-item"><path id="path2" d="m0.3 0v148h119.7c6.9 0 10.6-8.2 6-13.4l-119.1-132c-1.5-1.6-3.7-2.6-6-2.6z"/></svg>
                                                <?php endif; ?>

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
                    
                        </div><!-- .home-hero-items -->
                    
                    <?php endif; ?>

                </div><!-- .home-hero-inner -->
            </div><!-- .container -->
        </section><!-- .home-hero-wrapper-->
    </div>
    <?php get_template_part('template-parts/blocks/flight-popup'); ?>
    
<?php endif; ?>