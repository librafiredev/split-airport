<?php

namespace SplitAirport\Migrations;

use SplitAirport\Storage\Database;

class Flight
{
    public static function createTables()
    {

        if (!file_exists(dirname(Database::getDbPath()))) {
            mkdir(dirname(Database::getDbPath()), 0755, true);
        }

        $db = new Database();
        $connestion = $db->getConnection();

        
        // Delete old data

       
        $connestion->exec("CREATE VIRTUAL TABLE IF NOT EXISTS flights_search USING fts5(flight_number, destination, airline)");
        $connestion->exec("DELETE FROM flights_search");

        $connestion->exec("CREATE  TABLE IF NOT EXISTS flights (
        ID INTEGER PRIMARY KEY,
        AD TEXT,
        acttime TEXT,
        comment TEXT,
        esttime TEXT,
        gate TEXT,
        parkingPosition TEXT,
        schdate TEXT,
        schtime TEXT,
        sifFromto TEXT,
        sifVia TEXT,
        via TEXT
        )");

         // Delete old data

        $connestion->exec("DELETE FROM flights");

        $db->closeConnestion();
    }
}
