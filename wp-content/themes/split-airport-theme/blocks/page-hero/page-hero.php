<?php
/*
* Block Name: Page Hero
* Post Type: page 
*/

if (isset($block['data']['preview_image_help'])) :
    echo '<img src="' . $block['data']['preview_image_help'] . '" style="width:100%; height:auto;">';
else:
    $working_hours = get_field('working_hours');
?>

    <section class="page-hero-wrapper">

        <div class="page-hero-wrapper-inner">
            <div class="page-hero-img-wrap"><?php echo wp_get_attachment_image(get_field('background'), 'full',); ?></div>
            <div class="page-hero-cutout-wrap">
                <svg version="1.2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1439 71" width="1439" height="71" class="page-hero-cutout">
                    <path d="m682.7 0h-682.7v716h1445v-645h-611.4c-18.5 0-36.5-6.1-51.3-17.3l-48.3-36.5c-14.8-11.2-32.8-17.2-51.3-17.2z" />
                </svg>
            </div>
        </div>


        <div class="container">

            <?php if (get_field('title')): ?>
                <button type="button" onclick="history.go(-1)" class="page-hero-back-btn">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M11.765 23.6263L0.8 13.7998C0.318633 13.3249 0.0326416 12.6815 0 12C0.0330377 11.3186 0.318966 10.6753 0.8 10.2002L11.765 0.373699C12.1426 0.0245768 12.6757 -0.0902977 13.1598 0.0731377C13.644 0.236573 14.004 0.652946 14.1018 1.16255C14.1997 1.67215 14.0201 2.19587 13.632 2.53283L5.166 10.1167C5.08832 10.1869 5.06134 10.2987 5.09822 10.3975C5.1351 10.4963 5.22815 10.5616 5.332 10.5616L22.587 10.5616C23.3674 10.5616 24 11.2056 24 12C24 12.7944 23.3674 13.4384 22.587 13.4384L5.332 13.4384C5.22785 13.4383 5.13456 13.504 5.09784 13.6032C5.06112 13.7024 5.08868 13.8144 5.167 13.8843L13.632 21.4672C14.0201 21.8041 14.1997 22.3278 14.1018 22.8375C14.004 23.3471 13.644 23.7634 13.1598 23.9269C12.6757 24.0903 12.1426 23.9754 11.765 23.6263Z" fill="#2D2D2D" />
                    </svg>
                </button>
                <h1 class="page-hero-title"><?php echo the_field('title'); ?></h1>

            <?php endif; ?>

            <?php if ($working_hours): ?>

                <p class="page-hero-working-hours"><?php echo $working_hours; ?></p>

            <?php endif; ?>
        </div>
    </section><!-- .page-hero-wrapper-->

<?php endif; ?>