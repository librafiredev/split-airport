<?php 
/*
* Block Name: Page Hero
* Post Type: page 
*/

if( isset( $block['data']['preview_image_help'] )  ) :
    echo '<img src="'. $block['data']['preview_image_help'] .'" style="width:100%; height:auto;">';
else: ?>

    <section class="page-hero-wrapper">
        
        <div class="page-hero-img-wrap"><?php echo wp_get_attachment_image(get_field('background'), 'full', ); ?></div>
        <div class="page-hero-cutout-wrap">
            <svg version="1.2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1439 71" width="1439" height="71" class="page-hero-cutout"><path d="m682.7 0h-682.7v716h1445v-645h-611.4c-18.5 0-36.5-6.1-51.3-17.3l-48.3-36.5c-14.8-11.2-32.8-17.2-51.3-17.2z"/></svg>
        </div>

    </section><!-- .page-hero-wrapper-->
    
<?php endif; ?>
