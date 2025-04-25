<?php 
/*
* Block Name: Procurement Table
* Post Type: page 
*/

if( isset( $block['data']['preview_image_help'] )  ) :
    echo '<img src="'. $block['data']['preview_image_help'] .'" style="width:100%; height:auto;">';
else: ?>
    <section class="procurement-table-wrapper ">
        <div class="request-doc-modal-wrapper custom-modal-wrapper">
            <div class="custom-modal-close-area"></div>
            <div class="request-doc-modal custom-modal">
                <div class="custom-modal-close-btn-wrap">
                    <button type="button" class="custom-modal-close-btn">
                        <?php echo file_get_contents(get_template_directory() . '/assets/images/x.svg'); ?>
                    </button>
                </div>

                <div>
                    <h3 class="request-doc-header heading-third"><?php the_field('modal_header'); ?></h3>
                    <h2 class="request-doc-title"><?php the_field('title'); ?></h2>
                    
                    <?php if( have_rows('columns') ): ?>
                        <div class="request-doc-items">
                            <?php while ( have_rows('columns') ) : the_row(); ?>
                                <div class="request-doc-item">
                                    <div class="request-doc-item-title"><?php the_sub_field('title'); ?></div>
                                    <div class="request-doc-item-value"><?php the_sub_field('value'); ?></div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php endif; ?>
                    <?php echo the_field('modal_form_content'); ?>
                </div>
            </div>
            
        </div>

        <div class="container">
            <div class="procurement-table-inner">
                <h3 class="heading-third procurement-table-subtitle"><?php the_field('subtitle'); ?></h3>

                <div class="procurement-table">
                    <div class="procurement-table-header">
                        <h3 class="heading-third"><?php the_field('table_header'); ?></h3>
                        <button type="button" class="request-doc-modal-btn"><span><?php the_field('title'); ?></span><?php echo file_get_contents(get_template_directory() . '/assets/images/link-arrow.svg'); ?></button>
                    </div>

                    <?php if( have_rows('columns') ): ?>
                        <div class="procurement-table-rows">
                            <?php while ( have_rows('columns') ) : the_row(); ?>
                                <div class="procurement-table-row">
                                    <div class="procurement-table-row-title heading-third"><?php the_sub_field('title'); ?></div>
                                    <div class="procurement-table-row-value"><?php the_sub_field('value'); ?></div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ( $link = get_field('link') ) : ?>
                    <div>
                        <?php 
                        get_template_part('template-parts/shortcodes/button', null, ['title' => isset($link['title']) ? $link['title'] : "", 'url' => isset($link['url']) ? $link['url'] : "", 'newTab' => isset($link['newtab']) ? $link['newtab'] : "no"]);
                        ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section><!-- .procurement-table-wrapper-->
    
<?php endif; ?>