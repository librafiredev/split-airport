<?php extract($args); ?>

<div class="sc-card sc-card-address">
    <div class="sc-card__left">
        <?php echo file_get_contents(get_template_directory() . '/assets/images/pin.svg'); ?>
    </div>
    <div class="sc-card__right">
        <p class="sc-card__label"><?php esc_html_e('Address'); ?></p>
        <p class="sc-card__value">
            <?php if ( !empty($link) ) : ?>
                <a href="<?php echo $link; ?>" target="_blank"><?php echo $address; ?></a>
            <?php else: ?>
                <span><?php echo $address; ?></span>
            <?php endif; ?>
        </p>
    </div>
</div>