<?php 
/*
* Block Name: Monthly Table
* Post Type: page 
*/

if( isset( $block['data']['preview_image_help'] )  ) :
    echo '<img src="'. $block['data']['preview_image_help'] .'" style="width:100%; height:auto;">';
else:

$table_months = ['january','february','march','april','may','june','july','august','september','october','november','december'];
?>

    <section class="monthly-table-wrapper">
        <?php if ( get_field('title') ) : ?>
            <div class="container">
                <h2 class="heading-secondary">
                    <?php echo get_field('title'); ?>
                </h2>
            </div>
        <?php endif; ?>

        <div class="monthly-table-inner">
            <div class="container">
                <table class="monthly-table">
                    <thead>
                        <tr>
                            <th colspan="<?php echo count($table_months) + 2; ?>" class="table-title">
                                <?php the_field('table_title'); ?>
                            </th>
                        </tr>
                
                        <!-- Head -->
                        <tr>
                            <th class="table-year">
                                <?php the_field('year_header'); ?>
                            </th>
                            <?php
                            foreach ($table_months as $index => $month) {
                                ?>
                                <th class="table-month">
                                    <?php echo wp_date('M', strtotime( '2019-'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT).'-14' )); ?>
                                </th>
                
                                <?php
                            }
                            ?>
                            <th class="table-total">
                                <?php the_field('totals_header'); ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if( have_rows('rows') ): ?>
                            <?php while ( have_rows('rows') ) : the_row(); ?>
                                <tr>
                                    <th class="table-year">
                                        <?php echo get_sub_field('year'); ?>
                                    </th>
                
                                    <?php
                                    $sum = 0;
                                    foreach ($table_months as $index => $month) {
                                        ?>
                                        <td class="table-month">
                                            <?php
                                            try {
                                                $month_value = (float) get_sub_field($month);
                                                $sum += $month_value;
                                            } catch (\Throwable $th) {
                                                //throw $th;
                                            }
                                            echo $month_value; ?>
                                        </td>
                
                                        <?php
                                    }
                                    ?>
                                    <td class="table-total">
                                        <?php echo $sum; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section><!-- .monthly-table-wrapper-->
    
<?php endif; ?>