<?php 
/*
* Block Name: Home Hero
* Post Type: page 
*/

if( isset( $block['data']['preview_image_help'] )  ) :
    echo '<img src="'. esc_url($block['data']['preview_image_help']) .'" style="width:100%; height:auto;">';
else: 

    $follow_flights_pages = get_field('business_homepage', 'options') ?? [];
    $excluded_pages = array_map(fn($post) => $post->ID, get_field('business_homepage', 'options') ?? []);


    $title = get_field('title');
    
    $background_mobile = get_field('background_mobile');
    
    $background = get_field('background');

    $split_first_two_items = get_field('split_first_two_items');

    $show_search_form = get_field('show_search_form');

    $items = get_field('items') ?: [];
    $count_correction = $split_first_two_items ? 1 : 0;

    $corrected_count = count($items) - $count_correction;
    ?>

    <div class="hero-home-overflow <?php echo $show_search_form ? 'hero-home-has-search' : 'hero-home-no-search'; ?> <?php echo $corrected_count % 2 == 0 ? 'hero-home-even-items' : 'hero-home-odd-items' ?>">
        <section class="home-hero-wrapper">

            <?php echo wp_get_attachment_image($background_mobile, 'full', false, array('class' => 'home-hero-bg home-hero-bg-mobile')); ?>
            <?php echo wp_get_attachment_image($background, 'full', false, array('class' => 'home-hero-bg home-hero-bg-desktop')); ?>
            
            <div class="container">
                <div class="home-hero-inner">

                    <?php if( $title ): ?>
                        <div class="home-hero-title">
                            <h1><?php echo esc_html($title); ?></h1>
                        </div><!-- .home-hero-title -->
                    <?php endif; ?>

                    <?php if ( $show_search_form ): ?>
                        <div class="home-hero-search">
                            <?php get_template_part('template-parts/blocks/arrivals-timetable', 'search'); ?>
                        </div><!-- .home-hero-search -->
                    <?php endif; ?>

                    <?php if( !empty($items) ): ?>
                        <div class="home-hero-items" style="max-width: <?php echo 182.5 * $corrected_count; ?>px;">
                            <svg width="186" height="176" version="1.2" viewBox="0 0 186 176" xmlns="http://www.w3.org/2000/svg" class="svg-hero-cutout-bg"><path class="s0" d="m0 0v176h197.4v-2h-10.8c-4.5 0-9.1-1.9-12.2-5.2l-148.4-163.6c-3.1-3.3-7.4-5.2-11.9-5.2z"/></svg>
                    
                            <div class="home-hero-items-inner">
                                <?php 
                                $index_open = false;
                                foreach ($items as $index => $item): 
                                    $icon = $item['icon'] ?? null;
                                    $link = $item['link'] ?? null;
                                    $row_index = $index + 1;
                                    if ($link):
                                        $link_url = $link['url'] ?? '#';
                                        $link_title = $link['title'] ?? '';
                                        $link_target = $link['target'] ?? '_self';
                                ?>

                                    <?php if( $row_index == 1 && $split_first_two_items && !$index_open ): 
                                        $index_open = true;
                                    ?>
                                        <div class="home-hero-item-two">
                                    <?php endif; ?>

                                    <div class="home-hero-item <?php echo ($row_index > 2 || !$split_first_two_items) ? 'home-hero-item-one' : ''; ?>">
                                        <a href="<?php echo esc_url($link_url); ?>" target="<?php echo esc_attr($link_target); ?>">
                                            <?php if ( $row_index == count($items) ): ?>
                                                <svg version="1.2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 128 148" width="128" height="148" class="svg-hero-cutout-item"><path id="path2" d="m0.3 0v148h119.7c6.9 0 10.6-8.2 6-13.4l-119.1-132c-1.5-1.6-3.7-2.6-6-2.6z"/></svg>
                                            <?php endif; ?>

                                            <?php if( !empty($icon) && isset($icon['ID']) ): ?>
                                                <div class="home-hero-item-icon">
                                                    <?php echo wp_get_attachment_image($icon['ID'], 'full'); ?>
                                                </div><!-- .home-hero-item-icon -->
                                                <div class="home-hero-item-text">
                                                    <span><?php echo esc_html($link_title); ?></span>
                                                </div><!-- .home-hero-item-text -->
                                            <?php endif; ?>
                                        </a>
                                    </div><!-- .home-hero-item -->

                                    <?php if( $row_index == 2 && $split_first_two_items && $index_open ): 
                                        $index_open = false;
                                    ?>
                                        </div><!-- .home-hero-item-two -->
                                    <?php endif; ?>

                                <?php 
                                    endif; 
                                endforeach; 
                                if( $index_open && $split_first_two_items ):
                                    $index_open = false;
                                ?>
                                    </div><!-- .home-hero-item-two -->
                                <?php endif; ?>
                            </div><!-- .home-hero-items-inner -->
                        </div><!-- .home-hero-items -->
                    <?php endif; ?>

                </div><!-- .home-hero-inner -->
            </div><!-- .container -->

            <?php 
             if (!in_array(get_the_ID(), $excluded_pages)) {
                get_template_part('template-parts/blocks/my-flights'); 
            }
            ?>

        </section><!-- .home-hero-wrapper-->
    </div>

<?php endif; ?>
