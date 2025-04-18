<?php
/*
* Block Name: Introductory Section
* Post Type: page 
*/

if (isset($block['data']['preview_image_help'])) :
    echo '<img src="' . $block['data']['preview_image_help'] . '" style="width:100%; height:auto;">';

else:
    $button = get_field('button');

?>

    <section class="introductory-section-wrapper">
        <div class="container">
            <div class="row">
                <div class="introductory-section-col introductory-section-txt-col">
                    <div class="entry-content">
                        <?php the_field('content'); ?>
                    </div>

                    <?php if ($button): ?>

                        <a
                            <?php if (!empty($button)): ?>
                            target="<?php echo esc_attr($button['target']); ?>"
                            <?php endif; ?>
                            class="introductory-section-button"
                            href="<?php echo esc_url($button['url']); ?>">
                            <?php
                            echo esc_html($button['title']) . file_get_contents(get_template_directory() . '/assets/images/link-arrow.svg');
                            ?>
                        </a>


                    <?php endif; ?>

                    <?php if (have_rows('badges')): ?>
                        <div class="introductory-section-badges">
                            <?php while (have_rows('badges')) : the_row(); ?>
                                <?php $badge_type = get_sub_field('badge_type'); ?>
                                <div class="introductory-section-badge introductory-section-badge-<?php echo $badge_type; ?>">
                                    <?php the_sub_field('label'); ?>
                                    <?php if ($badge_type == 'top-right-arrow'): ?>
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M4 12L12 4" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" />
                                            <path d="M5.5 4H12V10.5" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    <?php endif; ?>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (($image = get_field('image')) || have_rows('additional_links')): ?>
                    <div class="introductory-section-col introductory-section-img-col">
                        <?php if (!empty($image)): ?>
                            <div class="introductory-section-img"><?php echo wp_get_attachment_image($image['ID'], 'large'); ?></div>
                        <?php endif; ?>

                        <?php if (have_rows('additional_links')): ?>
                            <?php while (have_rows('additional_links')) : the_row(); ?>

                                <div class="introductory-section-mobile-links">
                                    <?php if (!empty($link = get_sub_field('link'))): ?>
                                        <a href="<?php echo $link['url']; ?>" class="introductory-section-additional-link">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M4 12L12 4" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" />
                                                <path d="M5.5 4H12V10.5" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg><?php echo $link['title']; ?>
                                        </a>
                                    <?php endif; ?>
                                </div>

                            <?php endwhile; ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </section><!-- .introductory-section-wrapper-->

<?php endif; ?>