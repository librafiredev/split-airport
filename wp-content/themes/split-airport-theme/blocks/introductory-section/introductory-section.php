<?php 
/*
* Block Name: Introductory Section
* Post Type: page 
*/

if( isset( $block['data']['preview_image_help'] )  ) :
    echo '<img src="'. $block['data']['preview_image_help'] .'" style="width:100%; height:auto;">';
else: ?>

    <section class="introductory-section-wrapper">
        <div class="container">
            <div class="row">
                <div class="introductory-section-col">
                    <div class="entry-content">
                        <?php the_field('content'); ?>
                    </div>

                    <?php if( have_rows('badges') ): ?>
                        <?php while ( have_rows('badges') ) : the_row(); ?>
                            <?php $badge_type = get_sub_field('badge_type'); ?>
                            <div class="introductory-section-<?php $badge_type ?>">
                                <?php the_sub_field('label'); ?>
                                <?php if ( $badge_type ): ?>
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 12L12 4" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/><path d="M5.5 4H12V10.5" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
                
                <?php if ( ($image = get_field('image')) || have_rows('additional_links') ): ?>
                    <div class="introductory-section-col">
                        <?php echo wp_get_attachment_image($image['ID'], 'large'); ?>

                        <?php if( have_rows('additional_links') ): ?>
                            <?php while ( have_rows('additional_links') ) : the_row(); ?>
                                
                                <div class="introductory-section-mobile-links">
                                    <?php if ( !empty($link = get_sub_field('link')) ): ?>
                                        <a href="<?php echo $link['url']; ?>">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 12L12 4" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/><path d="M5.5 4H12V10.5" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/></svg><?php echo $link['title']; ?>
                                        </a>
                                    <?php endif; ?>
                                </div>

                            <?php endwhile; ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </section><!-- .introductory-section-wrapper-->
    
<?php endif; ?>