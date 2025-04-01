<?php
/*
* Block Name: Image Box
* Post Type: page 
*/

if (isset($block['data']['preview_image_help'])) :
    echo '<img src="' . $block['data']['preview_image_help'] . '" style="width:100%; height:auto;">';
else:

    $title = get_field('title');
    $image = get_field('image');
    $content = get_field('content');
    $visibility = get_field('visibility');
    $accordion_section = get_field('accordions_section');
    $box_section = get_field('box_section');
    $lists = get_field('lists');
    $bottom_section = get_field('bottom_section');
    $warning_message = get_field('warning_message');
?>

    <section class="image-box">
        <div class="container">
            <div class="image-box__inner">
                <div class="image-box__items">
                    <div class="image-box__left">

                        <?php if ($image): ?>

                            <div class="image-box__image">
                                <?php echo wp_get_attachment_image($image, 'large'); ?>
                            </div>

                        <?php endif; ?>

                    </div>
                    <div class="image-box__right">

                        <?php if ($title): ?>

                            <h2 class="title-secondary"><?php echo $title; ?></h2>

                        <?php endif; ?>

                        <?php if ($content): ?>

                            <div class="image-box__content"><?php echo $content; ?></div>

                        <?php endif; ?>

                        <?php if ($visibility['box_section_visibility']): ?>

                            <div class="image-box__section-box">

                                <?php if ($box_section['title']): ?>

                                    <p class="image-box__section-box-title"><?php echo $box_section['title']; ?></p>

                                <?php endif; ?>

                                <div class="image-box__section-box-items">
                                    <div class="image-box__section-box-item">
                                        <div class="image-box__section-box-item-image">
                                            <?php echo file_get_contents(get_template_directory() . '/assets/images/check.svg'); ?>
                                        </div>
                                        <div class="image-box__section-box-item-content">

                                            <?php if ($box_section['box_1_main_text']): ?>

                                                <p class="image-box__section-box-item-main-text"><?php echo $box_section['box_1_main_text']; ?></p>

                                            <?php endif; ?>

                                            <?php if ($box_section['box_1_text']): ?>

                                                <p class="image-box__section-box-item-text"><?php echo $box_section['box_1_text']; ?></p>

                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="image-box__section-box-item">
                                        <div class="image-box__section-box-item-image">
                                            <?php echo file_get_contents(get_template_directory() . '/assets/images/x.svg'); ?>
                                        </div>
                                        <div class="image-box__section-box-item-content">

                                            <?php if ($box_section['box_2_main_text']): ?>

                                                <p class="image-box__section-box-item-main-text"><?php echo $box_section['box_2_main_text']; ?></p>

                                            <?php endif; ?>

                                            <?php if ($box_section['box_2_text']): ?>

                                                <p class="image-box__section-box-item-text"><?php echo $box_section['box_2_text']; ?></p>

                                            <?php endif; ?>
                                        </div>
                                    </div>

                                </div>

                            </div>

                        <?php endif; ?>

                        <?php
                        get_template_part('template-parts/blocks/warning-message', null, ['warning_message' => $warning_message]);
                        if ($visibility['list_section_visibility'] && $lists):
                            foreach ($lists as $list_section) :
                                get_template_part('template-parts/blocks/list', null, ['list_section_fields' => $list_section['list_section']]);
                            endforeach;
                        endif;
                        if ($visibility['accordion_section_visibility']):
                            get_template_part('template-parts/blocks/accordions', null, ['accordions_section_fields' => $accordion_section['accordions_section']]);
                        endif; ?>


                        <?php if ($visibility['bottom_section_visibility']): ?>

                            <div class="image-box__bottom-section">

                                <?php if ($bottom_section['title']): ?>

                                    <h3 class="heading-third"><?php echo $bottom_section['title']; ?></h3>

                                <?php endif; ?>

                                <?php if ($bottom_section['content']): ?>

                                    <div class="image-box__bottom-section-content"><?php echo $bottom_section['content']; ?></div>

                                <?php endif; ?>

                            </div>

                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </section><!-- .image-box-->

<?php endif; ?>