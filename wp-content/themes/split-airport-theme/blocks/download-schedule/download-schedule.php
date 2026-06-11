<?php 
/*
* Block Name: Download Schedule
* Post Type: page 
*/

if( isset( $block['data']['preview_image_help'] )  ) :
    echo '<img src="'. $block['data']['preview_image_help'] .'" style="width:100%; height:auto;">';
else: 


$result  = fetch_flight_schedule([
    'dateFrom' => null,
    'dateTo'   => null,
]);
$flights = $result['flights'];

?>

    <section class="download-schedule-wrapper" style="background-image: url(<?php echo get_template_directory_uri() ?>/assets/images/dots-pattern.svg);">

        <div class="container">
            <div class="download-schedule-header">
                <div class="download-schedule-title-wrap">
                    <h2><?php the_field('title'); ?></h2>
                </div>

                <form class="download-schedule-filters js-download-schedule-filters">
                    <div class="dl-schedule-date-range js-dl-schedule-date-range" data-range-message="<?php echo __('Maximum search range is 3 months.', 'split-airport'); ?>">
                        <div class="labeled-field-wrapper js-dls-date-wrap is-empty"><div class="labeled-field-label"><?php echo __('From date *', 'split-airport'); ?></div> <div class="large-select-main">
                            <div><input type="text" readonly class="dls-date-display date-from-display" placeholder="<?php echo __('Select date', 'split-airport'); ?>" /> <input name="dls_from_date" type="text" readonly class="dls-hidden date-from" /></div><button type="button" class="js-clear-range">×</button>
                        </div></div>
                        <div class="labeled-field-wrapper js-dls-date-wrap is-empty"><div class="labeled-field-label"><?php echo __('To date *', 'split-airport'); ?></div> <div class="large-select-main">
                            <div><input type="text" readonly class="dls-date-display date-to-display" placeholder="<?php echo __('Select date', 'split-airport'); ?>" /> <input name="dls_to_date" type="text" readonly class="dls-hidden date-to" /></div><button type="button" class="js-clear-range">×</button>
                        </div></div>
                    </div>

                    <div class="labeled-field-wrapper dls-select-destination"><div class="js-select2-lbl labeled-field-label"><?php echo __('Destination', 'split-airport'); ?></div> <div class="dls-select-wrap" data-placeholder="<?php echo __('Country, city or airport', 'split-airport'); ?>"><select name="dls_destination" class="js-dls-destination-select" data-placeholder="<?php echo __('Country, city or airport', 'split-airport'); ?>">
                        <option></option>
                        <?php foreach (get_schedule_destinations_from_api() as $destinations) : ?>
                            <option value="<?php echo esc_attr($destinations['id']); ?>"><?php echo esc_html($destinations['name']); ?></option>
                        <?php endforeach; ?>
                    </select></div></div>

                    <div class="labeled-field-wrapper dls-select-carrier"><div class="js-select2-lbl labeled-field-label"><?php echo __('Carrier', 'split-airport'); ?></div> <div class="dls-select-wrap" data-placeholder="<?php echo __('Enter carrier', 'split-airport'); ?>"><select name="dls_carrier" class="js-dls-carrier-select" data-placeholder="<?php echo __('Enter carrier', 'split-airport'); ?>">
                        <option></option>
                        <?php foreach (get_schedule_carriers_from_api() as $carrier) : ?>
                            <option value="<?php echo esc_attr($carrier['id']); ?>"><?php echo esc_html($carrier['name']); ?></option>
                        <?php endforeach; ?>
                    </select></div></div>

                    <button type="submit" class="dls-submit-button"><?php echo __('Search', 'split-airport'); ?></button>
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
                    <?php echo $result['table_html']; ?>
                </div>
            </div>

            <div class="lg-dls-loader">
                <div class="spinner"></div>
            </div>

            <div class="entry-content dls-entry-content">
                <?php the_field('text'); ?>
            </div>
        </div>
    </section><!-- .download-schedule-wrapper-->
    
<?php endif; ?>

<script>
window.splitGlobalDLScheduleData = {
    flights: <?php echo json_encode($flights); ?>,
    filters: { from: '', to: '', destination: 'Any', carrier: 'Any', searchTime: '<?php echo date('c'); ?>', },
}
</script>
