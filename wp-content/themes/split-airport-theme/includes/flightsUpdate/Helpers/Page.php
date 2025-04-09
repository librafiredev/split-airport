<?php

namespace SplitAirport\Helpers;

class Page
{

    private static $pageUrl;


    public static function getSearchPage()
    {
        $pageID =  get_field('arrivals_timetable', 'options');
        return self::$pageUrl = get_page_link($pageID);
    }
}
