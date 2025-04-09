<?php

namespace SplitAirport;

use SplitAirport\Models\Flight;

class SearchAPI
{

    public static function init()
    {
        add_action('rest_api_init', [static::class, 'registerSearchRestAPI']);
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

    public static function searchRestAPI(\WP_REST_Request $request)
    {

        $term = wp_strip_all_tags($request->get_param('term'));
        $type = wp_strip_all_tags($request->get_param('flightType'));
        $date = wp_strip_all_tags($request->get_param('flightDate'));
        $queryType = wp_strip_all_tags($request->get_param('queryType'));
        $offset =  wp_strip_all_tags($request->get_param('offset')) ?: 0;

        $flights = Flight::getSearchData($term, $date, $type, $queryType, $offset);


        if ($flights['posts']) {

            ob_start();

            if ($queryType === 'search') {

                if ($term) {
                    get_template_part('template-parts/blocks/search-data', null, [
                        'flights'       => $flights['posts'],
                        'term'          => $term,
                        'flightType'    => $type
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
                    <a href="#" class="load-more"><?php esc_html_e('Load More', 'split-airport'); ?></a>

<?php

                endif;
            }

            $flightsHTML = ob_get_clean();

            wp_send_json_success($flightsHTML);
        } else {

            ob_start();

            get_template_part('template-parts/blocks/no-flights');

            $NoFlightsHTML = ob_get_clean();
            wp_send_json_success($NoFlightsHTML);
        }
    }
}
