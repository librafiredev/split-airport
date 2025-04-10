<?php
extract($args);

if (!empty($flight['schtime'])) {
    $schTime = new DateTime($flight['schtime']);
    $schTime = $schTime->format('H:i');
}

if (!empty($flight['esttime'])) {
    $esttime = new DateTime($flight['esttime']);
    $esttime = $esttime->format('H:i');
}
?>

<div data-id="<?php echo $flight['ID']; ?>" class="flight">
    <span class="flight__planned">
        <?php echo isset($schTime) ? htmlspecialchars($schTime) : '' ?>
    </span>

    <span class="flight__expected">
        <?php echo isset($esttime) ? htmlspecialchars($esttime) : '' ?>
    </span>

    <span class="flight__arriving-from">
        <?php echo !empty($flight['destination']) ? htmlspecialchars($flight['destination']) : '' ?>
    </span>

    <span class="flight__flight">
        <div class="flight__icon">

        </div>
        <div class="flight__info">
            <?php echo !empty($flight['flight_number']) && !empty($flight['airline'])
                ? htmlspecialchars($flight['flight_number'] . ' – ' . $flight['airline'])
                : '' ?>
        </div>
    </span>

    <span class="flight__baggage-claim">
        <?php echo !empty($flight['parkingPosition']) ? htmlspecialchars($flight['parkingPosition']) : '' ?>
    </span>

    <span class="flight__baggage-status">
        <?php echo !empty($flight['comment']) ? htmlspecialchars($flight['comment']) : '' ?>
    </span>
</div>