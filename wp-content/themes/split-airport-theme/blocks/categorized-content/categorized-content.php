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
                                    <?php the_sub_field('content'); ?>
                                </div>
                                <?php
                                $documents = get_field('document');

                                if ($documents && is_array($documents)) :
                                    foreach ($documents as $doc) :
                                        $file = $doc['file'];
                                        $file_id = $file['ID'];

                                        if ($file_id) :
                                            $file_path = get_attached_file($file_id);
                                            $file_url = wp_get_attachment_url($file_id);
                                            $file_size = round(((filesize($file_path)) / 1024 / 1024), 2);
                                            $document_description = $doc['document_description']; // koristi direktno iz $doc
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
                                                        <div class="document-item-title"><?php echo esc_html($document_title); ?></div>
                                                    </div>
                                                    <div class="document-item-main-c">
                                                        <a href="<?php echo esc_url($file_url); ?>" download class="document-item-dl">
                                                            <?php esc_html_e('Download', 'split-aritport'); ?>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                <?php
                                        endif;
                                    endforeach;
                                endif;
                                ?>

                            <?php endwhile; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
    </section><!-- .categorized-content-wrapper-->
    
<?php endif; ?>