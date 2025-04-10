<?php
/*
* Block Name: Arrivals Timetable
* Post Type: page 
*/

use SplitAirport\Helpers\Page;

if (isset($block['data']['preview_image_help'])) :
    echo '<img src="' . $block['data']['preview_image_help'] . '" style="width:100%; height:auto;">';
else:
    $dates = [];

    for ($i = 0; $i < 5; $i++) {
        $dates[date('Y-m-d', strtotime("+$i days"))] = date('M d', strtotime("+$i days"));
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
                            <?php esc_html_e('Clear all filters and start a new search', 'split-airport'); ?>
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
                            <div class="date-switcher__view">
                                <?php
                                if ($flightDate) {
                                    echo ($flightDate === date('Y-m-d') ? __('Today, ', 'split-airport') : '') . $dates[$flightDate];
                                } else {
                                    echo __('Today, ', 'split-airport') . $dates[date('Y-m-d')];
                                }

                                ?>
                            </div>
                            <div data-direction="right" class="date-switcher__right">
                                <?php echo file_get_contents(get_template_directory() . '/assets/images/date-switcher-right.svg');  ?>
                            </div>
                        </div>
                        <?php if ($dates): ?>

                            <select style="display:none;" name="flightDate">
                                <?php foreach ($dates as $value => $date): ?>

                                    <option <?php if ($flightDate === $value) echo 'selected=selected'; ?> value="<?php echo $value; ?>"><?php echo ($value === date('Y-m-d') ? 'Today, ' : "") . $date; ?></option>

                                <?php endforeach; ?>

                            </select>

                        <?php endif; ?>
                    </div>
                </div>
                <div class="arrivals-timetable__table">
                    <div class="arrivals-timetable__table-header">
                        <span class="arrivals-timetable__table-name"><?php esc_html_e('Planned', 'split-airport') ?></span>
                        <span class="arrivals-timetable__table-name"><?php esc_html_e('Expected', 'split-airport') ?></span>
                        <span class="arrivals-timetable__table-name flight-type"><?php echo $flightType === 'arrival' ? __('Arriving from', 'split-airport') :  __('Going to', 'split-airport')  ?></span>
                        <span class="arrivals-timetable__table-name"><?php esc_html_e('Flight', 'split-airport') ?></span>
                        <span class="arrivals-timetable__table-name"><?php esc_html_e('Baggage claim', 'split-airport') ?></span>
                        <span class="arrivals-timetable__table-name"><?php esc_html_e('Status', 'split-airport') ?></span>
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

