<?php extract($args); ?>

<div class="phone-card">
    <div class="phone-card__left">
        <?php echo file_get_contents(get_template_directory() . '/assets/images/phone.svg'); ?>
    </div>
    <div class="phone-card__right">
        <p class="phone-card__label"><?php esc_html_e('Telephone Number'); ?></p>
        <p class="phone-card__number"><a href="tel:<?php echo $number; ?>"><?php echo $number; ?></a></p>
    </div>
</div>