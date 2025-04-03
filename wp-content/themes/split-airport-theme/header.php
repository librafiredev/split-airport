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
		
		<div class="site-warning-wrap">
			<div class="container">
				<div class="site-warning">
					<!-- TODO: figure out what to do with colors for this icon (when warnings are open and there are multiple warnings with different type) -->
					<div class="site-warning-icographics site-warning-danger">
						<div class="site-warning-icon-wrap">
							<svg width="14" height="12" viewBox="0 0 14 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12.9473 9.27954L8.13649 0.96704C8.02108 0.767967 7.85539 0.602716 7.656 0.487841C7.45662 0.372966 7.23055 0.3125 7.00045 0.3125C6.77034 0.3125 6.54427 0.372966 6.34489 0.487841C6.14551 0.602716 5.97981 0.767967 5.8644 0.96704V0.967467L1.05361 9.27954C0.938105 9.47902 0.877177 9.7054 0.876954 9.9359C0.876731 10.1664 0.937221 10.3929 1.05234 10.5926C1.16745 10.7923 1.33314 10.9582 1.53271 11.0735C1.73229 11.1888 1.95872 11.2495 2.18923 11.2496H11.8117C12.0422 11.2495 12.2686 11.1888 12.4682 11.0735C12.6678 10.9582 12.8334 10.7923 12.9486 10.5926C13.0637 10.3929 13.1242 10.1664 13.1239 9.9359C13.1237 9.7054 13.0628 9.47902 12.9473 9.27954ZM6.56252 4.6875C6.56252 4.57147 6.60861 4.46019 6.69066 4.37814C6.77271 4.29609 6.88399 4.25 7.00002 4.25C7.11605 4.25 7.22733 4.29609 7.30938 4.37814C7.39143 4.46019 7.43752 4.57147 7.43752 4.6875V6.875C7.43752 6.99103 7.39143 7.10231 7.30938 7.18436C7.22733 7.26641 7.11605 7.3125 7.00002 7.3125C6.88399 7.3125 6.77271 7.26641 6.69066 7.18436C6.60861 7.10231 6.56252 6.99103 6.56252 6.875V4.6875ZM7.00034 9.50016C6.87055 9.50016 6.74367 9.46167 6.63575 9.38956C6.52783 9.31745 6.44371 9.21496 6.39404 9.09504C6.34437 8.97513 6.33138 8.84318 6.3567 8.71588C6.38202 8.58858 6.44452 8.47165 6.5363 8.37987C6.62808 8.28809 6.74501 8.22559 6.87231 8.20027C6.99961 8.17495 7.13156 8.18794 7.25147 8.23761C7.37139 8.28728 7.47388 8.3714 7.54599 8.47931C7.6181 8.58723 7.65659 8.71411 7.65659 8.84391C7.65658 9.01795 7.58744 9.18487 7.46437 9.30794C7.3413 9.43101 7.17439 9.50015 7.00034 9.50016Z" fill="white"/></svg>
						</div>
						<div class="site-warning-type-text">Alerts</div>
					</div>
					

					<div class="site-warning-controls"><button type="button" class="site-warning-prev"></button> <span class="site-warning-controls-txt"><span class="site-warning-controls-current">1</span>&nbsp;<?php echo __('of'); ?>&nbsp;<span class="site-warning-controls-total"><!-- TODO: echo total from db here --></span></span><button type="button" class="site-warning-next"></button></div>
						

					<div class="site-warning-items">
						<div class="site-warning-items-inner">

							<!-- TODO: REPLACE PLACEHOLDER ITEMS -->
							<div class="site-warning-item site-warning-info current-warning">
								<!-- TODO: add apropriate class based on warning type (for each item) -->
								<span class="site-warning-item-title">today, feb 19</span>
								<div class="site-warning-item-text">
									Due to planned industrial action, there will be no flight from London to or from Split on Thursday 27 March
								</div>
							</div>
							<div class="site-warning-item site-warning-danger">
								<span class="site-warning-item-title">feb, 18</span>
								<div class="site-warning-item-text">
									Due to planned works, the M25 will be shut between Junctions 10 (Wisley Interchange) and 11 (Chertsey) from 9pm on Friday 21 March, until 6am on Monday 24 March. Passengers planning to use this part of the M25 should allow for extra time when travelling to Heathrow during this period. For more information, please visit National Highways’ website.
								</div>
							</div>
							<div class="site-warning-item site-warning-warning">
								<span class="site-warning-item-title">feb, 17</span>
								<div class="site-warning-item-text">
									Due to planned works, there will be no flight from Paris to or from Split on Thursday 29 March
								</div>
							</div>

						</div>
					</div>

					<button type="button" class="site-warning-expand"></button>
				</div>
			</div>
			<div class="site-warning-overlay"></div>
		</div>
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

					<div class="language-menu">
						<div class="current-lang">Eng</div>
						<ul>
							<li><a href="#">English</a></li>
							<li><a href="#">Hrvatski</a></li>
						</ul>

					</div>
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