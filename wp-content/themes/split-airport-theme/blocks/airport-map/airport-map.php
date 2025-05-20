<?php 
/*
* Block Name: Airport Map
* Post Type: page 
*/

if( isset( $block['data']['preview_image_help'] )  ) :
    echo '<img src="'. $block['data']['preview_image_help'] .'" style="width:100%; height:auto;">';
else:

$initial_floor = 0;
$icon_size = 62;
$group_class_prefix = 'airport-map-group-';
$overlay_guide_class_prefix = 'airport-map-guide-';

if( ! function_exists('recoursively_render_map_categories') ) :

    function recoursively_render_map_categories(&$items, $additinal_data, $suffix = '', $level = 0, $options = array()) {
        ?>
        <ul class="map-sidebar-list map-sidebar-level-<?php echo $level; ?> <?php echo $level >= 2 ? 'map-sidebar-list-hidden' : ''; ?>">
            <?php
            foreach ($items as $idx => &$item) {
                $floor = $item['floor'];
                $group_idx = $item['group_index'];
                $new_sufix = $suffix . '-' . $idx;
                $has_toggle_sub_menu = !empty($item['children']) && $level == 1;
                $item_class = 'map-sidebar-item' . $new_sufix;
                $item['html_class'] = $item_class;
                $has_target_group = isset($item['group_index']);
                ?>
                <li class="map-sidebar-searchable <?php echo $item_class; ?>">
                    <button class="map-sidebar-btn <?php echo $has_toggle_sub_menu || (empty($item['children']) && $level != 0) ? 'map-sidebar-clickable' : 'map-sidebar-root'; ?> <?php echo $has_target_group ? 'has-target-group' : ''; ?>" type="button" data-target-floor="<?php echo $floor; ?>" data-target-group-class="<?php echo $options['group_class_prefix'] . $floor . '-' . $group_idx; ?>">
                        <?php if (!empty($item['icon_path'])): ?>
                            <img class="map-sidebar-item-icon" src="<?php echo $item['icon_path']; ?>" alt="Icon">
                        <?php endif; ?>
                        <?php if ($item['label']) : ?>
                            <?php echo $item['label']; ?>
                        <?php else: ?>
                            <?php $item['label'] = $additinal_data[$floor][$group_idx]['label']; ?>
                            <?php echo $additinal_data[$floor][$group_idx]['label']; ?>
                        <?php endif ?>
                        <?php if ( $has_toggle_sub_menu ) : ?>
                            <span class="map-sidebar-chev">
                                <?php echo file_get_contents(get_template_directory() . '/assets/images/arrow-down.svg'); ?>
                            </span>
                        <?php endif; ?>
                    </button>

                    <?php
                    if ( !empty($item['children']) ) {
                        recoursively_render_map_categories($item['children'], $additinal_data, $new_sufix, $level + 1, $options);
                    }
                    ?>
                </li>
                <?php
            }
            ?>
        </ul>

        <?php
    }

endif;

