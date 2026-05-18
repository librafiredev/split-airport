<?php 
/*
* Block Name: Download Schedule
* Post Type: page 
*/

if( isset( $block['data']['preview_image_help'] )  ) :
    echo '<img src="'. $block['data']['preview_image_help'] .'" style="width:100%; height:auto;">';
else: 


$placeholder_data = [
    [
        'destination' => 'London',
        'date' => '23.04.2026 / Wednesday',
        'time' => '13:55',
        'number' => 'HA 521',
        'carrier' => 'Croatia Airlines',
        'code' => 'LH 6004',
    ],
    [
        'destination' => 'New York',
        'date' => '23.05.2026 / Someday',
        'time' => '13:55',
        'number' => 'HN 22',
        'carrier' => 'Someline',
        'code' => 'LH 6468',
    ],
];

$flights = $placeholder_data;

?>

    <section class="download-schedule-wrapper" style="background-image: url(<?php echo get_template_directory_uri() ?>/assets/images/dots-pattern.svg);">

        <div class="container">
            <div class="download-schedule-header">
                <div>
                    <h2><?php the_field('title'); ?></h2>
                </div>

                <form class="download-schedule-filters js-download-schedule-filters">
                    <div class="js-dl-schedule-date-range" data-range-message="<?php echo __('Maximum search range is 3 months.', 'split-airport'); ?>">
                        <div class="js-dls-date-wrap is-empty"><div><?php echo __('From date *', 'split-airport'); ?></div> <div><span class="date-from-display"></span> <input name="from_date" type="text" readonly class="date-from" placeholder="<?php echo __('Select', 'split-airport'); ?>" /></div><button type="button" class="js-clear-range">x</button></div>
                        <div class="js-dls-date-wrap is-empty"><div><?php echo __('To date *', 'split-airport'); ?></div> <div><span class="date-to-display"></span> <input name="to_date" type="text" readonly class="date-to" placeholder="<?php echo __('Select', 'split-airport'); ?>" /></div><button type="button" class="js-clear-range">x</button></div>
                    </div>

                    <div><div><?php echo __('Destination', 'split-airport'); ?></div> <div><select name="destination" class="js-dls-destination-select" data-placeholder="<?php echo __('Country, city or airport', 'split-airport'); ?>"></select></div></div>

                    <div><div><?php echo __('Carrier', 'split-airport'); ?></div> <div><select name="carrier" class="js-dls-carrier-select" data-placeholder="<?php echo __('Enter carrier', 'split-airport'); ?>"></select></div></div>

                    <button type="submit"><?php echo __('Search', 'split-airport'); ?></button>
                </form>
            </div>
            
            <div class="download-schedule-btns content-button rounded-buttons">
                <button type="button" class="js-download-pdf-schedule">
                    <?php esc_html_e('Download as PDF', 'split-airport') ?>
                </button>
                <button type="button" class="js-download-csv-schedule">
                    <?php esc_html_e('Download as CSV', 'split-airport') ?>
                </button>
            </div>

            <div class="basic-table download-schedule-table">
                <div class="basic-table-header">
                    <span class="basic-table-cell "><?php esc_html_e('Destination', 'split-airport') ?></span>
                    <span class="basic-table-cell "><?php esc_html_e('Flight date', 'split-airport') ?></span>
                    <span class="basic-table-cell "><?php esc_html_e('Flight time', 'split-airport') ?></span>
                    <span class="basic-table-cell "><?php esc_html_e('Flight number & Carrier', 'split-airport') ?></span>
                    <span class="basic-table-cell "><?php esc_html_e('Code share', 'split-airport') ?></span>
                </div>

                <div class="download-schedule-content">
                    <?php foreach ($flights as $flight) {
                        echo lf_get_download_schedule_row_html($flight);
                    } ?>
                </div>
            </div>
        </div>

        <div class="dls-spinner">
            <div class="spinner"></div>
        </div>
    </section><!-- .download-schedule-wrapper-->
    
<?php endif; ?>

<script>
window.splitGlobalDLScheduleData = {
    flights: <?php echo json_encode($flights); ?>,
    filters: { from: '', to: '', destination: 'Any', carrier: 'Any', searchTime: '<?php echo date('c'); ?>', },
}
</script>
