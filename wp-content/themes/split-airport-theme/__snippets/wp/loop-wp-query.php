<?php
$args = array(
	'post_type' 		=> 'book',
	'posts_per_page'	=> -1,
	'meta_query'    => array(
        array(
            'key'       => 'system_power_supply',
            'value'     => array('single', 'redundant'),
            'compare'   => 'IN',
        )
    )
);

$query = new WP_Query( $args );
?>

<?php if ( $query->have_posts() ) : ?>
    <?php while ( $query->have_posts() ) : $query->the_post(); ?>

        <?php the_title(); ?>
        <?php the_content(); ?>
        <?php the_field('my_custom_field'); ?>

    <?php endwhile; ?>
<?php endif; ?>

<?php wp_reset_query(); ?>