<?php
add_shortcode('phone', 'phone_number_callback');

function phone_number_callback($atts)
{
    ob_start();

    get_template_part('template-parts/shortcodes/phone', null, ['number' => isset($atts['number']) ? $atts['number'] : ""]);

    return ob_get_clean();
}

add_shortcode('button', 'button_callback');

function button_callback($atts)
{
    ob_start();

    get_template_part('template-parts/shortcodes/button', null, ['title' => isset($atts['title']) ? $atts['title'] : "", 'url' => isset($atts['url']) ? $atts['url'] : "", 'newTab' => isset($atts['newtab']) ? $atts['newtab'] : "no"]);

    return ob_get_clean();
}
