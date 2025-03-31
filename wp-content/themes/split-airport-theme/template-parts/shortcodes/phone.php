<?php extract($args); ?>

<div class="phone">
    <div class="phone__left">
        <?php echo file_get_contents(get_template_directory() . '/assets/images/phone.svg'); ?>
    </div>
    <div class="phone__right">
        <p class="phone__label"><?php esc_html_e('Telephone Number'); ?></p>
        <p class="phone__number"><a href="tel:<?php echo $number; ?>"><?php echo $number; ?></a></p>
    </div>
</div>