<?php 
/*
* Block Name: Raw Content
* Post Type: page 
*/

if( isset( $block['data']['preview_image_help'] )  ) :
    echo '<img src="'. $block['data']['preview_image_help'] .'" style="width:100%; height:auto;">';
else: 
    $content = get_field('content');
    if(!$content) return;

?>

    <section class="raw-content">
        <div class="container">
            <div class="raw-content__inner">
                <?php echo $content; ?>
            </div>
        </div>
    </section><!-- .raw-content-->
    
<?php endif; ?>