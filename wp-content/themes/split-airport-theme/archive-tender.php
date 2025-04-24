<?php

get_header();
?>

<div id="primary" class="content-area">
    <?php get_template_part('template-parts/blocks/tender-modal-scaffold'); ?>
    
	<main id="main" class="site-main" role="main">

        <?php 
        get_template_part('template-parts/blocks/page-hero-generic', null, [
            'background' => get_field('tender_archive_hero', 'option'),
            'title' => split_archive_title(),
        ]);
        ?>

        <section class="tenders-wrapper">
            <div class="container">
                <div class="tenders-inner">
                    <?php
                    $categorized_posts = array();
                    ?>
                    <?php if (have_posts()): ?>
                        <?php
                        while (have_posts()): the_post();
                            $terms = get_the_terms( get_the_ID(), 'tender-category' );

                            if ( !($terms && ! is_wp_error( $terms )) ) {
                                $fallback_term = new stdClass();
                                $fallback_term->term_id = 0;
                                $fallback_term->name = '';
                                $terms = [
                                    $fallback_term,
                                ];
                            }
                            
                            foreach ( $terms as $term ) :
                                if (empty($categorized_posts[$term->term_id])) {
                                    $categorized_posts[$term->term_id]['posts'] = [$post];
                                    $categorized_posts[$term->term_id]['term'] = $term;
                                } else {
                                    $categorized_posts[$term->term_id]['posts'][] = $post;
                                }
                            endforeach;
                        endwhile; ?>
                    <?php else:
                        echo __('There are no tenders matching this criteria.');
                    endif; 


                    foreach ($categorized_posts as $category_id => $category) {
                        ?>
                        <div class="tenders-cat-wrap">
                            <h2 class="tenders-cat-title"><?php echo $category['term']->name; ?></h2>
                            <?php
                            foreach ($category['posts'] as $post) {
                                setup_postdata( $post );
                                get_template_part('template-parts/posts/tender');
                            }
                            ?>
                            <?php if (!empty($category['term']->name)) : ?>
                                
                                <?php 
                                get_template_part('template-parts/shortcodes/button', null, [
                                    'title' => get_finished_tenders_title($category['term']->name),
                                    'url' => get_term_link( $category['term'], 'tender-category' ),
                                    'newTab' => "no",
                                ]);
                                ?>
                            <?php endif; ?>
                        </div>
                        <?php
                    }

                    cpt_flexible_pagination( $paged, $wp_query->max_num_pages );
                    wp_reset_postdata();
                    wp_reset_query();
                    ?>

                    </section>
                </div>
            </div>

	</main><!-- #main -->
</div><!-- #primary -->

<?php get_footer(); ?>
