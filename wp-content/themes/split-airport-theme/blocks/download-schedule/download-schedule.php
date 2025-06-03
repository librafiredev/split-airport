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
                            $document_description = get_sub_field('document_description');
                            $document_title = get_the_title($file_id);
                            ?>

                            <div class="document-item">
                                            <div class="document-item-icon-wrap">
                                                <?php echo file_get_contents(get_template_directory() . '/assets/images/fileIcon.svg'); ?>
                                            </div>
                                        <div class="document-item-main">
                                            <div class="document-item-main-l">
                                                <div class="document-item-description">
                                                    <?php
                                                    echo esc_html(!empty($document_description) ? $document_description : $document_title);
                                                    ?>
                                                </div>
                                                <div class="document-item-title"><?php echo get_the_title($file_id); ?></div>
                                            </div>
                                                <div class="document-item-main-c">
                                                    <a href="<?php echo esc_url($file_url); ?>" download class="document-item-dl">
                                                        <?php esc_html_e('Download', 'split-aritport'); ?>
                                                    </a>
                                                </div>
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