<div class="my-flights-btn">
    <div class="my-flights-svg-wrap"><?php echo file_get_contents(get_template_directory() . '/assets/images/my-flight-cutout.svg'); ?></div>
    <div class="my-flights-btn-item">
        <strong>CZ 390</strong> to London Stansted Heathrow LSH <strong>13:50</strong> Gate <strong>1</strong> <strong>Landed</strong> <span class="my-flights-count">+4</span>
    </div>
</div>

<div class="my-flights-modal-wrapper custom-modal-wrapper">
    <div class="custom-modal-close-area"></div>
    <div class="custom-modal">
        <div class="my-flights-modal-header">
            <div class="my-flights-modal-header-left">
                <img class="my-flights-modal-header-icon" src="<?php echo get_template_directory_uri() . "/assets/images/fav-flights.svg" ?>" alt="Fav flights" />
                <div class="heading-third"><?php esc_html_e('Favourite flights', 'split-airport') ?></div>
            </div>
            <div class="custom-modal-close-btn-wrap">
                <button type="button" class="custom-modal-close-btn">
                    <?php echo file_get_contents(get_template_directory() . '/assets/images/x.svg'); ?>
                </button>
            </div>
        </div>

        <div>
            <!-- NOTE: placeholder loop -->
            <?php for ($i=0; $i < 5; $i++) : ?>
                <div class="my-flight-item">
                    <div class="my-flight-item-btn"><strong>CZ 390</strong> <span class="my-flight-item-destination">to Split</span> <strong>13:50</strong> Gate <strong>1</strong> <strong>Landed</strong></div>
                    <div class="my-flight-item-remove-btn"><?php echo file_get_contents(get_template_directory() . '/assets/images/x.svg'); ?></div>
                </div>
            <?php endfor; ?>
            
        </div>
    </div>
    
</div>