<?php
/*
* Block Name: Airlines
* Post Type: page 
*/

if (isset($block['data']['preview_image_help'])) :
    echo '<img src="' . $block['data']['preview_image_help'] . '" style="width:100%; height:auto;">';
else:

    $term = isset($_GET['search']) ? wp_strip_all_tags($_GET['search']) : "";
    $sidebar = get_field('sidebar');

    $supported_airlines = new WP_Query([
        'post_type'             => 'airline',
        'posts_per_page'        => -1,
        'orderby'               => 'title',
        'order'                 => "ASC",
        's'                     => $term,
        'meta_query' => [
            [
                'key' => 'type',
                'value' => 'supported',
                'compare' => '=',
            ]
        ],
    ]);

    $unsupported_airlines = new WP_Query([
        'post_type'             => 'airline',
        'posts_per_page'        => -1,
        'orderby'               => 'title',
        'order'                 => "ASC",
        's'                     => $term,
        'meta_query' => [
            [
                'key' => 'type',
                'value' => 'unsupported',
                'compare' => '=',
            ]
        ],
    ]);

?>

    <section class="airlines">
        <div class="container">
            <div class="airlines__inner">
                <div class="airlines__main">
                    <label class="airlines__top">
                        <?php echo file_get_contents(get_template_directory() . '/assets/images/search-icon.svg');  ?>
                        <input placeholder="<?php esc_html_e('Find your airline', 'split-airport');  ?>" type="text" name="search" />
                    </label>
                    <button type="button" class="airlines-mobile-sidebar-btn">
                        <?php echo file_get_contents(get_template_directory() . '/assets/images/info.svg'); ?>
                        <span class="heading-third"><?php esc_html_e('Required identification & limitations', 'split-airport');  ?></span>
                        <svg width="7" height="12" viewBox="0 0 7 12" fill="none" xmlns="http://www.w3.org/2000/svg" class="chevron-right"><path d="M1 1L6 6L1 11" stroke="#084983" stroke-width="1.42857" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                    <div class="airlines__items">

                        <h3 class="heading-third"><?php esc_html_e('Supported airlines', 'split-airport'); ?></h3>

                        <?php if ($supported_airlines->have_posts()): ?>

                            <div class="airlines__supported">
                                <?php
                                while ($supported_airlines->have_posts()): $supported_airlines->the_post();
                                    get_template_part('template-parts/posts/airline');
                                endwhile; ?>

                            </div>

                        <?php else:
                            get_template_part('template-parts/posts/no-posts');
                        endif; ?>

                        <h3 class="heading-third"><?php esc_html_e('Unsupported airlines', 'split-airport'); ?></h3>

                        <?php if ($unsupported_airlines->have_posts()): ?>

                            <div class="airlines__unsupported">
                                <?php
                                while ($unsupported_airlines->have_posts()): $unsupported_airlines->the_post();
                                    get_template_part('template-parts/posts/airline');
                                endwhile; ?>
                            </div>

                        <?php else:

                            get_template_part('template-parts/posts/no-posts');
                        endif; ?>

                    </div>
                </div>

                <?php if ($sidebar): ?>

                    <div class="airlines__sidebar">
                        <div class="airlines-mobile-sidebar-close-btn-wrap">
                            <button type="button" class="airlines-mobile-sidebar-close-btn">
                                <?php echo file_get_contents(get_template_directory() . '/assets/images/x.svg'); ?>
                            </button>
                        </div>

                        <?php foreach ($sidebar as $section): ?>

                            <div class="airlines__section">

                                <?php if ($section['title']): ?>

                                    <h3 class="airlines__section-title"><?php echo $section['title']; ?></h3>

                                <?php endif; ?>

                                <?php if ($section['content']): ?>

                                    <div class="airlines__section-content"><?php echo $section['content']; ?></div>

                                <?php endif; ?>

                            </div>

                        <?php endforeach; ?>

                    </div>

                <?php endif; ?>

            </div>
        </div>
    </section><!-- .airlines-->
    <div class="loader"></div>

<?php endif; ?>