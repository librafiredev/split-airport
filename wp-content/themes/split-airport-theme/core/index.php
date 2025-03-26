<?php 
/**
 * Custom functions for this theme.
 */
require get_template_directory() . '/core/functions.php';

/**
 * Custom filters and actions for this theme.
 */
require get_template_directory() . '/core/filters_actions.php';

/**
 * Custom shortcodes for this theme.
 */
require get_template_directory() . '/core/shortcodes.php';

/**
 * Register CPT's automatically.
 */
require get_template_directory() . '/includes/cpt/index.php';

/**
 * Load gutenberg blocks.
 */
if( WEBSITE_TYPE == 1 ):
    require get_template_directory() . '/core/blocks.php';
endif;

/**
 * Update all assets.
 */
require get_template_directory() . '/core/update_assets.php';

?>