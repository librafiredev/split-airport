<?php 
/*
* Block Name: Airport Map
* Post Type: page 
*/

if( isset( $block['data']['preview_image_help'] )  ) :
    echo '<img src="'. $block['data']['preview_image_help'] .'" style="width:100%; height:auto;">';
else:

$icon_size = 62;
$group_class_prefix = 'airport-map-group-';

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
                ?>
                <li class="map-sidebar-searchable <?php echo $item_class; ?>">
                    <button class="map-sidebar-btn <?php echo $has_toggle_sub_menu || (empty($item['children']) && $level != 0) ? 'map-sidebar-clickable' : ''; ?>" type="button" data-target-floor="<?php echo $floor; ?>" data-target-group-class="<?php echo $options['group_class_prefix'] . $floor . '-' . $group_idx; ?>">
                        <?php if ($item['label']) : ?>
                            <?php echo $item['label']; ?>
                        <?php else: ?>
                            <?php $item['label'] = $additinal_data[$floor][$group_idx]['label']; ?>
                            <?php echo $additinal_data[$floor][$group_idx]['label']; ?>
                        <?php endif ?>
                        <?php if ( $has_toggle_sub_menu ) : ?>
                            <span>
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
        'overlay_path' => '',
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
                )
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
    ],
    // FLOOR 1
    [
        // array(
        //     'label' => '',
        //     'category' => 1,
        //     'items' => [
        //         array(
        //             'shape_path' => '',
        //         ),
        //         array(
        //             'shape_path' => '',
        //         ),
        //         array(
        //             'shape_path' => '',
        //             'type' => 'area',
        //         )
        //     ],
        // ),
    ],
];
$category_data = [
    array(
        'label' => esc_html__('Baggage'),
        'children' => [
            array(
                'group_index' => 0,
                'floor' => 0,
            ),
        ],
    ),
    array(
        'label' => esc_html__('Services'),
        'children' => [
            array(
                'label' => esc_html__('Info pult'),
                'children' => [
                    array(
                        'label' => esc_html__('Info on floor 0'),
                        'group_index' => 1,
                        'floor' => 0,
                    ),
                ]
            ),
        ],
    ),
];
$overlays_data = [];

?>

    <section class="airport-map-wrapper">
        <div class="airport-map-container">
            <div class="airport-map-sidebar">
                <div>
                    <input type="text" class="airport-map-search" />
                </div>
                <div>
                    <?php recoursively_render_map_categories($category_data, $icon_data, '', 0, array('group_class_prefix' => $group_class_prefix)); ?>
                    <div class="airport-map-no-results hidden-no-results"><?php esc_html_e('No results found for: ') ?>“<span class="airport-map-search-term"></span>”</div>
                </div>
            </div>
            <div class="airport-map-main">
                <div class="airport-map-floors">
                    <?php foreach ($floors_data as $floor_idx => $floor) : ?>
                        <div class="airport-map-pannable">
                            <div class="airport-map-wrap">
                                <img src="<?php echo $floor['bg_path']; ?>" alt="" />
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
                
                                                <div class="airport-map-shape-wrap airport-map-shape-wrap-area" style="left: <?php echo $pos_x_percent; ?>%; top: <?php echo $pos_y_percent; ?>%; width: <?php echo $width_percent; ?>%; height: <?php echo $height_percent; ?>%;">
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
                                                <div class="airport-map-shape-wrap airport-map-shape-wrap-icon" style="left: <?php echo $pos_x_percent; ?>%; top: <?php echo $pos_y_percent; ?>%; width: <?php echo $width_percent; ?>%; height: <?php echo $height_percent; ?>%;">
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
                            </div>
                        </div>
                    <?php endforeach; ?>
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
        })
    </script>
    
<?php endif; ?>