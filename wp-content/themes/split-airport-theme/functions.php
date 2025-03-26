<?php

/**
 * Require file where we can set theme config values
 */
require get_template_directory() . '/theme-config.php';

/**
** DON'T TOUCH THIS. This is the file that's requiring all the needed
** files for the theme to function correctly.
*/
require get_template_directory() . '/core/index.php';

/***********************************************************************
** START EDITING FROM HERE
*/

/**
* Require file where we can include different scripts (plugins...)
*/
require get_template_directory() . '/includes/enqueue_scripts_and_styles.php';


/**
 * Require file where we can define custom image sizes
 */
require get_template_directory() . '/includes/custom_image_sizes.php';

/**
 * Require file where we can write custom functions per project
 */
require get_template_directory() . '/includes/theme_functions.php';