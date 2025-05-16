<?php

namespace SplitAirport\Helpers;

class Page
{

    private static $pageUrl;


    public static function getSearchPage()
    {
        $options_slug = apply_filters('wpml_current_language', null) !== 'en' ? 'options_' . apply_filters('wpml_current_language', null) : "options";
        $pageID =  get_field('arrivals_timetable', $options_slug);
        return self::$pageUrl = get_page_link($pageID);
    }
}
