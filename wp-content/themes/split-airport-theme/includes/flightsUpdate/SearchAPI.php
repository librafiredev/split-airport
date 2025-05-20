<?php

namespace SplitAirport;

use SplitAirport\Models\Flight;

class SearchAPI
{

    public static function init()
    {
        add_action('rest_api_init', [static::class, 'registerSearchRestAPI']);
        add_action('rest_api_init', [static::class, 'registerFlightAPI']);
    }

    public static function registerSearchRestAPI()
    {
        register_rest_route('splitAirport/v1', '/search/', array(
            'methods' => 'GET',
            'callback' => [static::class, 'searchRestAPI'],
            'permission_callback' => function (\WP_REST_Request $request) {
                return true;
            }
        ));
    }

    public static function registerFlightAPI()
    {
        register_rest_route('splitAirport/v1', '/flight/', array(
            'methods' => 'GET',
            'callback' => [static::class, 'flightRestAPI'],
            'permission_callback' => function (\WP_REST_Request $request) {
                return true;
            }
        ));
    }

    public static function flightRestAPI(\WP_REST_Request $request)
    {
        $ID = wp_strip_all_tags($request->get_param('ID'));
        $currentLanguage =  wp_strip_all_tags($request->get_param('currentLanguage')) ?: 'en';

         // Switch to current language

         if ($currentLanguage) {
            do_action('wpml_switch_language',  $currentLanguage);
        }
        

        $flight = Flight::getFlightByID($ID);

        if ($flight) {

            ob_start();
            get_template_part('template-parts/blocks/flight-popup-dynamic-part', null, $flight);
            $popupHTML = ob_get_clean();
            wp_send_json_success($popupHTML);
        } else {
            wp_send_json_error('No flight with provided ID');
        }
    }

    public static function searchRestAPI(\WP_REST_Request $request)
    {

        $term = wp_strip_all_tags($request->get_param('term'));
        $type = wp_strip_all_tags($request->get_param('flightType'));
        $date = wp_strip_all_tags($request->get_param('flightDate'));
        $queryType = wp_strip_all_tags($request->get_param('queryType'));
        $destination =  wp_strip_all_tags($request->get_param('destination'));
        $airline =  wp_strip_all_tags($request->get_param('airlineCompany'));
        $earlierFlights =  wp_strip_all_tags($request->get_param('earlierFlights'));
        $offset =  wp_strip_all_tags($request->get_param('offset')) ?: 0;
        $currentLanguage =  wp_strip_all_tags($request->get_param('currentLanguage')) ?: 'en';

        // Switch to current language

        if ($currentLanguage) {
            do_action('wpml_switch_language',  $currentLanguage);
        }

        $flights = Flight::getSearchData($term, $date, $type, $destination, $airline, $earlierFlights, $queryType, $offset);

        if ($flights['posts']) {

            ob_start();

            if ($queryType === 'search') {

                if ($term) {
                    get_template_part('template-parts/blocks/search-data', null, [
                        'flights'       => $flights['posts'],
                        'term'          => $term,
                        'flightType'    => $type,
                        'date'          => $date
                    ]);
                }
            } else {
                foreach ($flights['posts'] as $flight) {
                    get_template_part('template-parts/blocks/flight', null, [
                        'flight' => $flight
                    ]);
                }




                if ($flights['total_pages'] > $flights['current_page']):



?>
                    <!-- Uncomment this for load more pagination  -->
                    <!-- <a href="#" class="load-more"><?php //esc_html_e('Load More', 'split-airport'); 
                                                        ?></a> -->


<?php

                endif;
            }

            $flightsHTML = ob_get_clean();

            wp_send_json_success($flightsHTML);
        } else {

            ob_start();

            get_template_part('template-parts/blocks/no-flight', null, [
                'term'          => $term,
            ]);


            $NoFlightsHTML = ob_get_clean();
            wp_send_json_success($NoFlightsHTML);
        }
    }
}
