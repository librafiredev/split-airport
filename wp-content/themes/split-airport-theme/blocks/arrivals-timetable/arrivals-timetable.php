<?php
/*
* Block Name: Arrivals Timetable
* Post Type: page 
*/

use SplitAirport\Helpers\Page;

if (isset($block['data']['preview_image_help'])) :
    echo '<img src="' . $block['data']['preview_image_help'] . '" style="width:100%; height:auto;">';
else:

    $currentLanguage = apply_filters('wpml_current_language', null);
    $locale = $currentLanguage === 'hr' ? 'hr_HR.UTF-8' : 'en_US.UTF-8';

    $formatter = new \IntlDateFormatter(
        $locale,
        \IntlDateFormatter::NONE,
        \IntlDateFormatter::NONE,
        date_default_timezone_get(),
        \IntlDateFormatter::GREGORIAN,
        'MMM d'
    );

    $dates = [];

    for ($i = 0; $i < 5; $i++) {
        $timestamp = strtotime("+$i days");
        $key = date('Y-m-d', $timestamp);
        $value = $formatter->format($timestamp);
        $dates[$key] = $value;
    }

    $flightType = isset($_GET['flightType']) ? wp_strip_all_tags($_GET['flightType']) : "";
    $search = isset($_GET['search']) ? wp_strip_all_tags($_GET['search']) : "";
    $flightDate = isset($_GET['flightDate']) ? wp_strip_all_tags($_GET['flightDate']) : "";
    $destination = isset($_GET['destination']) ? wp_strip_all_tags($_GET['destination']) : "";
    $airline = isset($_GET['airlineCompany']) ? wp_strip_all_tags($_GET['airlineCompany']) : "";

?>

    <section class="arrivals-timetable">
        <div class="arrivals-timetable__top">
            <div class="container">
                <?php get_template_part('template-parts/blocks/arrivals-timetable', 'search'); ?>
            </div>
        </div>
        <div class="container">
            <div class="arrivals-timetable__inner">

                <?php if ($search || $destination || $airline): ?>

                    <p class="search-notice">
                    <?php
                    $notices = [];

                    if ($search) {
                        $notices[] = sprintf(__('Search term: <strong>%s</strong>', 'split-airport'), esc_html($search));
                    }

                    if ($destination) {
                        $notices[] = sprintf(__('Flights for Destination: <strong>%s</strong>', 'split-airport'), esc_html($destination));
                    }

                    if ($airline) {
                        $notices[] = sprintf(__('Filtered by Airline: <strong>%s</strong>', 'split-airport'), esc_html($airline));
                    }

                    echo implode(' &nbsp;|&nbsp; ', $notices);
                    ?>.
                    <a href="<?php echo Page::getSearchPage(); ?>">
                        <?php 
                        if ($currentLanguage === 'hr') {
                            echo esc_html__('Započni novu pretragu', 'split-airport');
                        } else {
                            echo esc_html__('Start new search', 'split-airport');
                        }
                        ?>
                    </a>
                </p>

                <?php endif; ?>

                <div class="arrivals-timetable__filters">
                    <div class="arrivals-timetable__radio-input">
                        <input id="arrivals" type="radio" name="flightsInit" value="arrival" checked="checked" />
                        <label for="arrivals"><?php esc_html_e('Arrivals', 'split-airport'); ?></label><br>
                    </div>
                    <div class="arrivals-timetable__radio-input">
                        <input id="departures" type="radio" name="flightsInit" value="departure" <?php if ($flightType === 'departure') echo 'checked=checked'; ?> />
                        <label for="departures"><?php esc_html_e('Departures', 'split-airport'); ?></label><br>
                    </div>
                    <div class="arrivals-timetable__radio-input">
                        <div class="date-switcher">
                            <div data-direction="left" class="date-switcher__left">
                                <?php echo file_get_contents(get_template_directory() . '/assets/images/date-switcher-left.svg');  ?>
                            </div>
                            <!-- NOTE: might need to delete this -->
                            <div class="date-switcher__view">
                                <?php
                                if ($flightDate) {
                                    echo ($flightDate === date('Y-m-d') ? __('Today, ', 'split-airport') : '') . $dates[$flightDate];
                                } else {
                                    echo __('Today, ', 'split-airport') . $dates[date('Y-m-d')];
                                }

                                ?>
                            </div>
                            <?php if ($dates): ?>

                                <div class="arrivals-timetable-search__date no-chevron-select">
                                    <select name="flightDate">
                                        <?php foreach ($dates as $value => $date): ?>
                                            <option data-isToday="<?php echo ($value === date('Y-m-d') ? 'true' : 'false') ?>" <?php if ($flightDate === $value) echo 'selected=selected'; ?> value="<?php echo $value; ?>"><?php echo ($value === date('Y-m-d') ? __('Today', 'split-airport') . ", " : "") . $date; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                            <?php endif; ?>
                            <div data-direction="right" class="date-switcher__right">
                                <?php echo file_get_contents(get_template_directory() . '/assets/images/date-switcher-right.svg');  ?>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="arrivals-timetable__table">
                    <a href="#" class="arrivals-timetable__earlier <?php if (isset($_GET['earlierFlights']) && $_GET['earlierFlights'] === 'show') echo 'active'; ?>">
                        <span>
                            <?php
                            if (isset($_GET['earlierFlights']) && $_GET['earlierFlights'] === 'show') {
                                esc_html_e('Back to current flights', 'split-airport');
                            } else {
                                esc_html_e('Show earlier flights', 'split-airport');
                            }
                            ?>
                        </span>
                        <?php

                        echo file_get_contents(get_template_directory() . '/assets/images/arrow-up.svg');
                        ?>
                    </a>
                    <div class="arrivals-timetable__table-header">
                        <span class="arrivals-timetable__table-name flight-info flight__flight"><?php esc_html_e('Flight', 'split-airport') ?></span>
                        <span class="arrivals-timetable__table-name flight-type flight__arriving-from"><?php echo $flightType === 'arrival' ? __('Arriving from', 'split-airport') :  __('Going to', 'split-airport')  ?></span>
                        <span class="arrivals-timetable__table-name flight__planned"><?php esc_html_e('Scheduled', 'split-airport') ?></span>
                        <span class="arrivals-timetable__table-name flight__expected"><?php esc_html_e('Estimated', 'split-airport') ?></span>

                        <?php if (isset($_GET['flightType']) && $_GET['flightType'] === 'departure'): ?>

                            <span class="arrivals-timetable__table-name gate flight__baggage-gate"><?php esc_html_e('Gate', 'split-airport') ?></span>

                        <?php endif; ?>

                        <span class="arrivals-timetable__table-name flight__baggage-status"><?php esc_html_e('Status', 'split-airport') ?></span>
                    </div>

                    <div class="arrivals-timetable__table-flights">

                    </div>
                </div>
            </div>
        </div>
        <div class="loader-wrap">
            <div class="loader"></div>
        </div>
    </section><!-- .arrivals-timetable-->

    <?php get_template_part('template-parts/blocks/flight-popup'); ?>

<?php endif; ?>