<?php 
/*
* Block Name: Document Group
* Post Type: page 
*/

if( isset( $block['data']['preview_image_help'] )  ) :
    echo '<img src="'. $block['data']['preview_image_help'] .'" style="width:100%; height:auto;">';
else: 

$item_data = setup_global_sidebar_for_block(get_field('title'));

?>

    <section class="document-group-wrapper <?php if ( !empty($item_data['block_class']) ) { echo $item_data['block_class']; } ?>">
        <div class="container">
            <div class="document-group-inner">
                <?php if ( get_field('title') ) : ?>
                    <h2 class="document-group-title"><?php the_field('title'); ?></h2>
                <?php endif; ?>
                <?php if ( get_field('content') ) : ?>
                    <div class="document-group-content"><?php the_field('content'); ?></div>
                <?php endif; ?>
                <?php if( have_rows('documents') ): ?>
                    <?php while ( have_rows('documents') ) : the_row(); ?>
                    <?php
                    $file_id = get_sub_field('document');
                    ?>
            
                    <?php if ( $file_id ): ?>
                        <?php
                        $file_path = get_attached_file($file_id);
                        $file_url = wp_get_attachment_url($file_id);
                        $file_size = round(((filesize($file_path)) / 1024 / 1024), 2);
                        ?>
                        <div class="document-item">
                            <div class="document-item-icon-wrap">
                                <?php echo file_get_contents(get_template_directory() . '/assets/images/fileIcon.svg'); ?>
                            </div>
                            <div class="document-item-main">
                                <div class="document-item-main-l">
                                <div class="document-item-title"><?php echo get_the_title($file_id); ?></div>
                                <div class="document-item-subtitle"><?php echo strtoupper(pathinfo($file_path, PATHINFO_EXTENSION)) . ', ' . $file_size . ' ' . 'MB'; ?></div>
                                </div>
                                <a href="<?php echo esc_url($file_url); ?>" download class="document-item-dl">
                                    <?php esc_html_e('Download', 'split-aritport'); ?>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php endwhile; ?>
                <?php endif; ?>
                <?php get_template_part('template-parts/blocks/warning-message', null, ['warning_message' => get_field('warning_message')]); ?>
            </div>
        </div>

    </section><!-- .document-group-wrapper-->
    
<?php endif; ?>