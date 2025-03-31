<?php
extract($args);
?>

<div class="accordions">

    <?php if($accordions_section_fields['title']): ?>

    <p class="accordions__title"><?php echo $accordions_section_fields['title']; ?> </p>

    <?php endif; ?>

    <?php if ($accordions_section_fields['accordions']): ?>

    <div class="accordions__items">

    <?php foreach ($accordions_section_fields['accordions'] as $accordion): ?>

       <div class="accordions__item">
            <p class="accordions__item-title"><?php echo $accordion['title']; ?> <?php echo file_get_contents(get_template_directory() . '/assets/images/arrow-down.svg'); ?></p>

            <?php if($accordion['text']): ?>

            <div style="display: none;" class="accordions__item-text">
                <?php echo $accordion['text']; ?>
            </div>

            <?php endif; ?>
       </div>

    <?php endforeach; ?>

    </div>

    <?php endif; ?>



</div>