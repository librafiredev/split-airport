<?php 
/*
* Block Name: Airport Map
* Post Type: page 
*/

if( isset( $block['data']['preview_image_help'] )  ) :
    echo '<img src="'. $block['data']['preview_image_help'] .'" style="width:100%; height:auto;">';
else:

$icon_size = 62;

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
            'label' => __('Lost & Found'),
            'id' => 1,
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
                    'type' => 'area',
                )
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
$category_data = [];
$overlays_data = [];

?>

    <section class="airport-map-wrapper">
        <div class="airport-map-container">
            <div class="airport-map-floors">
                <?php foreach ($floors_data as $key => $floor) : ?>
                    <div class="airport-map-pannable">
                        <div class="airport-map-wrap">
                            <img src="<?php echo $floor['bg_path']; ?>" alt="" />
                            <?php $groups = $icon_data[$key]; ?>
                            <?php foreach ($groups as $group) : ?>
                                <div class="airport-map-group">
                                    <?php $items = $group['items']; ?>
                                    <?php foreach ($items as $i_key => $item) : ?>
                                        <?php
                                        $shape_type = $item['type'];
                                        $pos_x = $item['x'];
                                        $pos_y = $item['y'];
                                        if ($shape_type != 'area') {
                                            $pos_x += $icon_size * .5;
                                            $pos_y += $icon_size * .5;
                                        }
                                    
                                        $pos_x_percent = 100 * $pos_x / $floor['width'];
                                        $pos_y_percent = 100 * $pos_y / $floor['height'];
                                        $icon_width_percent = 100 * $icon_size / $floor['width'];
                                        $icon_height_percent = 100 * $icon_size / $floor['height'];
                                    
                                        ?>
                                        <div class="airport-map-shape-wrap" style="left: <?php echo $pos_x_percent; ?>%; top: <?php echo $pos_y_percent; ?>%; width: <?php echo $icon_width_percent; ?>%; height: <?php echo $icon_height_percent; ?>%;">
                                            <div
                                                class="airport-map-shape airport-map-<?php echo $shape_type ?: 'icon'; ?>"
                                            >
                                                <img class="airport-map-icon-img" src="<?php echo $item['shape_path']; ?>" /> 
                                            </div>
                                            <div class="airport-map-tooltip <?php echo $item['tooltip_side'] ?: 'right'; ?>-tooltip"><?php echo $group['label']; ?></div>
                                        </div>
                                    <?php endforeach ?>
                                </div>
                            <?php endforeach ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section><!-- .airport-map-wrapper-->
    
<?php endif; ?>