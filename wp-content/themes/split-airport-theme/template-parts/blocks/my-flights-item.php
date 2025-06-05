 <?php

    use SplitAirport\Helpers\DateTimeFlight;

    extract($args);
    $type = strtolower($flight['AD']);
    $destinationDisplay = $type === 'arrival' ?  __('from', 'split-airport') . " " . $flight['destination'] : __('to', 'split-airport') . " " . $flight['destination'];
    ?>
 <div>
     <strong><?php echo $flight['flight_number']; ?></strong>
     <?php echo $destinationDisplay; ?> <strong><?php echo  $flight['esttime'] ? DateTimeFlight::formatTimeTableView($flight['esttime']) : ($flight['schtime'] ? DateTimeFlight::formatTimeTableView($flight['schtime']) : ''); ?></strong>
     <?php if ($type === 'departure'): ?> <?php esc_html_e('Gate', 'split-airport'); ?>
         <strong><?php echo $flight['gate']; ?></strong> <?php endif; ?>
     <strong><?php echo $flight['comment']; ?></strong>

 </div>