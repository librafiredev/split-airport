<?php
$logo = get_the_post_thumbnail();
$url = get_field('url', get_the_ID());
$type = get_field('type', get_the_ID());
$airlineUnsupportedText = get_field('airline_unsupported_text', 'options');
$opensText = get_field('opens', get_the_ID());
$closesText = get_field('closes', get_the_ID());
?>

<?php if (!empty($url)): ?>
    <a href="<?php echo esc_url($url['url']); ?>"
        target="<?php echo esc_attr($url['target']); ?>"
        class="airline <?php echo $type; ?>">
    <?php else: ?>
        <div class="airline <?php echo $type; ?>">
        <?php endif; ?>

        <div class="airline__left">
            <?php if ($logo): ?>
                <div class="airline__logo">
                    <?php echo $logo; ?>
                </div>
            <?php endif; ?>

            <div>
                <h3 class="airline__title"><?php the_title(); ?></h3>
                <span class="airlline__subtitle">
                    <?php if ($type === 'unsupported'): ?>
                        <?php echo $airlineUnsupportedText; ?>
                    <?php elseif ($type === 'supported'): ?>
                        <?php if ($opensText) : ?>
                            <span class="airlline__subtitle-item">
                                <?php echo $opensText ?>
                            </span>
                        <?php endif; ?>
                        <?php if ($closesText) : ?>
                            <span class="airlline__subtitle-item">
                                <?php echo $closesText ?>
                            </span>
                        <?php endif; ?>
                    <?php endif; ?>
                </span>
            </div>
        </div>

        <div class="airline__right">
            <div class="airline__url">
                <?php if ($type === 'unsupported'): ?>
                    <p class="airline__text"><?php echo file_get_contents(get_template_directory() . '/assets/images/link-arrow.svg'); ?></p>
                <?php else: ?>
                    <?php echo file_get_contents(get_template_directory() . '/assets/images/arrow-right.svg'); ?>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($url)): ?>
    </a>
<?php else: ?>
    </div>
<?php endif; ?>