<?php
/**
 * The main template file.
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package Split
 */

get_header(); ?>

<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">

		<?php $index_post = get_queried_object(); ?>

		<?php if( isset($index_post->post_content) && has_blocks( $index_post->post_content ) ): ?>

			<?php $post = $index_post; ?>

			<?php get_template_part( 'template-parts/content', 'page' ); ?>
			
		<?php else: ?>

			<?php if ( have_posts() ) : ?>

				<?php /* Start the Loop */ ?>
				<?php while ( have_posts() ) : ?>
					<?php

					the_post();

					/*
						* Include the Post-Format-specific template for the content.
						* If you want to override this in a child theme, then include a file
						* called content-___.php (where ___ is the Post Format name) and that will be used instead.
						*/
					get_template_part( 'template-parts/content', get_post_format() );

					?>
				<?php endwhile; ?>

				<?php split_post_navigation(); ?>

			<?php else : ?>

				<?php get_template_part( 'template-parts/content', 'none' ); ?>

			<?php endif; ?>
			
		<?php endif; ?>

	</main><!-- #main -->
</div><!-- #primary -->

<?php get_footer(); ?>
