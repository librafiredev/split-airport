<?php 
/*
* Block Name: Procurement Table
* Post Type: page 
*/

if( isset( $block['data']['preview_image_help'] ) ) :
    echo '<img src="'. esc_url($block['data']['preview_image_help']) .'" style="width:100%; height:auto;">';
else: ?>
<section class="procurement-table-wrapper">
    <div class="container">
        <?php
        $today = date('Y-m-d');
        $has_active = false;

        $args = [
            'post_type' => 'public-procurement',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ];

        $query = new WP_Query($args);
        $posts = [];

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $posts[] = $query->post;
            }
            wp_reset_postdata();
        }

        foreach ($posts as $post) {
            setup_postdata($post);

            $post_id = $post->ID;
            $modal_id = 'modal-' . $post_id;

            $subject_title = get_field('subject_title', $post_id);
            $items = get_field('procurement_custom_table', $post_id);

            $active_items = [];

            if ($items && is_array($items)) {
                foreach ($items as $item) {
                    $deadline_raw = trim($item['deadline']);
                    $deadline_raw = rtrim($deadline_raw, '.');
                    $deadline_date = DateTime::createFromFormat('d.m.Y', $deadline_raw);

                    if ($deadline_date && $deadline_date->format('Y-m-d') >= $today) {
                        $active_items[] = $item;
                    }
                }
            }

            if (!empty($active_items)) {
                $has_active = true;
                ?>
                <div class="procurement-table-inner">
                    <h3 class="heading-third procurement-table-subtitle">Postupci jednostavne nabave</h3>
                    <div class="procurement-table">
                        <div class="procurement-table-header">
                            <?php if ($subject_title): ?>
                                <h3 class="heading-third">Predmet</h3>
                                <button type="button" class="request-doc-modal-btn" data-modal-id="<?php echo esc_attr($modal_id); ?>">
                                    <span><?php echo esc_html($subject_title); ?></span>
                                    <?php echo file_get_contents(get_template_directory() . '/assets/images/link-arrow.svg'); ?>
                                </button>
                            <?php endif; ?>
                        </div>

                        <div class="procurement-table-rows">
                            <?php foreach ($active_items as $active): ?>
                                <?php if (!empty($active['procurement_number'])): ?>
                                    <div class="procurement-table-row">
                                        <div class="procurement-table-row-title heading-third">Broj nabave</div>
                                        <div class="procurement-table-row-value"><?php echo esc_html($active['procurement_number']); ?></div>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($active['procurement_date'])): ?>
                                    <div class="procurement-table-row">
                                        <div class="procurement-table-row-title heading-third">Datum</div>
                                        <div class="procurement-table-row-value"><?php echo esc_html($active['procurement_date']); ?></div>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($active['deadline'])): ?>
                                    <div class="procurement-table-row">
                                        <div class="procurement-table-row-title heading-third">Rok</div>
                                        <div class="procurement-table-row-value"><?php echo esc_html($active['deadline']); ?></div>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($active['status'])): ?>
                                    <div class="procurement-table-row">
                                        <div class="procurement-table-row-title heading-third">Status</div>
                                        <div class="procurement-table-row-value"><?php echo esc_html($active['status']); ?></div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- MODAL -->
                <?php 
                    $modal_post_id = intval(preg_replace('/\D/', '', $modal_id)); 
                    $subject_title = get_field('subject_title', $modal_post_id);
                    $items = get_field('procurement_custom_table', $post_id);
                    $document_list = get_field('documents', $post_id);

                ?>
                <div id="<?php echo esc_attr($modal_id); ?>" class="request-doc-modal-wrapper custom-modal-wrapper">
                    <div class="custom-modal-close-area"></div>
                    <div class="request-doc-modal custom-modal">
                        <div class="custom-modal-close-btn-wrap">
                            <button type="button" class="custom-modal-close-btn">
                                <?php echo file_get_contents(get_template_directory() . '/assets/images/x.svg'); ?>
                            </button>
                        </div>
                        <div>
                            <h3 class="request-doc-header heading-third">Predmet</h3>
                            <h2 class="request-doc-title"><?php echo esc_html($subject_title); ?></h2>

                            <div class="request-doc-items">
                                <?php foreach ($active_items as $active): ?>
                                    <?php if (!empty($active['procurement_number'])): ?>
                                        <div class="procurement-table-row">
                                            <div class="procurement-table-row-title heading-third">Broj nabave</div>
                                            <div class="procurement-table-row-value"><?php echo esc_html($active['procurement_number']); ?></div>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($active['procurement_date'])): ?>
                                        <div class="procurement-table-row">
                                            <div class="procurement-table-row-title heading-third">Datum</div>
                                            <div class="procurement-table-row-value"><?php echo esc_html($active['procurement_date']); ?></div>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($active['deadline'])): ?>
                                        <div class="procurement-table-row">
                                            <div class="procurement-table-row-title heading-third">Rok</div>
                                            <div class="procurement-table-row-value"><?php echo esc_html($active['deadline']); ?></div>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($active['status'])): ?>
                                        <div class="procurement-table-row">
                                            <div class="procurement-table-row-title heading-third">Status</div>
                                            <div class="procurement-table-row-value"><?php echo esc_html($active['status']); ?></div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>

                            

                            <?php if (!empty($document_list)) : ?>
                                <form method="post" enctype="multipart/form-data">
                                    <input type="hidden" name="modal_post_id" value="<?php echo esc_attr($modal_post_id); ?>">
                                    <div class="frm_form_fields">
                                        <fieldset>
                                            <legend class="frm_screen_reader">Documents Request</legend>

                                            <div class="frm_fields_container">
                                                <h4>Popis dokumenata koji ćete dobiti</h4>
                                                <ul>
                                                    <?php foreach ($document_list as $item) : 
                                                        $file_id = $item['document_file']['ID'] ?? null;
                                                        $title = get_the_title($file_id) ?>
                                                        <li><?php echo esc_html($title); ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>

                                            <div id="frm_field_8_container" class="frm_form_field form-field frm_required_field frm_top_container">
                                                <label for="email" class="frm_primary_label">
                                                    Email <span class="frm_required" aria-hidden="true">*</span>
                                                </label>
                                                <input type="email" id="email" name="email" value="" autocomplete="email" required>
                                            </div>

                                            <div id="frm_field_13_container" class="frm_form_field form-field frm_required_field frm_top_container">
                                                <div class="frm_checkbox" role="group">
                                                    <label for="consent">
                                                        <input type="checkbox" name="consent" id="consent" value="1" required>
                                                        Slažem se da se moji podaci koriste za ovu svrhu.
                                                    </label>
                                                </div>
                                            </div>

                                            <div id="frm_field_7_container" class="frm_form_field form-field">
                                                <div class="frm_submit frm_flex">
                                                    <button class="frm_button_submit frm_final_submit" type="submit" name="send_documents">
                                                        Pošalji
                                                    </button>
                                                </div>
                                            </div>
                                        </fieldset>
                                    </div>
                                </form>
                            <?php else : ?>
                                <p>Nema dokumenata.</p>
                            <?php endif; ?>



                        </div>
                    </div>
                </div>

                <?php
            }

            wp_reset_postdata();
        }

        $currentLanguage = apply_filters('wpml_current_language', null);


        if (!$has_active) {
            if ($currentLanguage === 'hr') {
                echo '<p>Trenutno nema postupaka jednostavne nabave u tijeku.</p>';
            } else {
                echo '<p>There are currently no ongoing simplified procurement procedures.</p>';
            }
        }

        if ($has_active) {
            if ($link = get_field('link')) :
                ?>
                <div style="max-width: 490px !important;">
                    <?php
                    get_template_part('template-parts/shortcodes/button', null, [
                        'title' => $link['title'] ?? "",
                        'url' => $link['url'] ?? "",
                        'newTab' => $link['newtab'] ?? "no"
                    ]);
                    ?>
                </div>
                <?php
            endif;
        }
        ?>
    </div>
</section>
<?php endif; ?>
