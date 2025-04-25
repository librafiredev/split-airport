<?php
/*
* Block Name: Rent Car
* Post Type: page 
*/

if (isset($block['data']['preview_image_help'])) :
    echo '<img src="' . $block['data']['preview_image_help'] . '" style="width:100%; height:auto;">';
else:
    $companies = get_field('companies');
    $should_prefix = get_field('should_add_prefixes');
?>

    <section class="rent-car">
        <div class="container">
            <div class="rent-car__inner">
                <?php if ( get_field('title') ) : ?>
                    <h2 class="rent-car-title"><?php the_field('title') ?></h2>
                <?php endif; ?>

                <?php if ($companies): ?>

                    <div class="rent-car__items">

                        <?php foreach ($companies as $company): ?>

                            <div class="rent-car__item">

                                <?php if ($company['logo']): ?>

                                    <div class="rent-car__item-image">
                                        <?php echo wp_get_attachment_image($company['logo'], 'medium'); ?>
                                    </div>

                                <?php endif; ?>

                                <div class="rent-car__item-info">

                                    <?php if ($company['name']): ?>

                                        <h3 class="rent-car__item-title"><?php echo $company['name']; ?></h3>

                                    <?php endif; ?>

                                    <?php if (!empty($company['info'])): ?>

                                        <span class="rent-car__item-phone"><?php echo $company['info']; ?></span>

                                    <?php endif; ?>

                                    <?php if ($company['phone']): ?>

                                        <div><?php echo $should_prefix ? __('Tel.:') : ''; ?> <a href="tel:<?php echo $company['phone']; ?>" class="rent-car__item-phone"><?php echo $company['phone']; ?></a></div>

                                    <?php endif; ?>

                                    <?php if (!empty($company['fax'])): ?>

                                        <div><?php echo $should_prefix ? __('Fax.:') : ''; ?> <a href="tel:<?php echo $company['fax']; ?>" class="rent-car__item-fax"><?php echo $company['fax']; ?></a></div>

                                    <?php endif; ?>

                                    <?php if (!empty($company['mobile'])): ?>

                                        <div><?php echo $should_prefix ? __('Mob.:') : ''; ?> <a href="tel:<?php echo $company['mobile']; ?>" class="rent-car__item-mobile"><?php echo $company['mobile']; ?></a></div>

                                    <?php endif; ?>


                                    <?php if ($company['email']): ?>

                                        <a href="mailto:<?php echo $company['email']; ?>" class="rent-car__item-mail"><?php echo $company['email']; ?></a>

                                    <?php endif; ?>

                                    <?php if ($company['site']): ?>

                                        <a href="<?php echo $company['site']; ?>" target="_blank" class="rent-car__item-site"><?php echo parse_url($company['site'], PHP_URL_HOST ); ?></a>

                                    <?php endif; ?>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    </div>

                <?php endif; ?>

            </div>
        </div>
    </section><!-- .rent-car-->

<?php endif; ?>