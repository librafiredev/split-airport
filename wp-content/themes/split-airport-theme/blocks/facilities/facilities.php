<?php
/*
* Block Name: Facilities
* Post Type: page 
*/

if (isset($block['data']['preview_image_help'])) :
    echo '<img src="' . $block['data']['preview_image_help'] . '" style="width:100%; height:auto;">';
else:
    $facilities = get_field('facilities');
?>

    <section class="facilities">
        <div class="container">
            <div class="facilities__inner">
                <?php if (get_field('title')) : ?>
                    <h2 class="facilities__title">
                        <?php the_field('title'); ?>
                    </h2>
                <?php endif; ?>
                
                <?php if (get_field('subtitle')) : ?>
                    <div class="facilities__subtitle">
                        <?php the_field('subtitle'); ?>
                    </div>
                <?php endif; ?>

                <?php if ($facilities): ?>

                    <div class="facilities__items">

                    <?php foreach ($facilities as $facility): ?>
                        <div class="facilities__item">
                            <?php if ($facility['image']): ?>
                                <div class="facilities__item-image">
                                    <?php echo wp_get_attachment_image($facility['image'], 'large'); ?>
                                </div>
                            <?php endif; ?>

                            <div class="facilities__item-content">
                                <?php if ($facility['title']): ?>
                                    <h2 class="heading-secondary"><?php echo $facility['title']; ?></h2>
                                <?php endif; ?>

                                <?php if ($facility['working_hours']): ?>
                                        <p class="page-bank-exchanges-working-hours"><?php echo $facility['working_hours']; ?></p>
                                <?php endif; ?>

                                <?php if ($facility['description']): ?>
                                    <div class="facilities__item-description"><?php echo $facility['description']; ?></div>
                                <?php endif; ?>

                                <?php if ($facility['button']): ?>
                                    <a
                                        <?php if (!empty($facility['button'])): ?>
                                        target="<?php echo esc_attr($facility['button']['target']); ?>"
                                        <?php endif; ?>
                                        class="parking__item-button"
                                        href="<?php echo esc_url($facility['button']['url']); ?>">
                                        <?php
                                        echo esc_html($facility['button']['title']) . file_get_contents(get_template_directory() . '/assets/images/link-arrow.svg');
                                        ?>
                                    </a>
                                <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>


                    </div>

                <?php endif; ?>

            </div>
        </div>
    </section><!-- .facilities-->

<?php endif; ?>