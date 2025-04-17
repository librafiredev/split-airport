<?php
/*
* Block Name: Image Content
* Post Type: page 
*/

if (isset($block['data']['preview_image_help'])) :
    echo '<img src="' . $block['data']['preview_image_help'] . '" style="width:100%; height:auto;">';
else:
    $content = get_field('content');
    $image = get_field('image');
    $spacing_suffix = 'default';
    $spacing_type = get_field('spacing_type');

    if ( !empty($spacing_type) ) {
        $spacing_suffix = $spacing_type;
    }
?>

    <section class="image-content <?php echo 'image-content-spacing-' . $spacing_suffix; ?>">
        <div class="container">
            <div class="image-content__inner">
                <div class="image-content__items">
                    <div class="image-content__left">

                        <?php if ($content): ?>

                            <div class="image-content__text">
                                <?php echo apply_filters('the_content', $content); ?>
                            </div>

                        <?php endif; ?>

                    </div>
                    <div class="image-content__right">

                        <?php if ($image): ?>

                            <div class="image-content__image">
                                <?php echo wp_get_attachment_image($image, 'large'); ?>
                            </div>

                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </section><!-- .image-content-->

<?php endif; ?>