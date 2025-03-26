<?php
/**
 * The template for displaying 404 pages (not found).
 *
 * @package Split
 */

get_header(); ?>

<div class="container">
    <div class="row">
        <div id="primary" class="content-area">
            <main id="main" class="site-main" role="main">

                <section class="error-404 not-found">
                    <header class="page-header">
                        <h1 class="page-title"><?php esc_html_e('Oops! That page can&rsquo;t be found.',
                                'split'); ?></h1>
                    </header><!-- .page-header -->

                    <div class="page-content">
                        <p><?php esc_html_e('It looks like nothing was found at this location. Maybe try one of the links below or a search?', 'split'); ?></p>
                    </div><!-- .page-content -->
                </section><!-- .error-404 -->
            </main><!-- #main -->
        </div><!-- #primary -->
    </div><!-- .row -->
</div><!-- .container -->

<?php get_footer(); ?>
