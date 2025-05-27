<?php

namespace SplitAirport\Models;

class MyFlights
{
    public static function getMyFlights()
    {
        $myFlights = [];

        if (isset($_COOKIE['myFlights'])) {
            $myFlights = json_decode(stripslashes($_COOKIE['myFlights']));
            if (!is_array($myFlights)) {
                $myFlights = [];
            }
        }

        return $myFlights;
    }
}
