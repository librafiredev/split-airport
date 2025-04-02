<?php
/*
* Block Name: Parking
* Post Type: page 
*/

if (isset($block['data']['preview_image_help'])) :
    echo '<img src="' . $block['data']['preview_image_help'] . '" style="width:100%; height:auto;">';
else:
    $title = get_field('title');
    $parkings = get_field('parkings');
    $bottom_information = get_field('bottom_information');
    $download_section = get_field('download_section');

?>

    <section class="parking">
        <div class="container">
            <div class="parking__inner">

                <?php if ($title): ?>

                    <h3 class="heading-third"><?php echo $title; ?></h3>

                <?php endif; ?>

                <?php if ($parkings): ?>

                    <div class="parking__items">

                        <?php foreach ($parkings as $parking): ?>

                            <div class="parking__item">
                                <div class="parking__item-top">

                                    <?php if ($parking['name']): ?>

                                        <h2 class="heading-secondary"><?php echo $parking['name']; ?></h2>

                                    <?php endif; ?>

                                    <?php if ($parking['button']): ?>

                                        <a
                                            <?php if (!empty($parking['button'])): ?>
                                            target="<?php echo esc_attr($parking['button']['target']); ?>"
                                            <?php endif; ?>
                                            class="parking__item-button"
                                            href="<?php echo esc_url($parking['button']['url']); ?>">
                                            <?php
                                            echo esc_html($parking['button']['title']) . file_get_contents(get_template_directory() . '/assets/images/link-arrow.svg');
                                            ?>
                                        </a>

                                    <?php endif; ?>
                                </div>

                                <?php if ($parking['vehicles']): ?>

                                    <div class="parking__item-vehicles">

                                        <?php foreach ($parking['vehicles'] as $vehicle): ?>

                                            <div class="parking__item-vehicle">

                                                <?php if ($vehicle['name']): ?>

                                                    <p class="parking__item-vehicle-name"><?php echo $vehicle['name']; ?></p>

                                                <?php endif; ?>

                                                <?php if ($vehicle['rates']): ?>

                                                    <div class="parking__item-vehicle-rates">

                                                        <?php foreach ($vehicle['rates'] as $rate): ?>

                                                            <div class="parking__item-vehicle-rate">

                                                                <?php if ($rate['time']): ?>

                                                                    <p class="parking__item-vehicle-rate-time"><?php echo $rate['time']; ?></p>

                                                                <?php endif; ?>

                                                                <?php if ($rate['value']): ?>

                                                                    <p class="parking__item-vehicle-rate-value"><?php echo $rate['value']; ?></p>

                                                                <?php endif; ?>
                                                            </div>

                                                        <?php endforeach; ?>
                                                    </div>

                                                <?php endif; ?>

                                            </div>

                                        <?php endforeach; ?>
                                    </div>

                                <?php endif; ?>

                            </div>

                        <?php endforeach; ?>
                    </div>

                <?php endif; ?>

                <?php if ($bottom_information): ?>

                    <div class="parking__bottom-info">

                        <?php if ($bottom_information['text_left'] || $bottom_information['text_middle']): ?>

                            <div class="parking__bottom-info-wrapper">

                                <?php if ($bottom_information['text_left']): ?>

                                    <p class="parking__bottom-info-left"><?php echo $bottom_information['text_left'];  ?></p>

                                <?php endif; ?>

                                <?php if ($bottom_information['text_middle']): ?>

                                    <p class="parking__bottom-info-middle"><?php echo $bottom_information['text_middle'];  ?></p>

                                <?php endif; ?>
                            </div>

                        <?php endif;
                        ?>

                        <?php if ($bottom_information['text_right']): ?>

                            <p class="parking__bottom-info-right"><?php echo $bottom_information['text_right'];  ?></p>

                        <?php endif; ?>
                    </div>

                <?php endif; ?>

                <?php if ($download_section): ?>

                    <div class="parking__download">

                        <?php if ($download_section['text']): ?>

                            <div class="parking__download-text">
                                <?php echo $download_section['text']; ?>
                            </div>

                        <?php endif; ?>

                        <?php if ($download_section['file']): ?>

                            <div class="parking__download-file">
                                <div class="parking__download-file-icon">
                                    <?php echo file_get_contents(get_template_directory() . '/assets/images/fileIcon.svg'); ?>
                                </div>

                                <?php

                                $file_path = get_attached_file($download_section['file']);
                                $file_url = wp_get_attachment_url($download_section['file']);
                                $file_size = round(((filesize($file_path)) / 1024 / 1024), 2);

                                ?>

                                <div class="parking__download-file-info">
                                    <p class="parking__download-file-name"><?php echo ucfirst(basename($file_path)); ?></p>
                                    <p class="parking__download-file-type"><?php echo strtoupper(pathinfo($file_path, PATHINFO_EXTENSION)) . ', ' . $file_size . ' ' . 'MB'; ?></p>
                                </div>

                                <a download href="<?php echo esc_url($file_url); ?>"><?php esc_html_e('Download', 'split-aritport'); ?></a>
                            </div>

                        <?php endif; ?>
                    </div>

                <?php endif; ?>

            </div>
        </div>
    </section><!-- .parking-->

<?php endif; ?>