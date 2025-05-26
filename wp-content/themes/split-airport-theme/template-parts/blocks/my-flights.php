<?php

use SplitAirport\Models\Flight;
use SplitAirport\Models\MyFlights;

$myFlights = MyFlights::getMyFlights();
$total = count($myFlights);
if ($myFlights) {
    $newestFlight = $myFlights[$total - 1];
    $newestFlight = Flight::getFlightByID($newestFlight);
}

?>

<div class="my-flights-btn view-trigger">
    <div class="my-flights-svg-wrap"><?php echo file_get_contents(get_template_directory() . '/assets/images/my-flight-cutout.svg'); ?></div>

    <?php
    if (isset($newestFlight) && $newestFlight):
        get_template_part('template-parts/blocks/my-flights-newest', null, ['newestFlight' => $newestFlight, 'total' => $total]);
    endif; ?>

</div>

<div class="my-flights-modal-wrapper custom-modal-wrapper">
    <div class="custom-modal-close-area"></div>
    <div class="custom-modal">
        <div class="my-flights-modal-header">
            <div class="my-flights-modal-header-left">
                <img class="my-flights-modal-header-icon" src="<?php echo get_template_directory_uri() . "/assets/images/fav-flights.svg" ?>" alt="Fav flights" />
                <div class="heading-third"><?php esc_html_e('Favourite flights', 'split-airport') ?></div>
            </div>
            <div class="custom-modal-close-btn-wrap">
                <button type="button" class="custom-modal-close-btn">
                    <?php echo file_get_contents(get_template_directory() . '/assets/images/x.svg'); ?>
                </button>
            </div>
        </div>

        <div class="my-flights-modal-wrapper__items">
            <?php
            foreach ($myFlights as $myFlight) :
                $flight = Flight::getFlightByID($myFlight);
            ?>
                <div class="my-flight-item">
                    <div class="my-flight-item-btn">
                        <?php get_template_part('template-parts/blocks/my-flights-item', null, ['flight' => $flight]); ?>
                    </div>
                    <div data-id="<?php echo $myFlight; ?>" class="my-flight-item-remove-btn"><?php echo file_get_contents(get_template_directory() . '/assets/images/x.svg'); ?></div>
                </div>
            <?php endforeach; ?>

        </div>
    </div>

</div>