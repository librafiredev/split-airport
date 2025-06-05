<?php
/*
* Block Name: Transportation
* Post Type: page 
*/

if (isset($block['data']['preview_image_help'])) :
    echo '<img src="' . $block['data']['preview_image_help'] . '" style="width:100%; height:auto;">';
else:

    $warning_message = get_field('warning_message');
    $boxes = get_field('boxes');

?>

    <section class="transportation">
        <div class="container">
            <div class="transportation__inner">
                <?php get_template_part('template-parts/blocks/warning-message', null, ['warning_message' => $warning_message]); ?>
                <div class="transportation__items">

                    <?php if ($boxes): ?>

                        <div class="transportation__main">
                            <?php
                            foreach ($boxes as $box):
                                if($box['location'] !== 'default') continue;
                                get_template_part('template-parts/blocks/transportation-item', null, ['box' => $box]);
                            endforeach; ?>
                        </div>

                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section><!-- .transportation-->

<?php endif; ?>