<?php extract($args); ?>

<div class="flight-popup">
    <div class="flight-popup-header-top">
        <div class="flight-popup-header-top-left">
            <span class="flight-popup-flight-icon">
                <?php
                $type = isset($AD) && strtoupper($AD) === 'DEPARTURE' ? 'takeoff' : 'landing';
                $iconPath = $type === 'landing' ? '/assets/images/airplane-landing.svg' : '/assets/images/airplane-take-off.svg';
                echo file_get_contents(get_template_directory() . $iconPath);
                ?>
            </span>
            <span class="flight-popup-flight-text">
                <?php echo $type === 'landing' ? 'Arrival' : 'Departure'; ?>
            </span>
            <span class="flight-popup-flight-id">
                <?php echo isset($flight_number) && $flight_number ? esc_html($flight_number) : 'N/A'; ?>
            </span>
        </div>

        <button type="button" class="flight-popup-close-btn">
            <?php echo file_get_contents(get_template_directory() . '/assets/images/x.svg'); ?>
        </button>
    </div>

    <div class="flight-popup-header">
        <div class="flight-popup-header-title">
            <?php
            $destinationDisplay = isset($destination) && $destination ? esc_html($destination) : 'Unknown Destination';
            echo $type === 'landing' ? "{$destinationDisplay} to Split" : "Split to {$destinationDisplay}";
            ?>
        </div>
        <div class="flight-popup-header-text <?php echo strtolower(str_replace(" ", "-", $comment)); ?>">
            <?php echo $comment; ?>
        </div>
        <a class="flight-popup-header-btn" href="#">Follow this flight</a>
    </div>

    <div class="flight-popup-details">
        <div class="flight-popup-details-col flight-popup-details-img-col">
            <!-- Placeholder logo -->
            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/airline-logo-placeholder.svg'); ?>" alt="Airline logo" />
        </div>

        <div class="flight-popup-details-col flight-popup-details-lg-col">
            <div class="flight-popup-details-title">Airline</div>
            <div class="flight-popup-details-text">
                <?php echo isset($airline) && $airline ? esc_html($airline) : 'N/A'; ?>
            </div>
        </div>

        <div class="flight-popup-details-col">
            <div class="flight-popup-details-title">Date</div>
            <div class="flight-popup-details-text">
                <?php
                if (!empty($schdate)) {
                    $date = date_create($schdate);
                    echo date_format($date, 'd.m.');
                } else {
                    echo 'N/A';
                }
                ?>
            </div>
        </div>

        <div class="flight-popup-details-col">
            <div class="flight-popup-details-title">Planned</div>
            <div class="flight-popup-details-text">
                <?php
                if (!empty($schtime)) {
                    $time = date_create($schtime);
                    echo date_format($time, 'H:i');
                } else {
                    echo 'N/A';
                }
                ?>
            </div>
        </div>

        <div class="flight-popup-details-col">
            <div class="flight-popup-details-title">Expected</div>
            <div class="flight-popup-details-text">
                <?php
                if (!empty($esttime)) {
                    $time = date_create($esttime);
                    echo date_format($time, 'H:i');
                } else {
                    echo 'N/A';
                }
                ?>
            </div>
        </div>

        <div class="flight-popup-details-col">
            <div class="flight-popup-details-title">Gate</div>
            <div class="flight-popup-details-text">
                <?php echo !empty($gate) ? esc_html($gate) : '—'; ?>
            </div>
        </div>
    </div>

    <div class="flight-popup-main">
        <div class="flight-popup-main-item">
            <?php echo file_get_contents(get_template_directory() . '/assets/images/airplane.svg'); ?>
            <div class="flight-popup-main-title">Passport check (for non-EU citizens)</div>
            <div class="flight-popup-main-text">
                <p>The passport check is performed on a counter located right at the entrance from the runway.</p>
                <p>For more information about Croatian and EU travel documents requirements visit: <a href="https://mvep.gov.hr/consular-information-22802/travel-information/22806">mvep.gov.hr</a></p>
            </div>
        </div>

        <div class="flight-popup-main-item">
            <div class="flight-popup-main-title">Baggage claim</div>
            <div class="flight-popup-main-text">
                <p>The next area immediately after Passport check counters is the Baggage Claim area. The logo of your airline will be displayed on the corresponding baggage claim belt.</p>
            </div>
        </div>

        <div class="flight-popup-main-item">
            <div class="flight-popup-main-title">Lobby pick-up area</div>
            <div class="flight-popup-main-text">
                <p>After claiming your baggage, you will make your way to the lobby through the pick-up area. If somebody is waiting for you at the airport, this is where they will be.</p>
            </div>
        </div>

        <div class="flight-popup-main-item">
            <div class="flight-popup-main-title">Departing from the airport</div>
            <div class="flight-popup-main-text">
                <p>Split Airport is connected to Split and the surrounding area through a dedicated Airport Shuttle Bus, Taxis, and Public Transportation.</p>
            </div>
        </div>
    </div>
</div>