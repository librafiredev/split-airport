<?php
/**
 * The template for displaying all single posts.
 *
 * @package Split
 */

get_header(); ?>

<div class="container">
	<div class="row">
		<div id="primary" class="content-area col-md-8 col-sm-12">
			<main id="main" class="site-main" role="main">

				<?php
				while ( have_posts() ) :
					the_post();

					get_template_part( 'template-parts/content', 'single' );

					the_post_navigation();
					
					get_template_part( 'template-parts/post', 'author' );
					
				endwhile; // End of the loop.
				?>
				   
			</main><!-- #main -->
		</div><!-- #primary -->
		<?php get_sidebar(); ?>
	</div><!-- .row -->
</div><!-- .container -->

<?php get_footer(); ?>
