<?php 
/*
* Block Name: Home Hero
* Post Type: page 
*/

if( isset( $block['data']['preview_image_help'] )  ) :
    echo '<img src="'. $block['data']['preview_image_help'] .'" style="width:100%; height:auto;">';
else: ?>

    <section class="home-hero-wrapper">
    </section><!-- .home-hero-wrapper-->
    
<?php endif; ?>