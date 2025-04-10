<?php 
/*
* Block Name: Download Schedule
* Post Type: page 
*/

if( isset( $block['data']['preview_image_help'] )  ) :
    echo '<img src="'. $block['data']['preview_image_help'] .'" style="width:100%; height:auto;">';
else: ?>

    <section class="download-schedule-wrapper" style="background-image: url(<?php echo get_template_directory_uri() ?>/assets/images/dots-pattern.svg);">

        <div class="container">
            <div class="download-schedule-top">
                <div class="download-schedule-top-left">
                    <h2><?php the_field('title'); ?></h2>
                </div>

                <?php if( have_rows('files') ): ?>
                    <div class="download-schedule-top-right">
                        <?php while ( have_rows('files') ) : the_row(); ?>
                        <?php
                        $file_id = get_sub_field('file');
                        ?>
                        
                        <?php if ( $file_id ): ?>
                            <?php

                            $file_path = get_attached_file($file_id);
                            $file_url = wp_get_attachment_url($file_id);
                            $file_size = round(((filesize($file_path)) / 1024 / 1024), 2);
                            ?>

                            <div class="download-schedule-tr-item">
                                <div class="download-schedule-tri-icon-wrap">
                                    <?php echo file_get_contents(get_template_directory() . '/assets/images/fileIcon.svg'); ?>
                                </div>

                                <div class="download-schedule-tri-main">
                                    <div class="download-schedule-tri-main-l">
                                        <div class="download-schedule-tri-title"><?php echo ucfirst(basename($file_path)); ?></div>
                                        <div class="download-schedule-tri-subtitle"><?php echo strtoupper(pathinfo($file_path, PATHINFO_EXTENSION)) . ', ' . $file_size . ' ' . 'MB'; ?></div>
                                    </div>
                                    <a href="<?php echo esc_url($file_url); ?>" download class="download-schedule-item-dl">
                                        <?php esc_html_e('Download', 'split-aritport'); ?>
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ( get_field('text') ): ?>
                <div class="download-schedule-bottom">
                    <?php the_field('text'); ?>
                </div>
            <?php endif; ?>
        </div>

    </section><!-- .download-schedule-wrapper-->
    
<?php endif; ?>