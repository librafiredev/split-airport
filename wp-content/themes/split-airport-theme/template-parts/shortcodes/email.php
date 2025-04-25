<?php extract($args); ?>

<div class="sc-card sc-card-email">
    <div class="sc-card__left">
        <?php echo file_get_contents(get_template_directory() . '/assets/images/email.svg'); ?>
    </div>
    <div class="sc-card__right">
        <p class="sc-card__label"><?php esc_html_e('Email'); ?></p>
        <p class="sc-card__value"><a href="mailto:<?php echo $email; ?>"><?php echo $email; ?></a></p>
    </div>
</div>