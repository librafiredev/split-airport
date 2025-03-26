<?php
/**
 * The template for displaying search results pages.
 *
 * @package Split
 */

get_header(); ?>

<div class="container">
	<div class="row">
		<section id="primary" class="content-area">
			<main id="main" class="site-main" role="main">
				<?php if ( have_posts() ) : ?>

					<header class="page-header">
						<h1 class="page-title">
							<?php
							/* translators: %s: search term */
							printf( esc_html__( 'Search Results for: %s', 'split' ), '<span>' . get_search_query() . '</span>' );
							?>
						</h1>
					</header><!-- .page-header -->

					<?php

					while ( have_posts() ) :
						the_post();
						/**
						 * Run the loop for the search to output the results.
						 * If you want to overload this in a child theme then include a file
						 * called content-search.php and that will be used instead.
						 */

						get_template_part( 'template-parts/content', 'search' );
					endwhile;
					?>

					<?php split_post_navigation(); ?>

				<?php else : ?>

					<?php get_template_part( 'template-parts/content', 'none' ); ?>

				<?php endif; ?>
			</main><!-- #main -->
		</section><!-- #primary -->
	</div><!-- .row -->
</div><!-- .container -->

<?php get_footer(); ?>
