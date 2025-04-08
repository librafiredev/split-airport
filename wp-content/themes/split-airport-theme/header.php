<?php
/**
 * The header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="content">
 *
 * @package Split
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
	<?php wp_head(); ?>
    <?php include_once(get_template_directory(). '/includes/fonts.php'); ?>
	<meta name="theme-color" content="#010101">
</head>

<body <?php body_class(); ?>>
<div id="page" class="hfeed site">
	<header id="masthead" class="site-header" role="banner">		
		
		<?php if( have_rows('airport_warnings', 'option') ): ?>
			<div class="site-warning-wrap">
				<div class="container">
					<div class="site-warning">
						<?php 
						$all_warnings = !empty(get_field('airport_warnings', 'option')) ? get_field('airport_warnings', 'option') : [];
						$first_item_type = !empty($all_warnings[0]['type']) ? $all_warnings[0]['type'] : 'info';
						?>
						<div class="site-warning-icographics shared-warning" data-warning="<?php echo $first_item_type; ?>">
							<div class="site-warning-icon-wrap">
								<?php echo file_get_contents(get_template_directory() . '/assets/images/warning.svg') ?>
							</div>
							<div class="site-warning-type-text">
								<?php echo get_field('airport_alert_text', 'option'); ?>
							</div>
						</div>

						<div class="site-warning-controls"><button type="button" class="site-warning-prev"></button> <span class="site-warning-controls-txt"><span class="site-warning-controls-current">1</span>&nbsp;<?php echo __('of'); ?>&nbsp;<span class="site-warning-controls-total"><?php echo count($all_warnings); ?></span></span><button type="button" class="site-warning-next"></button></div>

						<div class="site-warning-items">
							<div class="site-warning-items-inner">
								<?php while ( have_rows('airport_warnings', 'option') ) : the_row(); ?>
									<?php $type = get_sub_field('type'); ?>

									<div class="site-warning-item current-warning" data-warning="<?php echo $type; ?>">
										<div class="site-warning-icographics warning-item-icon">
											<div class="site-warning-icon-wrap">
												<?php echo file_get_contents(get_template_directory() . '/assets/images/warning.svg') ?>
											</div>
											<div class="site-warning-type-text"><?php echo get_field('airport_alert_text', 'option'); ?></div>
										</div>
										<div class="site-warning-right">
											<span class="site-warning-item-title"><?php the_sub_field('title'); ?></span>
											<div class="site-warning-item-text">
												<?php the_sub_field('text'); ?>
											</div>
										</div>
									</div>
									
								<?php endwhile; ?>

							</div>
						</div>

						<button type="button" class="site-warning-expand"></button>
					</div>
				</div>
				<div class="site-warning-overlay"></div>
			</div>
		<?php endif; ?>

		<div class="container logo-menu-wrapper">
			<div class="site-header-inner">

				<?php if( function_exists('get_field') ): ?>

					<?php $logo = get_field('logo','option'); ?>

					<?php if( $logo ): ?>

						<div class="site-branding-main-logo site-branding">
							<div class="site-title">
								<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
									<img src="<?php echo( esc_url( $logo ) ); ?>" alt="<?php echo( esc_attr( get_bloginfo( 'title' ) ) ); ?>"/>
								</a>
							</div>
						</div><!-- .site-branding -->
						
					<?php endif; ?>

				<?php endif; ?>
			
				<nav id="site-navigation" class="main-navigation" role="navigation">
					<?php if( function_exists('get_field') ): ?>

						<?php $logo = get_field('logo','option'); ?>

						<?php if( $logo ): ?>

							<div class="mobile-nav-logo">
								<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
									<img src="<?php echo( esc_url( $logo ) ); ?>" alt="<?php echo( esc_attr( get_bloginfo( 'title' ) ) ); ?>"/>
								</a>
							</div>
							
						<?php endif; ?>

					<?php endif; ?>
					
					<?php

						wp_nav_menu(
							array(
								'theme_location' 		=> 	'primary',
								'menu_id' 				=> 	'primary-menu',
								'menu_class' 			=> 	'main-header-menu',
								'container_class'		=>	'main-menu-container'
							)
						);

						?>
					
					<?php custom_language_selector(); ?>
					
				</nav><!-- #site-navigation -->

				<div class="menu-toggle-wrapper">
					<a href='#' class="menu-toggle" aria-controls="primary-menu" aria-expanded="false">
						<span></span>
						<span></span>
						<span></span>
					</a>
				</div>
			</div> <!-- /.row justify-content-between -->
		</div> <!-- /.container logo-menu-wrapper -->
	</header><!-- #masthead /.site-header -->

	<div id="content" class="site-content">

		