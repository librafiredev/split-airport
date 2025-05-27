<?php

namespace SplitAirport;

use SplitAirport\Models\Flight;
use SplitAirport\Models\MyFlights as MyFlightsModel;

class MyFlights
{

    public static function init()
    {
        add_action('rest_api_init', [static::class, 'registerMyFlightsRestRoute']);
        add_action('rest_api_init', [static::class, 'registerCheckMyFlightsRestRoute']);
    }

    public static function registerMyFlightsRestRoute()
    {
        register_rest_route('splitAirport/v1', '/myflights/', array(
            'methods' => 'POST',
            'callback' => [static::class, 'myFlights'],
            'permission_callback' => function (\WP_REST_Request $request) {
                return true;
            }
        ));
    }

    public static function registerCheckMyFlightsRestRoute()
    {
        register_rest_route('splitAirport/v1', '/check-my-flights/', array(
            'methods' => 'POST',
            'callback' => [static::class, 'checkMyFlights'],
            'permission_callback' => function (\WP_REST_Request $request) {
                return true;
            }
        ));
    }

    public static function checkMyFlights()
    {
        $myFlights = MyFlightsModel::getMyFlights();

        if ($myFlights) {
            $landedFlights = Flight::checkLandedFlights($myFlights);

            $myFlights = array_filter($myFlights, function ($flight) use ($landedFlights) {
                return !in_array($flight, $landedFlights);
            });

        
            $expire = time() + (10 * 365 * 24 * 60 * 60);
            setcookie('myFlights', json_encode($myFlights), $expire, '/');

            ob_start();

            get_template_part('template-parts/blocks/my-flights-dynamic-part', null, ['myFlights' => $myFlights]);

            $myFlightsHTML = ob_get_clean();

            wp_send_json_success([
                'myFlightsHTML' => $myFlightsHTML
            ]);
        } else {
            wp_send_json_success([
                'myFlightsHTML' => ''
            ]);
        }
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
        setcookie('myFlights', json_encode($myFlights), $expire, '/');

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
