<?php
/*
* Block Name: Faq
* Post Type: page 
*/

if (isset($block['data']['preview_image_help'])) :
    echo '<img src="' . $block['data']['preview_image_help'] . '" style="width:100%; height:auto;">';
else:
    $title = get_field('title');
    $accordions_section = get_field('accordions_section');
?>

    <section class="faq">
        <div class="container">
            <div class="faq__inner">
                <div class="faq__left">

                    <?php if ($title): ?>

                        <h2 class="heading-secondary"><?php echo $title; ?></h2>

                    <?php endif; ?>

                </div>
                <div class="faq__right">

                    <?php get_template_part('template-parts/blocks/accordions', null, ['accordions_section_fields' => $accordions_section['accordions_section']]); ?>

                </div>
            </div>
        </div>
    </section><!-- .faq-->

<?php endif; ?>