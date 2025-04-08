<?php
$logo = get_the_post_thumbnail();
$url = get_field('url', get_the_ID());
$type = get_field('type', get_the_ID());
?>

<div class="airline <?php echo $type; ?>">

    <div class="airline__left">
        <?php if ($logo): ?>

            <div class="airline__logo">
                <?php echo $logo; ?>
            </div>

        <?php endif;  ?>

        <div>
            <h3 class="airline__title"><?php the_title(); ?></h3>
            <?php if ($type === 'unsupported'): ?>
                <a
                    <?php if (!empty($url)): ?>
                    target="<?php echo esc_attr($url['target']); ?>"
                    <?php endif; ?>
                    class="airlline__url-mobile"
                    href="<?php echo esc_url($url['url']); ?>">
                    <?php esc_html_e('Unsupported, visit airlines website', 'split-airport'); ?>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="airline__right">
        <?php if ($url): ?>
            <a
                <?php if (!empty($url)): ?>
                target="<?php echo esc_attr($url['target']); ?>"
                <?php endif; ?>
                class="airlline__url"
                href="<?php echo esc_url($url['url']); ?>">
            
            <?php if ($type === 'unsupported'): ?>

                <p class="airline__text"><span class="airline-external"><?php esc_html_e('Unsupported, visit airlines website', 'split-airport'); ?></span><?php echo file_get_contents(get_template_directory() . '/assets/images/link-arrow.svg'); ?></p>

            <?php else:
                echo file_get_contents(get_template_directory() . '/assets/images/arrow-right.svg');
            endif;  ?>

            </a>
        <?php endif; ?>
    </div>
</div>