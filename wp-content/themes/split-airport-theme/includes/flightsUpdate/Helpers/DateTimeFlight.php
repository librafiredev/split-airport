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

    public static function getFlightTimeWindow(int $daysAhead = 4): array
    {
        $timezone = new DateTimeZone(self::$timezone);
        $now = new DateTime('now', $timezone);
        $startOfDay = (clone $now)->setTime(0, 0, 0);
        $endOfWindow = (clone $startOfDay)->modify("+{$daysAhead} days")->setTime(23, 59, 59); // Maybe goes now ()
        $before = floor(($now->getTimestamp() - $startOfDay->getTimestamp()) / 60);
        $after = floor(($endOfWindow->getTimestamp() - $now->getTimestamp()) / 60);

        return [
            'after' => max(0, $after),
            'before' => max(0, $before),
        ];
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
