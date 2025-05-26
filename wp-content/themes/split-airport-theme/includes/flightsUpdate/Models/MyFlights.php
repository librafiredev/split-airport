<?php

namespace SplitAirport\Models;

class MyFlights
{
    public static function getMyFlights()
    {
        $myFlights = [];

        if (isset($_COOKIE['myFlights'])) {
            $myFlights = @unserialize(stripslashes($_COOKIE['myFlights']));
            if (!is_array($myFlights)) {
                $myFlights = [];
            }
        }

        return $myFlights;
    }
}
