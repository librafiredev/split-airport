<?php
extract($args);
?>

<section class="page-hero-wrapper">

    <div class="page-hero-wrapper-inner">
        <div class="page-hero-img-wrap">
            <div class="page-hero-img-pattern" style="background-image: url(<?php echo get_template_directory_uri() ?>/assets/images/hero-pattern.png);"></div>
            <?php if ($background) : ?>
                <?php echo wp_get_attachment_image($background, 'full',); ?>
            <?php endif; ?>
        </div>
        <div class="page-hero-cutout-wrap">
            <svg version="1.2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1439 71" width="1439" height="71" class="page-hero-cutout">
                <path d="m682.7 0h-682.7v716h1445v-645h-611.4c-18.5 0-36.5-6.1-51.3-17.3l-48.3-36.5c-14.8-11.2-32.8-17.2-51.3-17.2z" />
            </svg>
        </div>

        <?php get_template_part('template-parts/blocks/my-flights'); ?>
    </div>


    <div class="container">

        <?php if ($title): ?>
        
            <h1 class="page-hero-title"><?php echo $title; ?></h1>

        <?php endif; ?>

        <?php if ($working_hours): ?>

            <p class="page-hero-working-hours"><?php echo $working_hours; ?></p>

        <?php endif; ?>
    </div>
</section><!-- .page-hero-wrapper-->
