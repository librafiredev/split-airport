<?php
extract($args);
?>

<div class="search-data">

    <?php
    if ($flights):

        $destinations = array_map(function ($flight) {
            return $flight['destination'];
        }, $flights);

        $destinations = array_unique($destinations);

        $airlines = array_map(function ($flight) {
            return $flight['airline'];
        }, $flights);

        $airlines = array_unique($airlines);

    ?>

        <div class="search-data__flights">
            <?php
            foreach ($flights as $flight):

                $schTime = new DateTime($flight['schtime']);
                $schTime = $schTime->format('H:i');

            ?>

                <div class="search-data__flight">
                    <div class="search-data__flight-left">
                        <span class="search-data__flight-time"><?php echo $schTime; ?></span>
                        <span class="search-data__flight-town"><?php echo $flight['destination']; ?></span>
                    </div>
                    <div class="search-data__flight-right">
                        <span class="search-data__flight-number"><?php echo $flight['flight_number']; ?></span>
                        <span class="search-data__flight-company"><?php echo $flight['airline']; ?></span>
                    </div>
                </div>

            <?php endforeach; ?>
        </div>

        <a class="search-data__more" href="#"><?php esc_html_e('Show more flights with', 'split-airport') ?> <span class="search-data__term"><?php echo $term; ?></span></a>


        <div class="search-data__from-to">
            <p class="search-data__from-to-title"><?php echo $flights[0]['AD'] === 'DEPARTURE' ? __('All flights to', 'split-airport') : __('All flights from', 'split-airport')  ?></p>
            <div class="search-data__from-to-destinations">

                <?php foreach ($destinations as $destination): ?>
                    <a href=""><?php echo $destination; ?></a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="search-data__airline">
            <p class="search-data__airline-title"><?php echo  __('All flights with', 'split-airport'); ?></p>
            <div class="search-data__airline-companies">

                <?php foreach ($airlines as $airline): ?>
                    <a href=""><?php echo $airline; ?></a>
                <?php endforeach; ?>
            </div>
        </div>

    <?php endif; ?>

</div>