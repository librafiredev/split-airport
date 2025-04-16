<?php 
extract($args);
?>

<div class="tc-grid-item <?php echo $class; ?> tc-grid-item-type-<?php echo $image_type; ?>">
    <a href="<?php echo esc_url($link_url); ?>" target="<?php echo esc_attr($link_target); ?>">
        <div class="tc-grid-item-bg" <?php echo ( $image_type == 'background' && $image )? 'style="background-image:url('.wp_get_attachment_image_url($image['ID'], 'medium_large').');"': ''; ?> ></div>

        <?php if( $image_type != 'background' && $image ): ?>

            <div class="tc-grid-item-img-wrap tc-grid-item-<?php echo $image_type; ?>">
                <?php echo ( isset($image['ID']) )? wp_get_attachment_image($image['ID'], 'full'):''; ?>
            </div><!-- .tc-grid-item-image -->
            
        <?php endif; ?>

        <div class="tc-grid-item-text">
            <span><?php echo esc_html($link_title); ?></span>
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/arrow-white.svg" alt="arrow-white" class="tc-grid-item-arrow" />
        </div><!-- .tc-grid-item-text -->

    </a>
</div><!-- .tc-grid-item -->
