<?php

// check if the flexible content field has rows of data
if( have_rows('flexible_content_field_name') ):

     // loop through the rows of data
    while ( have_rows('flexible_content_field_name') ) : the_row();

        if( get_row_layout() == 'paragraph' ):

        	the_sub_field('text');

        elseif( get_row_layout() == 'download' ): 

        	$file = get_sub_field('file');

        endif;

    endwhile;

else :

    // no layouts found

endif;

?>