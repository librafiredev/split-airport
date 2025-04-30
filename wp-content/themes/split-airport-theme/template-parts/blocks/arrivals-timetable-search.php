<?php
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
?>

<div class="arrivals-timetable-search">
    <div class="arrivals-timetable-search__input-wrap">
        <div class="arrivals-timetable-search__input">
            <?php echo file_get_contents(get_template_directory() . '/assets/images/search-icon.svg');  ?>
            <input name="search" type="text" placeholder="<?php esc_html_e('Find your Flights', 'split-airport'); ?>" />
            <div class="loader-search"></div>
        </div>
        <div style="display: none;" class="arrivals-timetable-search__bottom">
            <div class="arrivals-timetable-search__bottom-filters">
                <div class="arrivals-timetable-search__radio-inputs">
                    <div class="arrivals-timetable-search__radio-input">
                        <input id="arrivals-search" type="radio" name="flightsSearch" value="arrival" checked="checked" />
                        <label for="arrivals-search"><?php esc_html_e('Arrivals', 'split-airport'); ?></label><br>
                    </div>
                    <div class="arrivals-timetable-search__radio-input">
                        <input id="departures-search" type="radio" name="flightsSearch" value="departure" />
                        <label for="departures-search"><?php esc_html_e('Departures', 'split-airport'); ?></label><br>
                    </div>
                </div>
                <div class="arrivals-timetable-search__date">
                    <?php if ($dates): ?>
                        <select name="flightDateSearch">
                            <?php foreach ($dates as $value => $date): ?>
                                <option value="<?php echo $value; ?>"><?php echo ($value === date('Y-m-d') ? __('Today', 'split-airport') . ', ' : "") . $date; ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>
            </div>
            <div class="arrivals-timetable-search__bottom-results">
            </div>
        </div>
    </div>
</div>