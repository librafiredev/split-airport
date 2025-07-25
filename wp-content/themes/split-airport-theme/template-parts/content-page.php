<?php
/**
 * The template used for displaying page content in page.php
 *
 * @package Split
 */


global $sa_sidebar_nav_index;
global $sa_sidebar_nav_items;
global $sa_sidebar_nav_content_wrapper_open;

$sa_sidebar_nav_index = 0;
$sa_sidebar_nav_items = [];
$sa_sidebar_nav_content_wrapper_open = false;

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="entry-content">
		<?php the_content(); ?>
		<?php if ( $sa_sidebar_nav_content_wrapper_open ) : ?>
				</div><?php // note, this is open in one of the blocks ?>
				<div class="sa-global-sidebar">
					<div class="sa-global-sidebar-title"><div class="heading-third"><?php the_field('sidebar_navigation_title'); ?></div></div>
					
					<div class="sa-global-sidebar-items-wrap">
						<?php if ( !empty($sa_sidebar_nav_items) ) : ?>
							<div class="sa-global-current-block-mobile"><?php echo $sa_sidebar_nav_items[0]['title'] ?></div>
						<?php endif; ?>
						<div class="sa-global-sidebar-items">
							<?php foreach ($sa_sidebar_nav_items as $index => $value) { ?>
								<button type="button" class="sa-sidebar-item-btn<?php echo $index == 0 ? ' is-active' : ''; ?>" data-target-block="<?php echo $value['unique_class']; ?>"><?php echo $value['title']; ?></button>
							<?php } ?>
						</div>
					</div>
				</div>
			
			</div><?php // note, this is open in one of the blocks ?>
		<?php endif; ?>
	</div><!-- .entry-content -->
</article><!-- #post-## -->


