<?php

namespace SplitAirport;

use SplitAirport\Models\Flight;
use SplitAirport\Models\MyFlights as MyFlightsModel;

class MyFlights
{

    public static function init()
    {
        add_action('rest_api_init', [static::class, 'registerMyFlightsRestRoute']);
    }

    public static function registerMyFlightsRestRoute()
    {
        register_rest_route('splitAirport/v1', '/myFlights/', array(
            'methods' => 'POST',
            'callback' => [static::class, 'myFlights'],
            'permission_callback' => function (\WP_REST_Request $request) {
                return true;
            }
        ));
    }


    public static function myFlights(\WP_REST_Request $request)
    {
        $flightID = wp_strip_all_tags($request->get_param('flightID'));

        if (!$flightID) {
            return wp_send_json_error('No flight with provided ID', 422);
        }

        $myFlights = MyFlightsModel::getMyFlights();

        if (!in_array($flightID, $myFlights)) {
            $myFlights[] = $flightID;
        } else {
            $flightToDelete = array_search($flightID, $myFlights);
            unset($myFlights[$flightToDelete]);
            $myFlights = array_values($myFlights);
        }

        $expire = time() + (10 * 365 * 24 * 60 * 60);
        setcookie('myFlights', serialize($myFlights), $expire, '/');

        $total = count($myFlights);
 
        if (!empty($myFlights)) {
            $newestFlight = $myFlights[$total - 1];
            $newestFlight = Flight::getFlightByID($newestFlight);
        }

    
        ob_start();
        get_template_part('template-parts/blocks/my-flights-newest', null, ['newestFlight' => isset($newestFlight) ? $newestFlight : "", 'total' => $total]);
        $smallView = ob_get_clean();

        return wp_send_json_success(['smallView' => $smallView]);
    }
}
