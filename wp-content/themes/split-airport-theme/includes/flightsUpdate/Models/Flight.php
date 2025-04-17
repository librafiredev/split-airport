<?php

namespace SplitAirport\Models;

use SplitAirport\Helpers\DateTimeFlight;
use SplitAirport\Storage\Database;
use SplitAirport\Storage\Files;

class Flight
{

    private static $searchPerPage = 3;
    private static $postsPerPage = 7;

    public static function getAirlineByTitle($title)
    {
        global $wpdb;

        $airline = $wpdb->get_row(
            $wpdb->prepare(
                "
                SELECT *
                FROM {$wpdb->prefix}posts
                WHERE post_title = %s
                AND post_type = 'airline'
                AND post_status = 'publish'
                LIMIT 1
                ",
                $title
            ),
            ARRAY_A
        );

        return $airline;
    }


    public static function getFlightByID($ID)
    {
        $db = new Database();
        $connection = $db->getConnection();

        $sql = $connection->prepare("
        SELECT
            fs.flight_number,
            fs.destination,
            fs.airline,
            fm.ID,
            fm.AD,
            fm.acttime,
            fm.comment,
            fm.esttime,
            fm.gate,
            fm.parkingPosition,
            fm.schdate,
            fm.schtime,
            fm.sifFromto,
            fm.sifVia,    
            fm.via
        FROM flights_search fs
        JOIN flights fm ON fs.rowid = fm.ID
        WHERE fm.ID = :ID  
    ");

        $sql->bindValue(':ID', $ID);
        $result = $sql->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
        $db->closeConnestion();

        return $row;
    }


    public static function getSearchData(string $term = "", string $date, string $type = 'arrivals', string $destination = "", string $airline = "", string $earlierFlights = "", string $queryType = 'query', int $offset = 0)
    {
        $db = new Database();
        $connection = $db->getConnection();
        $ftsTerm = $term . '*';
        $searchWhere = "";
        $pagination = "";
        $destinationWhere = "";
        $airlineWhere = "";
        $flightsTimeWhere = " date(fm.schtime) = date(:schdate)
        AND time(fm.schtime) >= time(:schtime) ";


        if (!$date) {
            $date = DateTimeFlight::todayDate();
        }

        if ($term) {
            $searchWhere = " AND flights_search MATCH :term";
        }

        if ($queryType === 'search') {
            $pagination = ' limit ' . self::$searchPerPage;
        } else {
            $pagination = ' limit ' . self::$postsPerPage . ' ' . 'offset ' . $offset;

            if ($destination) {
                $destinationWhere = ' AND fs.destination= :destination';
            }

            if ($airline) {
                $airlineWhere = ' AND fs.airline= :airline';
            }

            if ($earlierFlights === 'show') {
                $flightsTimeWhere = " date(fm.schtime) = date(:schdate)
                AND time(fm.schtime) < time(:schtime) ";
            }
        }

        $sql = $connection->prepare(
            "
        SELECT
            fs.flight_number,
            fs.destination,
            fs.airline,
            fm.ID,
            fm.AD,
            fm.acttime,
            fm.comment,
            fm.esttime,
            fm.gate,
            fm.parkingPosition,
            fm.schdate,
            fm.schtime,
            fm.sifFromto,
            fm.sifVia,    
            fm.via,
            COUNT(*) OVER () AS total_results
        FROM flights_search fs
        JOIN flights fm ON fs.rowid = fm.ID
        WHERE " . $flightsTimeWhere . "  
        AND fm.AD = :type"
                . $searchWhere
                . $destinationWhere
                . $airlineWhere . "
        ORDER BY fm.schtime
        " . $pagination
        );

        if ($sql) {
            if ($term) {
                $sql->bindValue(':term', $ftsTerm);
            }

            if ($destination) {
                $sql->bindValue(':destination', $destination);
            }

            if ($airline) {
                $sql->bindValue(':airline', $airline);
            }

            $sql->bindValue(':schdate', $date);
            $sql->bindValue(':schtime', DateTimeFlight::todayTime());
            $sql->bindValue(':type', strtoupper($type));

            $result = $sql->execute();

            $rows = [];
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $totalCount = $row['total_results'];
                unset($row['total_results']);
                $rows[] = $row;
            }

            $db->closeConnestion();

            return [
                'posts'             => $rows,
                'total_posts'       => isset($totalCount) ?: 0,
                'total_pages'       => isset($totalCount) ? ceil($totalCount / self::$postsPerPage) : 0,
                'current_page'      => isset($totalCount) ?  floor($offset / self::$postsPerPage) + 1 : 1,
            ];
        }
    }

    public static function insertData()
    {

        $db = new Database();
        $connection = $db->getConnection();

        $flightSearchSQLPrepare = $connection->prepare("INSERT INTO flights_search (flight_number, destination, airline) 
        VALUES (:flight_number, :destination, :airline)");

        $flightsSQLPrepare = $connection->prepare("
        INSERT INTO flights (
            ID, AD, acttime, comment, esttime, gate, parkingPosition,
            schdate, schtime, sifFromto, sifVia, via
        ) VALUES (
            :ID, :AD, :acttime, :comment, :esttime, :gate, :parkingPosition,
            :schdate, :schtime, :sifFromto, :sifVia, :via
        )
        ");

        $flights = Files::parseFiles();

        $currentFlights = $flights['current_flights'] ?? [];
      
        if ( $currentFlights) {
            foreach ( $currentFlights as $flight) {
                $flightSearchSQLPrepare->bindValue(':flight_number', $flight->brlet);
                $flightSearchSQLPrepare->bindValue(':destination', $flight->fromto);
                $flightSearchSQLPrepare->bindValue(':airline', $flight->operlong);
                $flightSearchSQLPrepare->execute();

                $ID = $connection->lastInsertRowID();

                $flightsSQLPrepare->bindValue(':ID', $ID);
                $flightsSQLPrepare->bindValue(':AD', $flight->AD ?? '');
                $flightsSQLPrepare->bindValue(':acttime', self::formatDate($flight->acttime) ?? '');
                $flightsSQLPrepare->bindValue(':comment', $flight->comment ?? '');
                $flightsSQLPrepare->bindValue(':esttime', self::formatDate($flight->esttime) ?? '');
                $flightsSQLPrepare->bindValue(':gate', $flight->gate ?? '');
                $flightsSQLPrepare->bindValue(':parkingPosition', $flight->parkingPosition ?? '');
                $flightsSQLPrepare->bindValue(':schdate', self::formatDate($flight->schdate) ?? '');
                $flightsSQLPrepare->bindValue(':schtime', self::formatDate($flight->schtime) ?? '');
                $flightsSQLPrepare->bindValue(':sifFromto', $flight->sifFromto ?? '');
                $flightsSQLPrepare->bindValue(':sifVia', $flight->sifVia ?? '');
                $flightsSQLPrepare->bindValue(':via', $flight->via ?? '');
                $flightsSQLPrepare->execute();
            }

            $db->closeConnestion();
        }
    }

    private static function formatDate(string $date): string
    {
        $clean = preg_replace('/Z\[UTC\]$/', '', $date);
        $date = new \DateTime($clean);
        return $date->format('Y-m-d H:i:s');
    }
}
