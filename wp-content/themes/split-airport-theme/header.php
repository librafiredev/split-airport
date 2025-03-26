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