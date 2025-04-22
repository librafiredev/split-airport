<?php

get_header();
?>

<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">

        <?php 
        get_template_part('template-parts/blocks/page-hero-generic', null, [
            'background' => get_field('tender_archive_hero', 'option'),
            'title' => split_archive_title(),
        ]);
        ?>
        <?php

        $term_id = get_queried_object()->term_id;

        $all_years = get_distinct_year_values_in('start_date', 'end_date', 'tender');
        $year = $_GET['tender_year'];
        if ($year) {
            $year = (int) $year;
        }
        ?>

        <section class="tenders-category-archive-wrapper">
            <div class="container">
                <form method="GET" class="tenders-category-year-from">
                    <select name="tender_year">
                        <option value=""><?php echo __('All Tenders'); ?></option>
                        <?php foreach ($all_years as $value) : ?>
                            <option value="<?php echo $value; ?>" <?php echo $year == $value ? 'selected' : '' ?> ><?php echo $value; ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
                <?php
                $dateNow = date('Y-m-d');
                $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
                
                $meta_query = array(
                    array(
                        'key'           => 'end_date',
                        'compare'       => '<=',
                        'value'         => $dateNow,
                        'type'          => 'DATE',
                    ),
                );
                
                if (!empty($year)) {
                    $year_start = $year . '-01-01';
                    $year_end = $year . '-12-31';
                    $meta_query['relation'] = 'AND';
                    $meta_query[] = array(
                        'relation' => 'OR',
                        array(
                            'relation' => 'AND',
                            array(
                                'key'           => 'end_date',
                                'compare'       => '<=',
                                'value'         => $year_end,
                                'type'          => 'DATE',
                            ),
                            array(
                                'key'           => 'end_date',
                                'compare'       => '>=',
                                'value'         => $year_start,
                                'type'          => 'DATE',
                            )
                        ),
                        array(
                            'relation' => 'AND',
                            array(
                                'key'           => 'start_date',
                                'compare'       => '<=',
                                'value'         => $year_end,
                                'type'          => 'DATE',
                            ),
                            array(
                                'key'           => 'start_date',
                                'compare'       => '>=',
                                'value'         => $year_start,
                                'type'          => 'DATE',
                            )
                        ),
                    );
                }
                $args = array(
                    'paged' => $paged,
                    'post_type' => 'tender',
                    'posts_per_page' => 9,
                    'post_status' => 'publish',
                    'order' => 'DESC',
                    'orderby' => 'meta_value',
                    'meta_query' => $meta_query,
                    'tax_query' => array(
                    array(
                        'taxonomy' => 'tender-category',
                        'field' => 'term_id',
                        'terms' => array( $term_id ),
                        'operator' => 'IN'
                    )
                )
                );
                $tender_query = new WP_Query($args);
                ?>
                    <?php if ($tender_query->have_posts()): ?>
                        <?php
                        while ($tender_query->have_posts()): $tender_query->the_post();
                            get_template_part('template-parts/posts/tender');
                        endwhile; ?>
                    <?php else:
                        echo __('There are no tenders matching this criteria.');
                    endif; ?>
                <?php
                
                cpt_flexible_pagination( $paged, $tender_query->max_num_pages );
                wp_reset_postdata();
                wp_reset_query();
                ?>
                        </section>
            </div>

	</main><!-- #main -->
</div><!-- #primary -->

<?php get_footer(); ?>
