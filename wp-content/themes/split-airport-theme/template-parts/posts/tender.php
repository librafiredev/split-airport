<div class="tender-item">
    <div class="tender-item-dates">
        <?php the_field('start_date'); ?> - <?php the_field('end_date'); ?>
    </div>
    <h3 class="tender-item-title"><?php the_title(); ?></h3>
    <div class="tender-item-excerpt">
        <?php the_excerpt() ?>
    </div>
</div>
