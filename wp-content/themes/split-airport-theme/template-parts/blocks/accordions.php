<?php
extract($args);
?>

<div class="accordions">

    <?php if($accordions_section_fields['title']): ?>

    <h3 class="accordions__title heading-third"><?php echo $accordions_section_fields['title']; ?> </h3>

    <?php endif; ?>

    <?php if ($accordions_section_fields['accordions']): ?>

    <div class="accordions__items">

    <?php foreach ($accordions_section_fields['accordions'] as $accordion): ?>

       <div class="accordions__item">
            <p class="accordions__item-title"><?php echo $accordion['title']; ?> <?php echo file_get_contents(get_template_directory() . '/assets/images/arrow-down.svg'); ?></p>

            <?php if($accordion['text']): ?>

            <div style="display: none;" class="accordions__item-text">
                <div class="accordions__item-text-inner entry-content"><?php echo $accordion['text']; ?></div>
            </div>

            <?php endif; ?>
       </div>

    <?php endforeach; ?>

    </div>

    <?php endif; ?>



</div>