<?php 
/*
* Block Name: Simple Block
* Post Type: page 
*/

if( isset( $block['data']['preview_image_help'] )  ) :
    echo '<img src="'. $block['data']['preview_image_help'] .'" style="width:100%; height:auto;">';
else: ?>

    <section class="simple-block-wrapper">
        <div class="container">
            <div><?php echo $block['data']['content']; ?></div>
            <div>
                <?php if( have_rows('buttons') ): ?>
                    <div class="simple-block-buttons">
                        <?php while ( have_rows('buttons') ) : the_row(); ?>
                            <?php if ( $link = get_sub_field('link') ) : ?>
                                <div>
                                    <?php
                                    get_template_part('template-parts/shortcodes/button', null, ['title' => isset($link['title']) ? $link['title'] : "", 'url' => isset($link['url']) ? $link['url'] : "", 'newTab' => isset($link['newtab']) ? $link['newtab'] : "no"]);
                                    ?>
                                </div>
                            <?php endif; ?>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section><!-- .simple-block-wrapper-->
    
<?php endif; ?>