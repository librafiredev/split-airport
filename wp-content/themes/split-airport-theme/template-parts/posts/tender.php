<?php 
$documents = '';
?>
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
        
        $documents .= '<div class="document-item">
            <div class="document-item-icon-wrap">
                '.file_get_contents(get_template_directory() . '/assets/images/fileIcon.svg') .'
            </div>
            <div class="document-item-main">
                <div class="document-item-main-l">
                    <div class="document-item-title">' . get_the_title($file_id) . '</div>
                    <div class="document-item-subtitle">'. strtoupper(pathinfo($file_path, PATHINFO_EXTENSION)) . ', ' . $file_size . ' ' . 'MB' . '</div>
                </div>
                <a href="' . esc_url($file_url) . '" download class="document-item-dl">
                    '. esc_html__('Download', 'split-aritport') . '
                </a>
            </div>
        </div>';
        ?>
    <?php endif; ?>
    <?php endwhile; ?>
<?php endif; ?>


<div class="tender-item">
    <div class="tender-item-dates">
        <?php the_field('start_date'); ?> - <?php the_field('end_date'); ?>
    </div>
    <h3 class="tender-item-title" onclick="populateAndOpenModal(<?php echo esc_attr(json_encode(array('post' => array('title' => get_the_title(), 'content' => get_the_content()), 'acf' => array('full_date' => get_field('start_date') . ' - ' . get_field('end_date'), 'documents' => $documents, 'start_date' => get_field('start_date'), 'end_date' => get_field('end_date'))))); ?>)"><?php the_title(); ?></h3>
    <div class="tender-item-excerpt">
        <?php the_excerpt() ?>
    </div>
</div>
