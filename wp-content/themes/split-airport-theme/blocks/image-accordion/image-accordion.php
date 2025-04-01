<?php
/*
* Block Name: Image Accordion
* Post Type: page 
*/

if (isset($block['data']['preview_image_help'])) :
    echo '<img src="' . $block['data']['preview_image_help'] . '" style="width:100%; height:auto;">';
else:

    $image = get_field('image');
    $title = get_field('title');
    $subtitle = get_field('subtitle');
    $checkin_section = get_field('checkin_section');
    $accordions_section = get_field('accordions_section');
    $list_section = get_field('list_section');
    $visibility = get_field('visibility');
    $bottom_title = get_field('bottom_title');
    $bottom_text = get_field('bottom_text');
?>

    <section class="image-accordion">
        <div class="container">
            <div class="image-accordion__inner">
                <div class="image-accordion__items">
                    <div class="image-accordion__left">

                        <?php if ($image): ?>

                            <div class="image-accordion__image">
                                <?php echo wp_get_attachment_image($image, 'large'); ?>
                            </div>

                        <?php endif; ?>

                    </div>
                    <div class="image-accordion__right">

                        <?php if ($title): ?>

                            <h2 class="heading-secondary"><?php echo $title; ?></h2>

                        <?php endif; ?>

                        <?php if ($subtitle): ?>

                            <p class="image-accordion__subtitle"><?php echo $subtitle; ?></p>

                        <?php endif; ?>

                        <?php if ($visibility['checkin_section_visibility']): ?>

                            <div class="image-accordion__checkin">

                                <?php if ($checkin_section['title']): ?>

                                    <h3 class="image-accordion__checkin-title heading-third"><?php echo $checkin_section['title']; ?></h3>

                                <?php endif; ?>

                                <?php if ($checkin_section['text']): ?>

                                    <div class="image-accordion__checkin-text"><?php echo $checkin_section['text']; ?></div>

                                <?php endif; ?>

                                <?php if ($checkin_section['visibility'] === 'boxes'): ?>

                                    <div class="image-accordion__checkin-boxes">
                                        <div class="image-accordion__checkin-box">
                                            <div class="image-accordion__checkin-box-left">
                                                <?php echo file_get_contents(get_template_directory() . '/assets/images/planet.svg'); ?>
                                            </div>
                                            <div class="image-accordion__checkin-box-right">

                                                <?php if ($checkin_section['box_1_title']): ?>

                                                    <p class="image-accordion-secondary-title"><?php echo $checkin_section['box_1_title']; ?></p>

                                                <?php endif; ?>

                                                <?php if ($checkin_section['box_1_text']): ?>

                                                    <p class="image-accordion__checkin-box-text"><?php echo $checkin_section['box_1_text']; ?></p>

                                                <?php endif; ?>

                                            </div>
                                        </div>
                                        <div class="image-accordion__checkin-box">
                                            <div class="image-accordion__checkin-box-left">
                                                <?php echo file_get_contents(get_template_directory() . '/assets/images/planet.svg'); ?>
                                            </div>
                                            <div class="image-accordion__checkin-box-right">

                                                <?php if ($checkin_section['box_2_title']): ?>

                                                    <p class="image-accordion-secondary-title"><?php echo $checkin_section['box_2_title']; ?></p>

                                                <?php endif; ?>

                                                <?php if ($checkin_section['box_2_text']): ?>

                                                    <p class="image-accordion__checkin-box-text"><?php echo $checkin_section['box_2_text']; ?></p>

                                                <?php endif; ?>

                                            </div>
                                        </div>
                                    </div>

                                <?php endif; ?>

                                <?php if ($checkin_section['visibility'] === 'button' && $checkin_section['button']):
                                    echo do_shortcode('[button title="' . $checkin_section['button']['title'] . '" url="' . $checkin_section['button']['url'] . '" newTab="' . (isset($checkin_section['button']['target']) && $checkin_section['button']['target'] === '_blank' ? 'yes' : 'no') . '"]');
                                endif; ?>

                                <?php get_template_part('template-parts/blocks/warning-message', null, ['warning_message' => $checkin_section['warning_message']]); ?>

                            </div>

                        <?php endif; ?>

                        <?php if ($visibility['accordion_section_visibility']): ?>

                            <?php get_template_part('template-parts/blocks/accordions', null, ['accordions_section_fields' => $accordions_section]); ?>

                        <?php endif; ?>

                        <?php if ($visibility['list_section_visibility']): ?>

                            <?php get_template_part('template-parts/blocks/list', null, ['list_section_fields' => $list_section]); ?>

                        <?php endif; ?>

                        <?php if ($bottom_title || $bottom_text): ?>

                            <div class="image-accordion__bottom-section">

                                <?php if ($bottom_title): ?>

                                    <h3 class="image-accordion__bottom-section-title heading-third"><?php echo $bottom_title; ?></h3>

                                <?php endif; ?>

                                <?php if ($bottom_text): ?>

                                    <p class="image-accordion__bottom-section-text"><?php echo $bottom_text; ?></p>

                                <?php endif; ?>

                            </div>

                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </section><!-- .image-accordion-->

<?php endif; ?>