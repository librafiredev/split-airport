<?php
$logo = get_the_post_thumbnail();
$url = get_field('url', get_the_ID());
$type = get_field('type', get_the_ID());
?>

<div class="airline <?php echo $type; ?>">

    <?php if ($url): ?>

        <a
            <?php if (!empty($url)): ?>
            target="<?php echo esc_attr($url['target']); ?>"
            <?php endif; ?>
            class="airlline__url"
            href="<?php echo esc_url($url['url']); ?>">
        </a>

    <?php endif; ?>

    <div class="airline__left">
        <?php if ($logo): ?>

            <div class="airline__logo">
                <?php echo $logo; ?>
            </div>

        <?php endif;  ?>

        <h3 class="heading-third"><?php the_title(); ?></h3>
    </div>

    <div class="airline__right">

        <?php if ($type === 'unsupported'): ?>

            <p class="airline__text"><?php esc_html_e('Unsupported, visit airlines website', 'split-airport');
                                        echo file_get_contents(get_template_directory() . '/assets/images/link-arrow.svg'); ?></p>

        <?php else:
            echo file_get_contents(get_template_directory() . '/assets/images/arrow-right.svg');
        endif;  ?>

    </div>
</div>