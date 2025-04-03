<?php
/*
* Block Name: Rent Car
* Post Type: page 
*/

if (isset($block['data']['preview_image_help'])) :
    echo '<img src="' . $block['data']['preview_image_help'] . '" style="width:100%; height:auto;">';
else:
    $companies = get_field('companies');
?>

    <section class="rent-car">
        <div class="container">
            <div class="rent-car__inner">

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

                                        <h3 class="heading-third"><?php echo $company['name']; ?></h3>

                                    <?php endif; ?>

                                    <?php if ($company['phone']): ?>

                                        <a href="tel:<?php echo $company['phone']; ?>" class="rent-car__item-phone"><?php echo $company['phone']; ?></a>

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