<?php 
/*
* Block Name: Categorized Content
* Post Type: page 
*/

if( isset( $block['data']['preview_image_help'] )  ) :
    echo '<img src="'. $block['data']['preview_image_help'] .'" style="width:100%; height:auto;">';
else: ?>

    <section class="categorized-content-wrapper categorized-content-<?php echo get_field('styles') ?: 'default'; ?>">
        <div class="container">
            <div class="categorized-content__inner">
                <div class="categorized-content__left">
                    <div class="categorized-content__text">
                        <?php if ( get_field('title') ) : ?>
                            <h2 class="categorized-content-title"><?php the_field('title'); ?></h2>
                        <?php endif; ?>
                        <?php if ( get_field('subtitle') ) : ?>
                            <h2 class="categorized-content-subtitle"><?php the_field('subtitle'); ?></h2>
                        <?php endif; ?>
                        <?php if( have_rows('items') ): ?>
                            <?php while ( have_rows('items') ) : the_row(); ?>
                                <div class="categorized-content__item">
                                    <h3 class="heading-third"><?php the_sub_field('title'); ?></h3>
                                    <?php the_sub_field('content'); ?>
                                </div>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
    </section><!-- .categorized-content-wrapper-->
    
<?php endif; ?>