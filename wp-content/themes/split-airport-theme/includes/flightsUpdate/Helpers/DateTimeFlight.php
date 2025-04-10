<?php

namespace SplitAirport\Helpers;

use DateTime;
use DateTimeZone;

class DateTimeFlight
{


    protected static string $timezone = 'Europe/Zagreb';

    public static function todayDate(): string
    {
        $date = new DateTime('now', new DateTimeZone(self::$timezone));
        return $date->format('Y-m-d');
    }

    public static function todayTime(): string
    {
        return (new DateTime('now', new DateTimeZone(self::$timezone)))->format('H:i:s');
    }

    public static function formatTimeTableView(string $time): string
    {
        return (new DateTime($time, new DateTimeZone(self::$timezone)))->format('H:i');
    }

    public static function formatDateTableView(string $date): string
    {
        return (new DateTime($date, new DateTimeZone(self::$timezone)))->format('d.m.');
    }
}
