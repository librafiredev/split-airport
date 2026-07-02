<?php 
/*
* Block Name: Download Schedule
* Post Type: page 
*/

if( isset( $block['data']['preview_image_help'] )  ) :
    echo '<img src="'. $block['data']['preview_image_help'] .'" style="width:100%; height:auto;">';
else: 


$tz          = wp_timezone();
$defaultFrom = new DateTime('today', $tz);
$defaultTo   = clone $defaultFrom;
$defaultTo->modify('+1 month');

?>

    <section class="download-schedule-wrapper initial-data" style="background-image: url(<?php echo get_template_directory_uri() ?>/assets/images/dots-pattern.svg);">

        <div class="container">
            <div class="entry-content dls-entry-content">
                <?php the_field('text'); ?>
            </div>

            <div class="download-schedule-header">
                <div class="download-schedule-title-wrap">
                    <h2><?php the_field('title'); ?></h2>
                </div>

                <form class="download-schedule-filters js-download-schedule-filters">
                    <div class="dl-schedule-date-range js-dl-schedule-date-range" data-range-message="<?php echo __('Maximum search range is 3 months.', 'split-airport'); ?>">
                        <div class="labeled-field-wrapper js-dls-date-wrap is-empty"><div class="labeled-field-label"><?php echo __('When', 'split-airport'); ?></div> <div class="large-select-main">
                            <div class="lsm-input-wrap"><input type="text" readonly class="dls-date-display date-from-to-display" placeholder="<?php echo __('Select date', 'split-airport'); ?>" /> <input name="dls_from_date" type="text" readonly class="dls-hidden date-from" /></div><button type="button" class="js-clear-range">×</button>
                        </div></div>
                        <div class="labeled-field-wrapper js-dls-date-wrap is-empty invisible-date"><div class="labeled-field-label"><?php echo __('To date *', 'split-airport'); ?></div> <div class="large-select-main">
                            <div class="lsm-input-wrap"><input type="text" readonly class="dls-date-display date-to-display" placeholder="<?php echo __('Select date', 'split-airport'); ?>" /> <input name="dls_to_date" type="text" readonly class="dls-hidden date-to" /></div><button type="button" class="js-clear-range">×</button>
                        </div></div>
                    </div>

                    <div class="labeled-field-wrapper labeled-single-field dls-select-destination"><div class="js-select2-lbl labeled-field-label"><?php echo __('Destination', 'split-airport'); ?></div> <div class="dls-select-wrap" data-placeholder="<?php echo __('Country, city or airport', 'split-airport'); ?>"><select name="dls_destination" class="js-dls-destination-select" data-placeholder="<?php echo __('Country, city or airport', 'split-airport'); ?>">
                        <option></option>
                        <?php foreach (get_schedule_destinations_from_api() as $destinations) : ?>
                            <?php if (!empty($destinations['id']) && !empty($destinations['name'])) : ?>
                                <option value="<?php echo esc_attr($destinations['id']); ?>"><?php echo esc_html($destinations['name']); ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select></div></div>

                    <div class="labeled-field-wrapper labeled-single-field dls-select-carrier"><div class="js-select2-lbl labeled-field-label"><?php echo __('Carrier', 'split-airport'); ?></div> <div class="dls-select-wrap" data-placeholder="<?php echo __('Enter carrier', 'split-airport'); ?>"><select name="dls_carrier" class="js-dls-carrier-select" data-placeholder="<?php echo __('Enter carrier', 'split-airport'); ?>">
                        <option></option>
                        <?php foreach (get_schedule_carriers_from_api() as $carrier) : ?>
                            <?php if (!empty($carrier['id']) && !empty($carrier['name'])) : ?>
                                <option value="<?php echo esc_attr($carrier['id']); ?>"><?php echo esc_html($carrier['name']); ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select></div></div>

                    <button type="submit" class="dls-submit-button">
                        <?php echo __('Search', 'split-airport'); ?>
                    </button>
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
                </div>
            </div>
            <div class="load-more-btn-wrap">
                <div class="js-load-more-btn">
                    <?php echo __('Show more flights', 'split-airport'); ?> <svg class="load-more-btn-arrow" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3.93305 10.808C4.05028 10.6909 4.20924 10.625 4.37499 10.625C4.54073 10.625 4.69969 10.6909 4.81692 10.808L9.37499 15.3661L9.37499 3.125C9.37499 2.95924 9.44083 2.80027 9.55804 2.68306C9.67525 2.56585 9.83423 2.5 9.99999 2.5C10.1657 2.5 10.3247 2.56585 10.4419 2.68306C10.5591 2.80027 10.625 2.95924 10.625 3.125L10.625 15.3661L15.1831 10.808C15.2411 10.75 15.31 10.704 15.3858 10.6726C15.4617 10.6411 15.5429 10.625 15.625 10.625C15.7071 10.625 15.7884 10.6412 15.8642 10.6726C15.94 10.704 16.0089 10.75 16.067 10.8081C16.125 10.8661 16.171 10.935 16.2024 11.0108C16.2338 11.0867 16.25 11.168 16.25 11.25C16.25 11.3321 16.2338 11.4134 16.2024 11.4892C16.171 11.565 16.125 11.6339 16.0669 11.692L10.4419 17.317C10.4399 17.319 10.4376 17.3207 10.4356 17.3227C10.4229 17.335 10.4099 17.347 10.3963 17.3582C10.3886 17.3645 10.3805 17.37 10.3727 17.3759C10.3642 17.3822 10.3559 17.3888 10.3471 17.3947C10.338 17.4008 10.3285 17.406 10.3192 17.4116C10.311 17.4165 10.3031 17.4216 10.2946 17.4261C10.2852 17.4311 10.2756 17.4354 10.266 17.4399C10.2571 17.4442 10.2483 17.4486 10.2392 17.4524C10.2299 17.4562 10.2205 17.4593 10.2111 17.4627C10.2012 17.4662 10.1915 17.47 10.1814 17.473C10.1721 17.4759 10.1626 17.4779 10.1531 17.4803C10.1428 17.4829 10.1326 17.4858 10.1221 17.4879C10.1112 17.49 10.1002 17.4913 10.0892 17.4929C10.08 17.4942 10.071 17.496 10.0617 17.4969C10.0411 17.4989 10.0206 17.5 9.99999 17.5C9.97939 17.5 9.95882 17.4989 9.9383 17.4969C9.92899 17.496 9.91995 17.4942 9.91072 17.4929C9.89978 17.4913 9.88879 17.49 9.87788 17.4879C9.86739 17.4858 9.85716 17.4829 9.84686 17.4803C9.8374 17.4779 9.8279 17.4759 9.81856 17.473C9.80849 17.47 9.79872 17.4662 9.78888 17.4627C9.7795 17.4593 9.77004 17.4562 9.7608 17.4524C9.75169 17.4486 9.74291 17.4442 9.73399 17.4399C9.72441 17.4354 9.71472 17.4311 9.70534 17.4261C9.69691 17.4216 9.68893 17.4165 9.68077 17.4116C9.67143 17.406 9.66196 17.4008 9.65289 17.3947C9.64407 17.3888 9.63576 17.3822 9.62729 17.3759C9.61943 17.37 9.61134 17.3645 9.60371 17.3582C9.58979 17.3468 9.57655 17.3346 9.56366 17.322C9.56186 17.3203 9.55985 17.3188 9.55805 17.317L3.93305 11.692C3.87501 11.6339 3.82897 11.565 3.79756 11.4892C3.76614 11.4134 3.74998 11.3321 3.74998 11.25C3.74998 11.1679 3.76614 11.0866 3.79756 11.0108C3.82897 10.935 3.87501 10.8661 3.93305 10.808Z" fill="#084983"/></svg>
                </div>
            </div>

            <div class="lg-dls-loader">
                <div class="spinner"></div>
            </div>


        </div>
    </section><!-- .download-schedule-wrapper-->
    
<?php endif; ?>

<script>
window.splitGlobalDLScheduleData = {
    flights: <?php echo json_encode([]); ?>,
    filters: { from: '', to: '', destination: 'Any', carrier: 'Any', searchTime: '<?php echo date('c'); ?>', },
    defaultFrom: '<?php echo $defaultFrom->format('c'); ?>',
    defaultTo: '<?php echo $defaultTo->format('c'); ?>',
    paginationSize: 10,
    currentPage: 0,
}
</script>
