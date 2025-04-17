<?php

namespace SplitAirport;

use SplitAirport\Helpers\DateTimeFlight;
use SplitAirport\Migrations\Flight;
use SplitAirport\Models\Flight as ModelFlight;
use SplitAirport\SearchAPI;
use SplitAirport\Storage\Files;

class FlightsUpdate
{

    const FLIGHTS_UDPATE_API = 'https://restapi.split-airport.hr/as-frontend/schedule/current';

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
        self::fetchNewData();
        Flight::createTables();
        ModelFlight::insertData();
    }

    private static function fetchNewData()
    {

        $flightTimeWindow =  DateTimeFlight::getFlightTimeWindow();
        
        // Old flights + current flights + 4 days flights

        try {
            $response = wp_remote_get(self::FLIGHTS_UDPATE_API . '?before=' . $flightTimeWindow['before'] . '&after=' . $flightTimeWindow['after']);

            if (!is_wp_error($response) && (200 === wp_remote_retrieve_response_code($response))) {
                $responseBody = $response['body'];
                Files::manageUpdateFiles($responseBody, 'current_flights.json');
            }
        } catch (\Exception $e) {
            error_log('Fetch flights error: ' . $e->getMessage());
        }
    }
}
