<?php

namespace SplitAirport\Storage;

class Database
{

    private static $dbPath;
    private \SQLite3 $connection;

    public function __construct()
    {

        $this->connection = new \SQLite3(self::getDbPath());
        $this->connection->busyTimeout(1000);
    }

    public static function getDbPath()
    {
        return self::$dbPath = get_template_directory() . '/includes/flightsUpdate/dbStorage/search.db';
    }

    public function getConnection(): \SQLite3
    {
        return $this->connection;
    }


    public function closeConnestion()
    {
        $this->connection->close();
    }
}
