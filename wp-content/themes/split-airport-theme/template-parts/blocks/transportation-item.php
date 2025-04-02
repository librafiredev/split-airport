<?php extract($args);
?>

<div class="transportation-item <?php echo $box['style']; ?>">


    <?php if ($box['style'] === 'image' && $box['image']): ?>

        <div class="transportation__item-image">
            <?php echo wp_get_attachment_image($box['image'], 'large'); ?>
        </div>

    <?php endif; ?>

    <?php if ($box['title']): ?>

        <h2 class="heading-secondary"><?php echo $box['title']; ?></h2>

    <?php endif;  ?>

    <?php if ($box['content']): ?>

        <div class="transportation__item-content"><?php echo $box['content']; ?></div>

    <?php endif;  ?>

    <?php if ($box['buttons'] && ($box['buttons']['button_1'] || $box['buttons']['button_2'])):


    ?>

        <div class="transportation__item-buttons">
            <?php if (isset($box['buttons']) && is_array($box['buttons'])): ?>

                <?php

                if (
                    isset($box['buttons']['button_1']) && is_array($box['buttons']['button_1']) &&
                    !empty($box['buttons']['button_1']['url'])
                ): ?>
                    <a
                        <?php if (!empty($box['buttons']['button_1']['target'])): ?>
                        target="<?php echo esc_attr($box['buttons']['button_1']['target']); ?>"
                        <?php endif; ?>
                        class="transportation__item-button <?php echo esc_attr($box['buttons']['button_1_style'] ?? ''); ?>"
                        href="<?php echo esc_url($box['buttons']['button_1']['url']); ?>">

                        <?php
                        $style_1 = $box['buttons']['button_1_style'] ?? '';
                        $svg_path_1 = ($style_1 === 'phone') ? '/assets/images/phone.svg' : '/assets/images/link-arrow.svg';
                        $full_svg_path_1 = get_template_directory() . $svg_path_1;

                        if (file_exists($full_svg_path_1)) {
                            echo file_get_contents($full_svg_path_1);
                        }

                        echo esc_html($box['buttons']['button_1']['title'] ?? '');
                        ?>
                    </a>
                <?php endif; ?>


                <?php

                if (
                    isset($box['buttons']['button_2']) && is_array($box['buttons']['button_2']) &&
                    !empty($box['buttons']['button_2']['url'])
                ): ?>
                    <a
                        <?php if (!empty($box['buttons']['button_2']['target'])): ?>
                        target="<?php echo esc_attr($box['buttons']['button_2']['target']); ?>"
                        <?php endif; ?>
                        class="transportation__item-button <?php echo esc_attr($box['buttons']['button_2_style'] ?? ''); ?>"
                        href="<?php echo esc_url($box['buttons']['button_2']['url']); ?>">

                        <?php
                        $style_2 = $box['buttons']['button_2_style'] ?? '';
                        $svg_path_2 = ($style_2 === 'phone') ? '/assets/images/phone.svg' : '/assets/images/link-arrow.svg';
                        $full_svg_path_2 = get_template_directory() . $svg_path_2;

                        if (file_exists($full_svg_path_2)) {
                            echo file_get_contents($full_svg_path_2);
                        }

                        echo esc_html($box['buttons']['button_2']['title'] ?? '');
                        ?>
                    </a>
                <?php endif; ?>

            <?php endif; ?>



        </div>

    <?php endif; ?>


    <?php if ($box['links_title'] || $box['links']): ?>

        <div class="transportation__item-links">

            <?php if ($box['links_title']): ?>

                <h3 class="heading-third"><?php echo $box['links_title']; ?></h3>

            <?php endif; ?>

            <?php
            if ($box['links']):

                foreach ($box['links'] as $link):

            ?>

                    <div class="transportation__item-links-item">

                        <?php if ($link['link']): ?>

                            <a <?php if (isset($link['link']['target']) && $link['link']['target']) echo "target='" . $link['link']['target'] . "'" ?> href="<?php echo $link['link']['url'] ?>" class="transportation__item-links-item-link"></a>

                        <?php endif; ?>
                        <div class="transportation__item-links-item-left">

                            <?php if ($link['link']): ?>

                                <p class="transportation__item-links-item-main-text"><?php echo $link['link']['title']; ?></p>

                            <?php endif; ?>

                            <?php if ($link['text']): ?>

                                <p class="transportation__item-links-item-text"><?php echo $link['text']; ?></p>

                            <?php endif; ?>

                        </div>
                        <div class="transportation__item-links-item-right">
                            <?php echo file_get_contents(get_template_directory() . '/assets/images/link-arrow.svg'); ?>
                        </div>
                    </div>

            <?php

                endforeach;
            endif; ?>

        </div>

    <?php endif; ?>

    <?php if ($box['bottom_title'] || $box['bottom_content']): ?>

        <div class="transportation__item-bottom">

            <?php if ($box['bottom_title']): ?>

                <h3 class="heading-third"><?php echo $box['bottom_title']; ?></h3>

            <?php endif; ?>

            <?php if ($box['bottom_content']): ?>

                <div class="transportation__item-bottom-content"><?php echo $box['bottom_content']; ?></div>

            <?php endif; ?>

        </div>

    <?php endif; ?>

</div>