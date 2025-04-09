<?php

namespace SplitAirport\Models;

use SimplePie\Parse\Date;
use SplitAirport\Storage\Database;
use SplitAirport\Storage\Files;

class Flight
{

    private static $searchPerPage = 3;
    private static $postsPerPage = 11;


    public static function getSearchData(string $term = "", string $date, string $type = 'arrivals', string $queryType = 'query', int $offset = 0)
    {
        $db = new Database();
        $connection = $db->getConnection();
        $ftsTerm = $term . '*';
        $searchWhere = "";

        if (!$date) {
            $date = (new \DateTime('now'))->format('Y-m-d');
        }

        // Delete this, just example test
        
        $date = '2025-02-12';

        if ($term) {
            $searchWhere = " AND flights_search MATCH :term";
        }

        if($queryType === 'search') {
            $pagination = ' limit ' . self::$searchPerPage;
        }

        else {
            $pagination = ' limit ' . self::$postsPerPage . ' ' . 'offset ' . $offset;
        }

        $sql = $connection->prepare("
        SELECT 
            fs.flight_number,
            fs.destination,
            fs.airline,
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
        WHERE date(fm.schdate) = date(:schdate)
        AND fm.AD = :type" . $searchWhere . $pagination ."  
    ");
        if ($term) {
            $sql->bindValue(':term', $ftsTerm);
        }

        $sql->bindValue(':schdate', $date);
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

        $currentFlights = $flights['current_flights'][0] ?? [];
        $periodFlights = $flights['flights_for_period'][0] ?? [];

        $allFlights = array_merge($currentFlights, $periodFlights);

        if ($allFlights) {
            foreach ($allFlights as $flight) {
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
