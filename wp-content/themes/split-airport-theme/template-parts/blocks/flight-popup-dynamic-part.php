<?php

use SplitAirport\Models\Flight;
use SplitAirport\Helpers\DateTimeFlight;

extract($args);

if (isset($airline) && $airline) {
    $airlinePost = Flight::getAirlineByTitle($airline);

    if ($airlinePost) {
        $airlineIcon = get_the_post_thumbnail($airlinePost['ID']);
    }
}

$type = strtolower($AD);

$timeline = get_field('single_flight_popup_' . ($type === 'arrival' ? 'arrivals' : 'departure'), 'options');
?>

<div class="flight-popup">
    <div class="flight-popup-header-top">
        <div class="flight-popup-header-top-left">
            <span class="flight-popup-flight-icon">
                <?php
                $iconPath = $type === 'arrival' ? '/assets/images/airplane-landing.svg' : '/assets/images/airplane-take-off.svg';
                echo file_get_contents(get_template_directory() . $iconPath);
                ?>
            </span>
            <span class="flight-popup-flight-text">
                <?php echo $type === 'arrival' ? __('Arrival', 'split-airport') :  __('Departure', 'split-airport'); ?>
            </span>

        </div>

        <button type="button" class="flight-popup-close-btn">
            <?php echo file_get_contents(get_template_directory() . '/assets/images/x.svg'); ?>
        </button>
    </div>

    <div class="flight-popup-header">
        <div class="flight-popup-header__left">
            <div class="flight-popup-header__top">
                <span class="flight-popup-flight-id">
                    <?php echo isset($flight_number) && $flight_number ? esc_html($flight_number) : 'N/A'; ?>
                </span>
                <div class="flight-popup-header-title">
                    <?php
                    $destinationDisplay = isset($destination) && $destination ? esc_html($destination) : __('Unknown Destination', 'split-airport');
                    echo $type === 'arrival' ? $destinationDisplay . " " . __('to Split', 'split-airport')   : __('Split to', 'split-airport') . " " . $destinationDisplay;
                    ?>
                </div>
            </div>
        </div>
        <div class="flight-popup-header__right">
            <!-- <a class="flight-popup-header-btn" href="#"><?php esc_html_e('Follow this flight', 'split-airport'); ?></a> -->
        </div>
        <div class="flight-popup-header-text <?php echo strtolower(str_replace(" ", "-", $comment)); ?>">
            <?php echo $comment; ?>
        </div>
    </div>

    <div class="flight-popup-details">
        <div class="flight-popup-details-col flight-popup-details-img-col">
            <?php if (isset($airlineIcon) && $airlineIcon) echo $airlineIcon; ?>
        </div>

        <div class="flight-popup-details-col flight-popup-details-lg-col">
            <div class="flight-popup-details-title"><?php esc_html_e('Airline', 'split-airport'); ?></div>
            <div class="flight-popup-details-text">
                <?php echo isset($airline) && $airline ? esc_html($airline) : 'N/A'; ?>
            </div>
        </div>

        <div class="flight-popup-details-col">
            <div class="flight-popup-details-title"><?php esc_html_e('Date', 'split-airport'); ?></div>
            <div class="flight-popup-details-text">
                <?php
                if (!empty($schdate)) {
                    echo DateTimeFlight::formatDateTableView($schdate);
                } else {
                    echo 'N/A';
                }
                ?>
            </div>
        </div>

        <div class="flight-popup-details-col">
            <div class="flight-popup-details-title"><?php esc_html_e('Scheduled', 'split-airport'); ?></div>
            <div class="flight-popup-details-text <?php if ($schtime && ($schtime < $esttime || $schtime > $esttime)) echo 'strikethrough'; ?>">
                <?php
                if (!empty($schtime)) {
                    echo DateTimeFlight::formatTimeTableView($schtime);
                } else {
                    echo 'N/A';
                }
                ?>
            </div>
        </div>

        <div class="flight-popup-details-col">
            <div class="flight-popup-details-title"><?php esc_html_e('Estimated', 'split-airport'); ?></div>
            <div class="flight-popup-details-text">
                <?php
                if (!empty($esttime) && $schtime != $esttime) {
                    echo DateTimeFlight::formatTimeTableView($esttime);
                } else {
                    echo "-";
                }
                ?>
            </div>
        </div>

        <?php if ($type === 'departure'): ?>

            <div class="flight-popup-details-col">
                <div class="flight-popup-details-title"><?php esc_html_e('Gate', 'split-airport'); ?></div>
                <div class="flight-popup-details-text">
                    <?php echo !empty($gate) ? esc_html($gate) : '—'; ?>
                </div>
            </div>

        <?php endif; ?>

    </div>

    <?php if ($timeline): ?>

        <div class="flight-popup-main">

            <?php foreach ($timeline as $row => $timelineItem): ?>
                <div class="flight-popup-main-item">
                    <div class="flight-popup-main-title"><?php echo $timelineItem['title']; ?></div>
                    <div class="flight-popup-main-text">
                        <?php echo $timelineItem['content']; ?>
                    </div>
                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</div>