<?php
extract($args);
?>

<div class="list">

    <?php if($list_section_fields['title']): ?>

    <h3 class="list__title heading-third"><?php echo $list_section_fields['title']; ?> </h3>

    <?php endif; ?>

    <?php if ($list_section_fields['items']): ?>

    <div class="list__items">

    <?php foreach ($list_section_fields['items'] as $item): ?>

       <div class="list__item">
            <p class="list__item-title"><?php echo $item['text']; ?></p>
       </div>

    <?php endforeach; ?>

    </div>

    <?php endif; ?>

</div>