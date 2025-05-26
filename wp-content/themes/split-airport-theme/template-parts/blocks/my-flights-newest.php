<?php
extract($args);
if(!$newestFlight) return;
?>
<div class="my-flights-btn-item">
    <?php get_template_part('template-parts/blocks/my-flights-item', null, ['flight' => $newestFlight]); ?>
    <span class="my-flights-count"><?php if ($total > 1) echo '+' . $total - 1 ?></span>
</div>