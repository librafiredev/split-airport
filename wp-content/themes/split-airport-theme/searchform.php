<?php
/**
 * File that's included each time we call get_search_form function
 *
 * @package Split
 */

?>

<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label>
		<input type="search" class="search-field" placeholder="<?php esc_html_e( 'Search', 'split' ); ?>" value="<?php echo get_search_query(); ?>" name="s" />
		<button class="submit-button-search" type="submit">
			<img src="<?php echo get_template_directory_uri(); ?>/assets/images/search.svg" alt="search" width="12" />
		</button>
	</label>
</form>