$floors_data = [
    array(
        'bg_path' => get_template_directory_uri() . "/assets/images/airport-map/floor-0.svg",
        'width' => 2650,
        'height' => 1469,
        'overlay_path' => get_template_directory_uri() . "/assets/images/airport-map/floor-0-overlay.svg",
        'top_overlay_path' => get_template_directory_uri() . "/assets/images/airport-map/floor-0-top-overlay.svg",
    ),
    array(
        'bg_path' => get_template_directory_uri() . "/assets/images/airport-map/floor-1.svg",
        'width' => 2470,
        'height' => 1462,
        'overlay_path' => get_template_directory_uri() . "/assets/images/airport-map/floor-1-overlay.svg",
    ),
    array(
        'bg_path' => get_template_directory_uri() . "/assets/images/airport-map/floor-2.svg",
        'width' => 2475,
        'height' => 1469,
        'overlay_path' => get_template_directory_uri() . "/assets/images/airport-map/floor-2-overlay.svg",
    ),
];
$icon_data = [
    // FLOOR 0
    [
        array(
            'label' => esc_html__('Lost & Found'),
            'items' => [
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/lost-n-found.svg',
                    'x' => 836,
                    'y' => 911,
                    'tooltip_side' => 'left',
                ),
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/lost-n-found.svg',
                    'x' => 1036,
                    'y' => 911,
                ),
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/lost-n-found-area.svg',
                    'x' => 927,
                    'y' => 920,
                    'width' => 82,
                    'height' => 40,
                    'type' => 'area',
                ),
            ],
        ),
        array(
            'label' => esc_html__('Info Pult'),
            'items' => [
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/info.svg',
                    'x' => 2085,
                    'y' => 437,
                    'tooltip_side' => 'left',
                ),
            ],
        ),
        array(
            'label' => esc_html__('Check-in counters'),
            'items' => [
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/plane-check-in.svg',
                    'x' => 1392,
                    'y' => 294,
                    
                ),
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/check-in-area.svg',
                    'x' => 1007,
                    'y' => 193,
                    'width' => 855,
                    'height' => 77,
                    'type' => 'area',
                ),
            ],
        ),
        array(
            'label' => esc_html__('Self check-in'),
            'items' => [
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/self-check-in-1.svg',
                    'x' => 1055,
                    'y' => 409,
                    
                ),
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/self-check-in-2.svg',
                    'x' => 1602,
                    'y' => 398,
                    'tooltip_side' => 'left',
                    
                ),
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/self-checkin-area.svg',
                    'x' => 1055,
                    'y' => 398,
                    'width' => 610,
                    'height' => 132,
                    'type' => 'area',
                    'override_z_index' => 2,
                ),
            ],
        ),
        array(
            'label' => esc_html__('flight tickets'),
            'items' => [
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/plane-boarding-pass-1.svg',
                    'x' => 2370,
                    'y' => 361,
                    'tooltip_side' => 'left',
                ),
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/plane-boarding-pass-1.svg',
                    'x' => 2156,
                    'y' => 437,
                    'tooltip_side' => 'left',
                ),
            ],
        ),
        array(
            'label' => esc_html__('VAT Refund'),
            'items' => [
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/refund.svg',
                    'x' => 954,
                    'y' => 294,
                    
                ),
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/refund-area.svg',
                    'x' => 920,
                    'y' => 280,
                    'width' => 99,
                    'height' => 102,
                    'type' => 'area',
                ),
            ],
        ),
        array(
            'label' => esc_html__('Meeting Point'),
            'items' => [
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/shrink.svg',
                    'x' => 1579,
                    'y' => 734,
                ),
            ],
        ),
        array(
            'label' => esc_html__('ATM'),
            'items' => [
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/money-atm.svg',
                    'x' => 1243,
                    'y' => 1054,
                    'tooltip_side' => 'left',
                ),
            ],
        ),
        array(
            'label' => esc_html__('Bank'),
            'items' => [
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/bank.svg',
                    'x' => 1032,
                    'y' => 1183,
                    'tooltip_side' => 'left',
                ),
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/bank-area.svg',
                    'x' => 966,
                    'y' => 1148,
                    'width' => 195,
                    'height' => 133,
                    'type' => 'area',
                    'override_z_index' => 3,
                ),
            ],
        ),
        array(
            'label' => esc_html__('Exchange'),
            'items' => [
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/exchange.svg',
                    'x' => 1218,
                    'y' => 1184,
                ),
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/exchange-area.svg',
                    'x' => 1159,
                    'y' => 1148,
                    'width' => 179,
                    'height' => 133,
                    'type' => 'area',
                ),
            ],
        ),
        array(
            'label' => esc_html__('Departure Passenger Assistance'),
            'items' => [
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/wheelchair.svg',
                    'x' => 1528,
                    'y' => 103,
                ),
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/wheelchair.svg',
                    'x' => 1614,
                    'y' => 597,
                ),
            ],
        ),
        array(
            'label' => esc_html__('Store'),
            'items' => [
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/storefront.svg',
                    'x' => 2370,
                    'y' => 437,
                    'tooltip_side' => 'left',
                ),
            ],
        ),
        array(
            'label' => esc_html__('Baggage Claim'),
            'items' => [
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/baggage.svg',
                    'x' => 302,
                    'y' => 776,
                ),
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/baggage-area.svg',
                    'x' => 288,
                    'y' => 446,
                    'width' => 601,
                    'height' => 764,
                    'type' => 'area',
                    'override_z_index' => 2,
                ),
            ],
        ),
        array(
            // NOTE: this is just an overlay
            'items' => [
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/baggage-area-no-interact.svg',
                    'x' => 288,
                    'y' => 446,
                    'width' => 601,
                    'height' => 764,
                    'type' => 'area',
                    'override_z_index' => 3,
                ),
            ],
        ),
        array(
            'label' => esc_html__('Customs'),
            'items' => [
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/security-officer.svg',
                    'x' => 828,
                    'y' => 502,
                    'tooltip_side' => 'left',
                ),
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/customs-area.svg',
                    'x' => 908,
                    'y' => 384,
                    'width' => 125,
                    'height' => 187,
                    'type' => 'area',
                    'override_z_index' => 3,
                ),
            ],
        ),
        array(
            'label' => esc_html__('Passport control'),
            'items' => [
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/passport-control.svg',
                    'x' => 331,
                    'y' => 263,
                ),
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/passport-control.svg',
                    'x' => 552,
                    'y' => 183,
                ),
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/passport-control-area.svg',
                    'x' => 288,
                    'y' => 126,
                    'width' => 438,
                    'height' => 248,
                    'type' => 'area',
                    'override_z_index' => 3,
                ),
            ],
        ),
        array(
            'label' => esc_html__('European Union'),
            'items' => [
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/eu.svg',
                    'x' => 476,
                    'y' => 183,
                ),
            ],
        ),
        array(
            'label' => esc_html__('Rent a Car'),
            'items' => [
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/car-key.svg',
                    'x' => 1639,
                    'y' => 1227,
                ),
            ],
        ),
        array(
            'label' => esc_html__('Taxi'),
            'items' => [
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/taxi-sign.svg',
                    'x' => 1712,
                    'y' => 934,
                ),
            ],
        ),
        array(
            'label' => esc_html__('Shuttle Bus'),
            'items' => [
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/bus.svg',
                    'x' => 1639,
                    'y' => 934,
                ),
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/bus.svg',
                    'x' => 70,
                    'y' => 797,
                ),
            ],
        ),
        array(
            'label' => esc_html__('Bus Terminal'),
            'items' => [
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/bus.svg',
                    'x' => 1639,
                    'y' => 1297,
                ),
            ],
        ),
        array(
            'label' => esc_html__('Parking'),
            'items' => [
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/parking.svg',
                    'x' => 1639,
                    'y' => 1157,
                ),
            ],
        ),
        
    ],
    // FLOOR 1
    [
        array(
            'label' => esc_html__('Info Pult'),
            'items' => [
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/info.svg',
                    'x' => 1979,
                    'y' => 99,
                ),
            ],
        ),
        array(
            'label' => esc_html__('Departure Passenger Assistance'),
            'items' => [
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/wheelchair.svg',
                    'x' => 1342,
                    'y' => 162,
                ),
            ],
        ),
        array(
            'label' => esc_html__('Duty free shops'),
            'items' => [
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/duty-free.svg',
                    'x' => 693,
                    'y' => 375,
                ),
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/duty-free.svg',
                    'x' => 540,
                    'y' => 622,
                ),
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/duty-free.svg',
                    'x' => 1874,
                    'y' => 284,
                ),
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/duty-free.svg',
                    'x' => 2104,
                    'y' => 335,
                ),
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/duty-free-area-0.svg',
                    'x' => 631,
                    'y' => 328,
                    'width' => 186,
                    'height' => 158,
                    'type' => 'area',
                    'override_z_index' => 3,
                ),
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/duty-free-area-1.svg',
                    'x' => 482,
                    'y' => 604,
                    'width' => 187,
                    'height' => 97,
                    'type' => 'area',
                    'override_z_index' => 3,
                ),
            ],
        ),
        array(
            'label' => esc_html__('Smoking Area'),
            'items' => [
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/allowances-smoking.png',
                    'x' => 566,
                    'y' => 358,
                ),
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/smoking-area.svg',
                    'x' => 492,
                    'y' => 326,
                    'width' => 96,
                    'height' => 94,
                    'type' => 'area',
                ),
            ],
        ),
        array(
            'label' => esc_html__('Business lounge'),
            'items' => [
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/sofa-couch.svg',
                    'x' => 448,
                    'y' => 813,
                ),
            ],
        ),
        array(
            'label' => esc_html__('International Departure'),
            'items' => [
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/airplane-takeoff.svg',
                    'x' => 704,
                    'y' => 227,
                ),
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/airplane-takeoff.svg',
                    'x' => 2082,
                    'y' => 227,
                    'tooltip_side' => 'left',
                    'override_z_index' => 10,
                ),
            ],
        ),
        array(
            'label' => esc_html__('Domestic Departure'),
            'items' => [
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/airplane-takeoff.svg',
                    'x' => 246,
                    'y' => 915,
                ),
            ],
        ),
        array(
            'label' => esc_html__('Passport Control'),
            'items' => [
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/passport-control.svg',
                    'x' => 255,
                    'y' => 689,
                ),
            ],
        ),
        array(
            'label' => esc_html__('Security Check Point'),
            'items' => [
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/security.svg',
                    'x' => 820,
                    'y' => 910,
                ),
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/security-area.svg',
                    'x' => 478,
                    'y' => 768,
                    'width' => 457,
                    'height' => 382,
                    'type' => 'area',
                    'override_z_index' => 3,
                ),
            ],
        ),
        array(
            'label' => esc_html__('European Union'),
            'items' => [
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/eu.svg',
                    'x' => 255,
                    'y' => 492,
                ),
            ],
        ),
        array(
            'label' => esc_html__('Restaurant'),
            'items' => [
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/restaurant.svg',
                    'x' => 295,
                    'y' => 1204,
                ),
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/restaurant.svg',
                    'x' => 1518,
                    'y' => 389,
                ),
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/rest-area-1.svg',
                    'x' => 197,
                    'y' => 1137,
                    'width' => 260,
                    'height' => 196,
                    'type' => 'area',
                ),
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/rest-area-2.svg',
                    'x' => 1447,
                    'y' => 374,
                    'width' => 204,
                    'height' => 92,
                    'type' => 'area',
                ),
            ],
        ),
        array(
            'label' => esc_html__('Caffe Bar'),
            'items' => [
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/coffe.svg',
                    'x' => 402,
                    'y' => 279,
                ),
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/coffe.svg',
                    'x' => 1766,
                    'y' => 236,
                ),
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/coffe.svg',
                    'x' => 1518,
                    'y' => 490,
                ),
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/coffe.svg',
                    'x' => 2184,
                    'y' => 236,
                ),
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/cafe-area-1.svg',
                    'x' => 318,
                    'y' => 233,
                    'width' => 231,
                    'height' => 168,
                    'type' => 'area',
                    'override_z_index' => 3,
                ),
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/cafe-area-2.svg',
                    'x' => 1447,
                    'y' => 475,
                    'width' => 204,
                    'height' => 92,
                    'type' => 'area',
                    'override_z_index' => 3,
                ),
            ],
        ),


    ],
    // FLOOR 2
    [
        array(
            'label' => esc_html__('Business lounge'),
            'items' => [
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/sofa-couch.svg',
                    'x' => 328,
                    'y' => 701,
                ),
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/sofa-couch.svg',
                    'x' => 328,
                    'y' => 886,
                ),
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/lounge-1.svg',
                    'x' => 275,
                    'y' => 632,
                    'width' => 182,
                    'height' => 208,
                    'type' => 'area',
                    'override_z_index' => 3,
                ),
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/lounge-2.svg',
                    'x' => 275,
                    'y' => 842,
                    'width' => 182,
                    'height' => 149,
                    'type' => 'area',
                    'override_z_index' => 3,
                ),
            ],
        ),
        array(
            'label' => esc_html__('Restaurant'),
            'items' => [
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/restaurant.svg',
                    'x' => 517,
                    'y' => 349,
                ),
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/rest-area-3.svg',
                    'x' => 457,
                    'y' => 277,
                    'width' => 182,
                    'height' => 215,
                    'type' => 'area',
                ),
            ],
        ),
        array(
            'label' => esc_html__('Caffe Bar'),
            'items' => [
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/coffe.svg',
                    'x' => 345,
                    'y' => 349,
                ),
                array(
                    'shape_path' => get_template_directory_uri() . '/assets/images/airport-map/cafe-area-3.svg',
                    'x' => 296,
                    'y' => 277,
                    'width' => 161,
                    'height' => 215,
                    'type' => 'area',
                ),
            ],
        ),
    ]
];
$category_data = [
    array(
        'label' => esc_html__('Check-in'),
        'icon_path' => get_template_directory_uri() . "/assets/images/airport-map/check-in-icon.svg",
        'children' => [
            array(
                'group_index' => 2,
                'floor' => 0,
            ),
            array(
                'label' => esc_html__('Self Check-in Kiosks'),
                'group_index' => 3,
                'floor' => 0,
            ),
            array(
                'label' => esc_html__('Flight Ticket Purchase'),
                'group_index' => 4,
                'floor' => 0,
            ),
            array(
                'group_index' => 5,
                'floor' => 0,
            ),
        ],
    ),
    array(
        'label' => esc_html__('Services'),
        'icon_path' => get_template_directory_uri() . "/assets/images/airport-map/users-icon.svg",
        'children' => [
            array(
                'label' => esc_html__('Info pult'),
                'children' => [
                    array(
                        'label' => esc_html__('Info on floor 0'),
                        'group_index' => 1,
                        'floor' => 0,
                    ),
                    array(
                        'label' => esc_html__('Info on floor 1'),
                        'group_index' => 0,
                        'floor' => 1,
                    ),
                ]
            ),
            array(
                'group_index' => 6,
                'floor' => 0,
            ),
            array(
                'group_index' => 7,
                'floor' => 0,
            ),
            array(
                'group_index' => 8,
                'floor' => 0,
            ),
            array(
                'label' => esc_html__('Exchange Office'),
                'group_index' => 9,
                'floor' => 0,
            ),
            array(
                'label' => esc_html__('Departure Passenger Assistance'),
                'children' => [
                    array(
                        'label' => esc_html__('Assistance on floor 0'),
                        'group_index' => 10,
                        'floor' => 0,
                    ),
                    array(
                        'label' => esc_html__('Assistance on floor 1'),
                        'group_index' => 1,
                        'floor' => 1,
                    ),
                ]
            ),
            array(
                'group_index' => 2,
                'floor' => 1,
            ),
            array(
                'group_index' => 3,
                'floor' => 1,
            ),
            array(
                'label' => esc_html__('Business lounges'),
                'children' => [
                    array(
                        'label' => esc_html__('Lounges on floor 1'),
                        'group_index' => 4,
                        'floor' => 1,
                    ),
                    array(
                        'label' => esc_html__('Lounges on floor 2'),
                        'group_index' => 0,
                        'floor' => 2,
                    ),
                ]
            ),
            array(
                'label' => esc_html__('International Departure Area'),
                'group_index' => 5,
                'floor' => 1,
            ),
            array(
                'label' => esc_html__('Domestic Departure Area'),
                'group_index' => 6,
                'floor' => 1,
            ),
            array(
                'group_index' => 11,
                'floor' => 0,
            ),
        ],
    ),
    array(
        'label' => esc_html__('Baggage'),
        'icon_path' => get_template_directory_uri() . "/assets/images/airport-map/baggage-icon.svg",
        'children' => [
            array(
                'group_index' => 0,
                'floor' => 0,
            ),
            array(
                'group_index' => 12,
                'floor' => 0,
            ),
        ],
    ),
    array(
        'label' => esc_html__('Passanger controls'),
        'icon_path' => get_template_directory_uri() . "/assets/images/airport-map/security-icon.svg",
        'children' => [
            array(
                'label' => esc_html__('Passport control'),
                'children' => [
                    array(
                        'label' => esc_html__('Passport control on floor 0'),
                        'group_index' => 15,
                        'floor' => 0,
                    ),
                    array(
                        'label' => esc_html__('Passport control on floor 1'),
                        'group_index' => 7,
                        'floor' => 1,
                    ),
                ]
            ),
            array(
                'group_index' => 8,
                'floor' => 1,
            ),
            array(
                'group_index' => 14,
                'floor' => 0,
            ),
            array(
                'label' => esc_html__('European Union'),
                'children' => [
                    array(
                        'label' => esc_html__('European Union on floor 0'),
                        'group_index' => 16,
                        'floor' => 0,
                    ),
                    array(
                        'label' => esc_html__('European Union on floor 1'),
                        'group_index' => 9,
                        'floor' => 1,
                    ),
                ]
            ),

        ],
    ),
    array(
        'label' => esc_html__('Parking'),
        'icon_path' => get_template_directory_uri() . "/assets/images/airport-map/parking-icon.svg",
        'children' => [
            array(
                'group_index' => 17,
                'floor' => 0,
            ),
            array(
                'group_index' => 18,
                'floor' => 0,
            ),
            array(
                'group_index' => 19,
                'floor' => 0,
            ),
            array(
                'group_index' => 20,
                'floor' => 0,
            ),

        ],
    ),
    array(
        'label' => esc_html__('Restaurants & Caffe bars'),
        'icon_path' => get_template_directory_uri() . "/assets/images/airport-map/restaurant-icon.svg",
        'children' => [
            array(
                'label' => esc_html__('Restaurants'),
                'children' => [
                    array(
                        'label' => esc_html__('Restaurants on floor 1'),
                        'group_index' => 10,
                        'floor' => 1,
                    ),
                    array(
                        'label' => esc_html__('Restaurants on floor 2'),
                        'group_index' => 1,
                        'floor' => 2,
                    ),
                ]
            ),
            array(
                'label' => esc_html__('Caffee bars'),
                'children' => [
                    array(
                        'label' => esc_html__('Caffee bars on floor 1'),
                        'group_index' => 11,
                        'floor' => 1,
                    ),
                    array(
                        'label' => esc_html__('Caffee bars on floor 2'),
                        'group_index' => 2,
                        'floor' => 2,
                    ),
                ]
            ),
        ],
    ),
];
$guides_data = [
    [
        array(
            'label' => esc_html__('Local arrival'),
            'image_path' => get_template_directory_uri() . "/assets/images/airport-map/floor-0-local-arrivals.svg",
            'target_class' => 'map-guide-local-arrivals',
        ),
        array(
            'label' => esc_html__('Local departure'),
            'image_path' => get_template_directory_uri() . "/assets/images/airport-map/floor-0-local-departure.svg",
            'target_class' => 'map-guide-local-departure',
            'override_z_index' => 18,
            'guide_tooltips' => [
                array(
                    'label' => esc_html__('2. Esclator to l2'),
                    'x' => 1300,
                    'y' => 500,
                    'tooltip_side' => 'left',
                ),
                array(
                    'label' => esc_html__('1. Check in'),
                    'x' => 1525,
                    'y' => 330,
                    'tooltip_side' => 'right',
                ),
            ],
        ),
        array(
            'label' => esc_html__('International arrival'),
            'image_path' => get_template_directory_uri() . "/assets/images/airport-map/floor-0-local-arrivals.svg",
            'target_class' => 'map-guide-international-arrivals',
        ),
        array(
            'label' => esc_html__('International departure'),
            'image_path' => get_template_directory_uri() . "/assets/images/airport-map/floor-0-international-departure.svg",
            'target_class' => 'map-guide-international-departure',
            'override_z_index' => 18,
            'guide_tooltips' => [
                array(
                    'label' => esc_html__('2. Esclator to l2'),
                    'x' => 1300,
                    'y' => 500,
                    'tooltip_side' => 'left',
                ),
                array(
                    'label' => esc_html__('1. Check in'),
                    'x' => 1525,
                    'y' => 330,
                    'tooltip_side' => 'right',
                ),
            ],
        ),
        array(
            'label' => esc_html__('Baggage claim'),
            'image_path' => get_template_directory_uri() . "/assets/images/airport-map/floor-0-baggage-claim.svg",
            'target_class' => 'map-guide-baggage-claim',
            'override_z_index' => 10,
        ),
        array(
            'label' => esc_html__('Baggage departure'),
            'image_path' => get_template_directory_uri() . "/assets/images/airport-map/floor-0-baggage-departure.svg",
            'target_class' => 'map-guide-baggage-departure',
            'override_z_index' => 10,
        ),
    ],[
        array(
            'label' => esc_html__('Local departure'),
            'image_path' => get_template_directory_uri() . "/assets/images/airport-map/floor-1-local-departure.svg",
            'target_class' => 'map-guide-local-departure',
        ),
        array(
            'label' => esc_html__('International departure'),
            'image_path' => get_template_directory_uri() . "/assets/images/airport-map/floor-1-international-departure.svg",
            'target_class' => 'map-guide-international-departure',
        ),

    ],
    []
];

