<?php 
/*
* Block Name: Text Cards Layout 2
* Post Type: page 
*/

if( isset( $block['data']['preview_image_help'] )  ) :
    echo '<img src="'. $block['data']['preview_image_help'] .'" style="width:100%; height:auto;">';
else: ?>

    <?php 

        $title = get_field('title');
        $text = get_field('text');

    ?>

    <section class="text-cards-wrapper text-cards-wrapper-2" style="background-image: url(<?php echo get_template_directory_uri() ?>/assets/images/dots-pattern.svg);">

        <div class="container">
            <div class="text-cards-inner">
                <img src="<?php echo get_template_directory_uri() ?>/assets/images/air-line.svg" class="text-cards-air-line" alt="Decoration image" />

                <div class="text-cards-top">

                    <div class="text-cards-top-item">
                        <?php if( $title ): ?>
                            <div class="text-cards-title">
                                <h2>
                                    <?php echo $title; ?>
                                </h2>
                            </div><!-- .text-cards-title -->
                        
                        <?php endif; ?>
                        <?php if( $text ): ?>
                            <div class="text-cards-text">
                                <p>
                                    <?php echo $text; ?>
                                </p>
                            </div><!-- .text-cards-text -->
                        
                        <?php endif; ?>
                    </div>

                </div><!-- .text-cards-top -->

                <?php if( have_rows('items') ): ?>
                
                    <div class="text-cards-2-items">
                
                        <?php while ( have_rows('items') ) : the_row(); 
                            $index = get_row_index();
                            $image = get_sub_field('image');
                            $image_type = get_sub_field('image_type');
                            $link = get_sub_field('link');

                            if( $link ): 
                                $link_url = $link['url'];
                                $link_title = $link['title'];
                                $link_target = $link['target'] ? $link['target'] : '_self';

                                get_template_part('template-parts/blocks/tc-grid-item', null, [
                                    'class' => 'text-card-2-item-'. $index,
                                    'image_type' => $image_type,
                                    'link_url' => $link_url,
                                    'link_target' => $link_target,
                                    'image' => $image,
                                    'link_title' => $link_title,
                                ]);
                            
                            endif; ?>
                
                        <?php endwhile; ?>
                
                    </div><!-- .text-cards-items -->
                
                <?php endif; ?>

            </div><!-- .text-cards-inner -->
        </div><!-- .container -->
    </section><!-- .text-cards-wrapper -->
    
<?php endif; ?>