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
					<div class="site-warning-icographics shared-warning" data-warning="info">
						<div class="site-warning-icon-wrap">
							<?php echo file_get_contents(get_template_directory() . '/assets/images/warning.svg') ?>
						</div>
						<div class="site-warning-type-text">Alerts</div>
					</div>

					<div class="site-warning-controls"><button type="button" class="site-warning-prev"></button> <span class="site-warning-controls-txt"><span class="site-warning-controls-current">1</span>&nbsp;<?php echo __('of'); ?>&nbsp;<span class="site-warning-controls-total"><!-- TODO: echo total from db here --></span></span><button type="button" class="site-warning-next"></button></div>
						

					<div class="site-warning-items">
						<div class="site-warning-items-inner">

							<!-- TODO: REPLACE PLACEHOLDER ITEMS -->

							<!-- TODO: add apropriate class based on warning type (for each item) -->
							<div class="site-warning-item current-warning" data-warning="info">
								<div class="site-warning-icographics warning-item-icon">
									<div class="site-warning-icon-wrap">
										<?php echo file_get_contents(get_template_directory() . '/assets/images/warning.svg') ?>
									</div>
									<div class="site-warning-type-text">Alerts</div>
								</div>
								<div class="site-warning-right">
									<span class="site-warning-item-title">today, feb 19</span>
									<div class="site-warning-item-text">
										Due to planned industrial action, there will be no flight from London to or from Split on Thursday 27 March
									</div>
								</div>
							</div>
							<div class="site-warning-item" data-warning="danger">
								<div class="site-warning-icographics warning-item-icon">
									<div class="site-warning-icon-wrap">
										<?php echo file_get_contents(get_template_directory() . '/assets/images/warning.svg') ?>
									</div>
									<div class="site-warning-type-text">Alerts</div>
								</div>
								<div class="site-warning-right">
									<span class="site-warning-item-title">feb, 18</span>
									<div class="site-warning-item-text">
										Due to planned works, the M25 will be shut between Junctions 10 (Wisley Interchange) and 11 (Chertsey) from 9pm on Friday 21 March, until 6am on Monday 24 March. Passengers planning to use this part of the M25 should allow for extra time when travelling to Heathrow during this period. For more information, please visit National Highways’ website.
									</div>
								</div>
							</div>
							<div class="site-warning-item" data-warning="warning">
								<div class="site-warning-icographics warning-item-icon">
									<div class="site-warning-icon-wrap">
										<?php echo file_get_contents(get_template_directory() . '/assets/images/warning.svg') ?>
									</div>
									<div class="site-warning-type-text">Alerts</div>
								</div>
								<div class="site-warning-right">
									<span class="site-warning-item-title">feb, 17</span>
									<div class="site-warning-item-text">
										Due to planned works, there will be no flight from Paris to or from Split on Thursday 29 March
									</div>
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

		<!-- NOTE: the popup is open when flight-popup-wrapper has "open" class -->
		<div class="flight-popup-wrapper">
			<div class="flight-popup-close-area"></div>
			<div class="flight-popup">
				<div class="flight-popup-header-top">
					<div class="flight-popup-header-top-left">
						<span class="flight-popup-flight-icon">
							<?php
							$type = 'landing';
							if ( $type == 'landing' ) {
								echo file_get_contents(get_template_directory() . '/assets/images/airplane-landing.svg');
							} else {
								echo file_get_contents(get_template_directory() . '/assets/images/airplane-take-off.svg');
							}
							?>
						</span>
						<span class="flight-popup-flight-text">
							<?php 
							$type = 'landing';

							if ( $type == 'landing' ) {
								echo "Arrival";
							} else {
								echo "Departure";
							}
							?>
						</span>
						<span class="flight-popup-flight-id">
							c3 409
						</span>
					</div>
					
					<button type="button" class="flight-popup-close-btn">
						<?php echo file_get_contents(get_template_directory() . '/assets/images/x.svg'); ?>
					</button>
				</div>
				
				<div class="flight-popup-header">
					<div class="flight-popup-header-title">
						London to Split
					</div>
					<div class="flight-popup-header-text">
						Onboarding
					</div>
					<a class="flight-popup-header-btn" href="#">Follow this flight</a>
				</div>

				<div class="flight-popup-details">
					<div class="flight-popup-details-col flight-popup-details-img-col">
						<!-- TODO: replace with actual logo of airline -->
						<img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/airplane-landing.svg'); ?>" />
					</div>
					
					<!-- temp items start -->

					<div class="flight-popup-details-col flight-popup-details-lg-col">
						<div class="flight-popup-details-title">
							Airline
						</div>
						<div class="flight-popup-details-text">
							Croatia Airlines
						</div>
					</div>
					

					<div class="flight-popup-details-col">
						<div class="flight-popup-details-title">
							Date
						</div>
						<div class="flight-popup-details-text">
							26.02
						</div>
					</div>
					<div class="flight-popup-details-col">
						<div class="flight-popup-details-title">
							Planned
						</div>
						<div class="flight-popup-details-text">
							13:05
						</div>
					</div>
					<div class="flight-popup-details-col">
						<div class="flight-popup-details-title">
							Expected
						</div>
						<div class="flight-popup-details-text">
							13:05
						</div>
					</div>
					<div class="flight-popup-details-col">
						<div class="flight-popup-details-title">
							Baggage claim
						</div>
						<div class="flight-popup-details-text">
							2
						</div>
					</div>
					
					<!-- temp items end -->
				</div>


				<div class="flight-popup-main">
					<div class="flight-popup-main-item">
						<!-- NOTE: not sure if this is dynamic or just in first imte -->
						<?php echo file_get_contents(get_template_directory() . '/assets/images/airplane.svg') ?>

						<div class="flight-popup-main-title">Passport check (for non-EU citizens)</div>
						<div class="flight-popup-main-text"><p>The passport check is performed on a counter located right at the entrance from the runway.</p><p>For more information about Croatian and EU travel documments requirements visit: <a href="https://mvep.gov.hr/consular-information-22802/travel-information/22806">https://mvep.gov.hr/consular-information-22802/travel-information/22806</a></p></div>
					</div>

					<div class="flight-popup-main-item">
						<div class="flight-popup-main-title">Baggage claim</div>
						<div class="flight-popup-main-text"><p>The next area immediately after Passport check counters is the Baggage Claim area. The logo of your airline will be displayed on the corresponding baggage claim belt.</p></div>
					</div>

					<div class="flight-popup-main-item">
						<div class="flight-popup-main-title">Lobby pick-up area</div>
						<div class="flight-popup-main-text"><p>After claiming your baggage, you will make your way to the lobby through the pick-up area. If somebody is waiting for your at the airport, this is where they will be.</p></div>
					</div>
					
					<div class="flight-popup-main-item">
						<div class="flight-popup-main-title">Departing from the airport</div>
						<div class="flight-popup-main-text"><p>Split Airport is connected to Split and the surrounding area through a dedicated Airport Shuttle Bus, Taxis, and Public Transportation.</p></div>
					</div>

					
				</div>

			</div>
		</div>