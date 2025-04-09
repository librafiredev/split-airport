<?php

namespace SplitAirport;

use SplitAirport\Migrations\Flight;
use SplitAirport\Models\Flight as ModelFlight;
use SplitAirport\SearchAPI;

class FlightsUpdate
{

    public static function init()
    {
        // Update data every minute

        add_action('init', function () {
            if (! wp_next_scheduled('update_flights_data')) {
                wp_schedule_event(time(), 'every_minute', 'update_flights_data');
            }
        });

        add_filter('cron_schedules', function ($schedules) {
            $schedules['every_minute'] = [
                'interval' => 60,
                'display'  => __('Every Minute')
            ];
            return $schedules;
        });

        add_action('update_flights_data', [static::class, 'UpdateFlightsData']);
        SearchAPI::init();
    }


    public static function UpdateFlightsData()
    {
        Flight::createTables();
        ModelFlight::insertData();
    }
}
