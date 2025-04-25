<?php extract($args); ?>

<div class="sc-card sc-card-phone">
    <div class="sc-card__left">
        <?php echo file_get_contents(get_template_directory() . '/assets/images/phone.svg'); ?>
    </div>
    <div class="sc-card__right">
        <p class="sc-card__label"><?php esc_html_e('Telephone Number'); ?></p>
        <p class="sc-card__value"><a href="tel:<?php echo $number; ?>"><?php echo $number; ?></a></p>
    </div>
</div>