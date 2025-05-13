<?php 

/**
** CHOICE FLEXIBLE OR GUTENBERG
** flexible = 0
** gutenberg = 1
*/
define( 'WEBSITE_TYPE', '1' );

/** CSS and JS files version */
define( 'ASSETS_VERSION', '1.1' );

/** After which block critical css stops */
define( 'CRITICAL_CSS_THRESHOLD', '2' );

/** Set to true while website is in development */
if( !defined( 'IN_DEVELOPMENT' ) )
    define( 'IN_DEVELOPMENT', false );

/** Google Maps API key */
define( 'ACF_GOOGLE_API_KEY', '' );