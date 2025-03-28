<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after
 *
 * @package Split
 */

?>
			</div><!-- #content -->

			<footer id="colophon" class="site-footer" role="contentinfo">
				<div class="footer-bg">
					<div class="footer-bg-svg-wrap">
						<svg width="1440" height="517" viewBox="0 0 1440 517" fill="none" xmlns="http://www.w3.org/2000/svg" class="footer-bg-svg"><path d="M487.105 0H0V517H1440V68.7394H619.322C613.098 68.7394 606.993 67.0309 601.673 63.8L504.754 4.93946C499.434 1.70856 493.329 0 487.105 0Z" fill="#F3F8FC"/></svg>
					</div>
				</div>

				<?php $logo = get_field('logo','option'); ?>

				<?php if( $logo ): ?>

					<div class="footer-top">
						<div class="container">
							<div class="footer-logo">
								<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
									<img src="<?php echo( esc_url( $logo ) ); ?>" alt="<?php echo( esc_attr( get_bloginfo( 'title' ) ) ); ?>"/>
								</a>
							</div><!-- .footer-logo -->
						</div><!-- .container -->
					</div><!-- .footer-top -->

				<?php endif; ?>

				<div class="widget-wrapper">
					<div class="container">
						<div class="row footer-widgets-wrapper">
							<?php get_template_part('template-parts/footer', 'widgets'); ?>
						</div>
					</div>
				</div>

				<?php

				if( function_exists('get_field') ):
					$footer_copyright_text = get_field('footer_copyright_text','option');
				else:
					$footer_copyright_text = '';
				endif;

				if( $footer_copyright_text ): ?>
					<div class="site-info">
						<div class="container">
							<div class="footer-copyright"><?php echo $footer_copyright_text; ?></div>
						</div>
					</div><!-- .site-info -->
				<?php endif; ?>
			</footer><!-- #colophon -->
		</div><!-- #page -->
		<!-- W3TC-include-css -->
		<?php wp_footer(); ?>
	<!-- W3TC-include-js-head -->
	</body>
</html>
