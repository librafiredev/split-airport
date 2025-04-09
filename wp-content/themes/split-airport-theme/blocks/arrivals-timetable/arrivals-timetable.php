<?php
/*
* Block Name: Arrivals Timetable
* Post Type: page 
*/

if (isset($block['data']['preview_image_help'])) :
    echo '<img src="' . $block['data']['preview_image_help'] . '" style="width:100%; height:auto;">';
else:
    $dates = [];

    for ($i = 0; $i < 5; $i++) {
        $dates[date('Y-m-d', strtotime("+$i days"))] = date('M d', strtotime("+$i days"));
    }

?>

    <section class="arrivals-timetable">
        <div class="arrivals-timetable__top">
            <?php get_template_part('template-parts/blocks/arrivals-timetable', 'search'); ?>
        </div>
        <div class="container">
            <div class="arrivals-timetable__inner">
                <div class="arrivals-timetable__filters">
                    <div class="arrivals-timetable__radio-input">
                        <input id="arrivals" type="radio" name="flightsInit" value="arrival" checked="checked" />
                        <label for="arrivals"><?php esc_html_e('Arrivals', 'split-airport'); ?></label><br>
                    </div>
                    <div class="arrivals-timetable__radio-input">
                        <input id="departures" type="radio" name="flightsInit" value="departure" />
                        <label for="departures"><?php esc_html_e('Departures', 'split-airport'); ?></label><br>
                    </div>
                    <div class="arrivals-timetable__radio-input">

                        <div class="date-switcher">
                            <div data-direction="left" class="date-switcher__left">
                                <?php echo file_get_contents(get_template_directory() . '/assets/images/date-switcher-left.svg');  ?>
                            </div>
                            <div class="date-switcher__view">
                                <?php echo __('Today, ', 'split-airport') . $dates[date('Y-m-d')]; ?>
                            </div>
                            <div data-direction="right" class="date-switcher__right">
                                <?php echo file_get_contents(get_template_directory() . '/assets/images/date-switcher-right.svg');  ?>
                            </div>
                        </div>
                        <?php if ($dates): ?>

                            <select style="display: none;" name="flightDate">
                                <?php foreach ($dates as $value => $date): ?>

                                    <option value="<?php echo $value; ?>"><?php echo ($value === date('Y-m-d') ? 'Today, ' : "") . $date; ?></option>

                                <?php endforeach; ?>

                            </select>

                        <?php endif; ?>
                    </div>
                </div>
                <div class="arrivals-timetable__table">
                    <div class="arrivals-timetable__table-header">
                        <div class="arrivals-timetable__table-header-left">
                            <span class="arrivals-timetable__table-name"><?php esc_html_e('Planned', 'split-airport') ?></span>
                            <span class="arrivals-timetable__table-name"><?php esc_html_e('Expected', 'split-airport') ?></span>
                            <span class="arrivals-timetable__table-name"><?php esc_html_e('Arriving from', 'split-airport') ?></span>
                            <span class="arrivals-timetable__table-name"><?php esc_html_e('Flight', 'split-airport') ?></span>
                        </div>
                        <div class="arrivals-timetable__table-header-right">
                            <span class="arrivals-timetable__table-name"><?php esc_html_e('Baggage claim', 'split-airport') ?></span>
                            <span class="arrivals-timetable__table-name"><?php esc_html_e('Status', 'split-airport') ?></span>
                        </div>
                    </div>

                    <div class="arrivals-timetable__table-flights">

                    </div>
                </div>
            </div>
        </div>
        <div class="loader"></div>
    </section><!-- .arrivals-timetable-->

<?php endif; ?>