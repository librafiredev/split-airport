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

$flightDate = isset($_GET['flightDate']) ? wp_strip_all_tags($_GET['flightDate']) : "";
$flightType = isset($_GET['flightType']) ? wp_strip_all_tags($_GET['flightType']) : "";
?>

<div class="arrivals-timetable-search">
    <div class="arrivals-timetable-search__input-wrap">
        <div class="arrivals-timetable-search__input">
            <?php echo file_get_contents(get_template_directory() . '/assets/images/search-icon.svg');  ?>
            <input name="search" type="text" placeholder="<?php esc_html_e('Find your Flights', 'split-airport'); ?>" autocomplete="off" />
            <div class="loader-search"></div>
        </div>
        <div style="display: none;" class="arrivals-timetable-search__bottom">
            <div class="arrivals-timetable-search__bottom-filters">
                <div class="arrivals-timetable-search__radio-inputs">
                    <div class="arrivals-timetable-search__radio-input">
                        <input <?php if($flightType === 'arrival') echo 'checked=checked'; ?>  id="arrivals-search" type="radio" name="flightsSearch" value="arrival" checked="checked" />
                        <label for="arrivals-search"><?php esc_html_e('Arrivals', 'split-airport'); ?></label><br>
                    </div>
                    <div class="arrivals-timetable-search__radio-input">
                        <input <?php if($flightType === 'departure') echo 'checked=checked'; ?> id="departures-search" type="radio" name="flightsSearch" value="departure" />
                        <label for="departures-search"><?php esc_html_e('Departures', 'split-airport'); ?></label><br>
                    </div>
                </div>
                <div class="arrivals-timetable-search__date">
                    <?php if ($dates): ?>
                        <select name="flightDateSearch">
                            <?php foreach ($dates as $value => $date): ?>
                                <option <?php if($value === $flightDate) echo 'selected=selected'; ?> value="<?php echo $value; ?>"><?php echo ($value === date('Y-m-d') ? __('Today', 'split-airport') . ', ' : "") . $date; ?></option>
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