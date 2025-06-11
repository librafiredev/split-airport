<?php
/*
* Block Name: Page Hero
* Post Type: page 
*/

if (isset($block['data']['preview_image_help'])) :
    echo '<img src="' . esc_url($block['data']['preview_image_help']) . '" style="width:100%; height:auto;">';
else:
    $working_hours = get_field('working_hours');

    $follow_flights_pages = get_field('business_homepage', 'options');
    $hide_my_flight_button = get_field('hide_my_flights_button', 'options');

    $follow_flights_pages = is_array($follow_flights_pages) ? $follow_flights_pages : [];
    $hide_my_flight_button = is_array($hide_my_flight_button) ? $hide_my_flight_button : [];

    $excluded_pages = array_map(fn($post) => $post->ID, $follow_flights_pages);
    $hidden_pages = array_map(fn($post) => $post->ID, $hide_my_flight_button);

    $current_page_id = get_the_ID();
    $should_hide_my_flights = in_array($current_page_id, $excluded_pages) || in_array($current_page_id, $hidden_pages);
?>

    <section class="page-hero-wrapper">

        <div class="page-hero-wrapper-inner">
            <div class="page-hero-img-wrap">
                <div class="page-hero-img-pattern" style="background-image: url(<?php echo esc_url(get_template_directory_uri() . '/assets/images/hero-pattern.png'); ?>);"></div>
                <?php echo wp_get_attachment_image(get_field('background'), 'full'); ?>
            </div>
            <div class="page-hero-cutout-wrap">
                <svg version="1.2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1439 71" width="1439" height="71" class="page-hero-cutout">
                    <path d="m682.7 0h-682.7v716h1445v-645h-611.4c-18.5 0-36.5-6.1-51.3-17.3l-48.3-36.5c-14.8-11.2-32.8-17.2-51.3-17.2z" />
                </svg>
            </div>

            <?php 
            if (!$should_hide_my_flights) {
                get_template_part('template-parts/blocks/my-flights'); 
            }
            ?>
        </div>

        <div class="container">

            <?php if (get_field('title')): ?>
                <h1 class="page-hero-title"><?php the_field('title'); ?></h1>
            <?php endif; ?>

            <?php if ($working_hours): ?>
                <p class="page-hero-working-hours"><?php echo esc_html($working_hours); ?></p>
            <?php endif; ?>

        </div>
    </section><!-- .page-hero-wrapper-->

<?php endif; ?>
