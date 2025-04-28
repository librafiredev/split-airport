<?php

use SplitAirport\Helpers\DateTimeFlight;
use SplitAirport\Models\Flight;

extract($args);

if (!empty($flight['schtime'])) {
    $schTime = DateTimeFlight::formatTimeTableView($flight['schtime']);
}

if (!empty($flight['esttime'])) {
    $estTime = DateTimeFlight::formatTimeTableView($flight['esttime']);
}

if ($flight['airline']) {
    $airline = Flight::getAirlineByTitle($flight['airline']);

    if ($airline) {
        $icon = get_the_post_thumbnail($airline['ID']);
    }
}

?>

<div data-id="<?php echo $flight['ID']; ?>" class="flight">
    <span class="flight__planned <?php if ($flight['esttime'] && ($flight['esttime'] < $flight['schtime'] || $flight['esttime'] > $flight['schtime']  )) echo 'strikethrough'; ?>">
        <?php echo isset($schTime) ? htmlspecialchars($schTime) : '' ?>
    </span>

    <span class="flight__expected">
        <?php echo isset($estTime) && $flight['esttime'] != $flight['schtime'] ? htmlspecialchars($estTime) : '' ?>
    </span>

    <span class="flight__arriving-from">
        <?php echo !empty($flight['destination']) ? htmlspecialchars($flight['destination']) : '' ?>
    </span>

    <span class="flight__flight">
        <?php if (isset($icon)): ?>

            <span class="flight__icon">
                <?php echo $icon; ?>
            </span>

        <?php endif; ?>

        <span class="flight__info">

            <?php echo !empty($flight['flight_number']) && !empty($flight['airline'])
                ? '<span class="flight__flight-num">' . htmlspecialchars($flight['flight_number']) . '</span>' . "<br>" . '<span class="flight__flight-airline">' . htmlspecialchars($flight['airline']) . '</span>'
                : '' ?>

        </span>
    </span>

    <span class="flight__baggage-gate">
        <?php echo !empty($flight['gate']) ? htmlspecialchars($flight['gate']) : ''
        ?>
    </span>

    <span class="flight__baggage-status <?php echo strtolower(str_replace(" ", "-", $flight['comment'])); ?>">
        <?php echo !empty($flight['comment']) ? htmlspecialchars($flight['comment']) : '' ?>
    </span>
</div>