$max_aspect_ratio = null;

foreach ($floors_data as $key => $value) {
    $c_ar = $value['width'] / max($value['height'], 1);
    if ($max_aspect_ratio == null) {
        $max_aspect_ratio = $c_ar;
    } else {
        $max_aspect_ratio = min($c_ar, $max_aspect_ratio);
    }
}

?>

    <section class="airport-map-wrapper">
        <div class="container" style="position: relative;">
            <button type="button" onclick="history.go(-1)" class="page-hero-back-btn airport-map-back-btn">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M11.765 23.6263L0.8 13.7998C0.318633 13.3249 0.0326416 12.6815 0 12C0.0330377 11.3186 0.318966 10.6753 0.8 10.2002L11.765 0.373699C12.1426 0.0245768 12.6757 -0.0902977 13.1598 0.0731377C13.644 0.236573 14.004 0.652946 14.1018 1.16255C14.1997 1.67215 14.0201 2.19587 13.632 2.53283L5.166 10.1167C5.08832 10.1869 5.06134 10.2987 5.09822 10.3975C5.1351 10.4963 5.22815 10.5616 5.332 10.5616L22.587 10.5616C23.3674 10.5616 24 11.2056 24 12C24 12.7944 23.3674 13.4384 22.587 13.4384L5.332 13.4384C5.22785 13.4383 5.13456 13.504 5.09784 13.6032C5.06112 13.7024 5.08868 13.8144 5.167 13.8843L13.632 21.4672C14.0201 21.8041 14.1997 22.3278 14.1018 22.8375C14.004 23.3471 13.644 23.7634 13.1598 23.9269C12.6757 24.0903 12.1426 23.9754 11.765 23.6263Z" fill="#5B5B5B" />
                </svg>
            </button>
            <h1 class="page-hero-title airport-map-title"><?php esc_html_e('Interactive map'); ?></h1>
        </div>
        <div class="airport-map-container">
            <div class="airport-map-sidebar">
                <div class="airport-map-sidebar-sarch-wrap">
                    <img src="<?php echo get_template_directory_uri() . "/assets/images/lg-search.svg"; ?>" alt="Sarch icon" class="airport-map-sarch-icon">
                    <input type="text" class="airport-map-search" placeholder="<?php esc_attr_e('Search airport map'); ?>" />
                </div>
                <div class="airport-map-sidebar-menu">
                    <?php recoursively_render_map_categories($category_data, $icon_data, '', 0, array('group_class_prefix' => $group_class_prefix)); ?>
                    <div class="airport-map-no-results hidden-no-results"><?php esc_html_e('No results found for: ') ?>“<span class="airport-map-search-term"></span>”</div>
                </div>
            </div>
            <div class="airport-map-main">
                <div class="airport-map-guide-cbs">
                    <div class="airport-map-guide-cbs-floor">
                        <div class="airport-map-guide-m-label"><?php echo esc_html__('Airport guide:'); ?></div>
                        <div class="airport-map-guide-cbs-only">
                            <?php $guides_on_floors = array(); ?>
                            <?php foreach ($guides_data as $g_floor_idx => $guide_group) {
                                foreach ($guide_group as $guide_item) {
                                    $target_class = $guide_item['target_class'];
                                    if (empty($guides_on_floors[$target_class])) {
                                        $guides_on_floors[$target_class] = [$g_floor_idx];
                                    } else {
                                        $guides_on_floors[$target_class][] = $g_floor_idx;
                                    }
                                }
                            } 
                            ?>

                            <?php $existing_guide_gr = []; ?>
                            <?php foreach ($guides_data as $g_floor_idx => $guide_group) : ?>
                                <?php if (empty($guide_group)) : ?>
                                    <div class="airport-map-no-guide airport-guide-cb-<?php echo $g_floor_idx; ?> <?php echo $g_floor_idx == $initial_floor ? 'is-active-cbs' : '' ?>">
                                        <?php if($g_floor_idx == 2) : ?>
                                            <?php esc_html_e('No guides on floor 2'); ?>
                                        <?php else: ?>
                                            <?php esc_html_e('No guides on current floor'); ?>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($guide_group as $guide_index => $guide_item) : ?>
                                        <?php 
                                        $target_class = $guide_item['target_class'];
                                        if (in_array($target_class, $existing_guide_gr, true)) {
                                            continue;
                                        } 
                                        $existing_guide_gr[] = $target_class;
                                        ?>
                                        <label class="airport-map-guide-cb-wrap <?php echo $g_floor_idx == $initial_floor ? 'is-active-cbs' : '' ?><?php if (!empty($guides_on_floors[$target_class])) { foreach ($guides_on_floors[$target_class] as $flr) { echo ' airport-guide-cb-' . $flr; } } ?>" style="transition-delay: <?php echo $guide_index * 100; ?>ms"><input type="checkbox" class="airport-map-guide-cb" data-target-guide-class="<?php echo $target_class; ?>" /><span><?php echo $guide_item['label']; ?></span></label>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="airport-map-floors" style="aspect-ratio: <?php echo $max_aspect_ratio; ?>;">
                    <?php $floor_controls_html = '<div class="airport-map-floor-btns">'; ?>
                    <div class="airport-map-floors-inner">
                        <div class="airport-map-pannable">
                            <?php foreach ($floors_data as $floor_idx => $floor) : ?>
                                <?php $floor_controls_html .= '<button type="button" class="airport-map-floor-btn ' . ($floor_idx == $initial_floor ? 'current-floor-btn' : '') . '" data-target-floor-idx="'.$floor_idx.'"><span>'.$floor_idx.'</span></button>'; ?>
                                <div class="airport-map-floor <?php echo $floor_idx == $initial_floor ? 'airport-map-active-floor' : ''; ?>" data-floor-idx="<?php echo $floor_idx; ?>">
                        
                                    <div class="airport-map-wrap">
                                        <img src="<?php echo $floor['bg_path']; ?>" alt="" />
                                        <?php if (!empty($floor['overlay_path'])) : ?>
                                            <img class="airport-map-fg-overlay" src="<?php echo $floor['overlay_path']; ?>" alt="" />
                                        <?php endif; ?>
                                        <?php if (!empty($floor['top_overlay_path'])) : ?>
                                            <img class="airport-map-fg-overlay top-overlay" src="<?php echo $floor['top_overlay_path']; ?>" alt="" />
                                        <?php endif; ?>
                                        <?php $groups = $icon_data[$floor_idx]; ?>
                                        <?php foreach ($groups as $g_idx => $group) : ?>
                                            <div class="airport-map-group <?php echo $group_class_prefix; ?><?php echo $floor_idx; ?>-<?php echo $g_idx; ?>">
                                                <?php $items = $group['items']; ?>
                                                <?php foreach ($items as $i_key => $item) : ?>
                                                    <?php
                                                    $shape_type = $item['type'] ?: 'icon';
                                                    $shape_path = $item['shape_path'];
                                                    $pos_x = $item['x'];
                                                    $pos_y = $item['y'];
                                                    if ($shape_type != 'area') {
                                                        $pos_x += $icon_size * .5;
                                                        $pos_y += $icon_size * .5;
                                                    }
                                                    $pos_x_percent = 100 * $pos_x / $floor['width'];
                                                    $pos_y_percent = 100 * $pos_y / $floor['height'];
                                                    ?>
                                                    <?php if ( $shape_type == 'area' ) : ?>
                                                        <?php
                                                        $width_percent = 100 * $item['width'] / $floor['width'];
                                                        $height_percent = 100 * $item['height'] / $floor['height'];
                                                        ?>
                                                        <div class="airport-map-shape-wrap airport-map-shape-wrap-area" style="left: <?php echo $pos_x_percent; ?>%; top: <?php echo $pos_y_percent; ?>%; width: <?php echo $width_percent; ?>%; height: <?php echo $height_percent; ?>%;<?php echo isset($item['override_z_index']) ? ' z-index:' . $item['override_z_index'] : ''; ?>" data-original-x="<?php echo $item['x']; ?>" data-original-y="<?php echo $item['y']; ?>">
                                                            <div
                                                                class="airport-map-shape airport-map-<?php echo $shape_type; ?>"
                                                            >
                                                                <?php echo file_get_contents($shape_path); ?>
                                                            </div>
                                                        </div>
                                                    <?php else: ?>
                                                        <?php
                                                        $width_percent = 100 * $icon_size / $floor['width'];
                                                        $height_percent = 100 * $icon_size / $floor['height'];
                                                        ?>
                                                        <div class="airport-map-shape-wrap airport-map-shape-wrap-icon" style="left: <?php echo $pos_x_percent; ?>%; top: <?php echo $pos_y_percent; ?>%; width: <?php echo $width_percent; ?>%; height: <?php echo $height_percent; ?>%;<?php echo isset($item['override_z_index']) ? ' z-index:' . $item['override_z_index'] : ''; ?>" data-original-x="<?php echo $item['x']; ?>" data-original-y="<?php echo $item['y']; ?>">
                                                            <div
                                                                class="airport-map-shape airport-map-<?php echo $shape_type; ?>"
                                                            >
                                                                <img class="airport-map-icon-img" src="<?php echo $shape_path; ?>" />
                                                            </div>
                                                            <div class="airport-map-tooltip <?php echo $item['tooltip_side'] ?: 'right'; ?>-tooltip"><?php echo $group['label']; ?></div>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php endforeach ?>
                                            </div>
                                        <?php endforeach ?>
                        
                                        <?php $guide_group = $guides_data[$floor_idx]; ?>
                                        <?php if (!empty($guide_group)) : ?>
                                            <?php foreach ($guide_group as $guide_index => $guide_item) : ?>
                                                <div class="airport-map-guide-wrap <?php echo $guide_item['target_class']; ?>" style="<?php echo isset($guide_item['override_z_index']) ? 'z-index:' . $guide_item['override_z_index'] : ''; ?>"><img class="" src="<?php echo $guide_item['image_path']; ?>" />
                                                    <?php if (!empty($guide_item['guide_tooltips'])) : ?>
                                                        <?php foreach ($guide_item['guide_tooltips'] as $key => $guide_tooltip) :
                                                            $pos_x_percent = 100 * $guide_tooltip['x'] / $floor['width'];
                                                            $pos_y_percent = 100 * $guide_tooltip['y'] / $floor['height'];
                                                            ?>
                                                            <div class="airport-map-tooltip airport-map-tooltip-guide guide-<?php echo $guide_tooltip['tooltip_side'] ?: 'right'; ?>-tooltip" style="left: <?php echo $pos_x_percent; ?>%; top: <?php echo $pos_y_percent; ?>%;"><?php echo $guide_tooltip['label']; ?></div>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                        
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php echo $floor_controls_html . '</div>'; ?>
                </div>
            </div>
        </div>
    </section><!-- .airport-map-wrapper-->

    <script>
        if (typeof window.airportMaps == 'undefined') {
            window.airportMaps = []
        }
        
        window.airportMaps.push({
            categories: <?php echo json_encode($category_data); ?>,
            currentFloor: <?php echo $initial_floor; ?>,
            floorsData: <?php echo json_encode($floors_data); ?>,
        })
    </script>
    
<?php endif; ?>