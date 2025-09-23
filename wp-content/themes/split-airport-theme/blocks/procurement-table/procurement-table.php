<?php 
/*
* Block Name: Procurement Table
* Post Type: page 
*/

if (isset($block['data']['preview_image_help'])) :
    echo '<img src="' . esc_url($block['data']['preview_image_help']) . '" style="width:100%; height:auto;">';
else: ?>
<section class="procurement-table-wrapper">
    <div class="container">
        <?php
        $has_items = false;
        $all_posts = [];

        $args = [
            'post_type'      => 'public-procurement',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ];

        $query = new WP_Query($args);

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id       = get_the_ID();
                $subject_title = get_field('subject_title', $post_id);
                $items         = get_field('procurement_custom_table', $post_id);

                if ($items && is_array($items)) {
                    $has_items = true;
                    $all_posts[] = [
                        'id'            => $post_id,
                        'subject_title' => $subject_title,
                        'items'         => $items,
                        'documents'     => get_field('documents', $post_id),
                    ];
                }
            }
            wp_reset_postdata();
        }

        if ($has_items): ?>
            <h3 class="heading-third procurement-table-subtitle">Postupci jednostavne nabave</h3>

            <?php foreach ($all_posts as $ap): 
                $modal_id = 'modal-' . $ap['id']; ?>
                <div class="procurement-table-inner">
                    <div class="procurement-table">
                        <div class="procurement-table-header">
                            <?php if ($ap['subject_title']): ?>
                                <h3 class="heading-third">Predmet</h3>
                                <button type="button" class="request-doc-modal-btn" data-modal-id="<?php echo esc_attr($modal_id); ?>">
                                    <span><?php echo esc_html($ap['subject_title']); ?></span>
                                    <?php echo file_get_contents(get_template_directory() . '/assets/images/link-arrow.svg'); ?>
                                </button>
                            <?php endif; ?>
                        </div>

                        <div class="procurement-table-rows">
                            <?php foreach ($ap['items'] as $active): ?>
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
                            <h2 class="request-doc-title"><?php echo esc_html($ap['subject_title']); ?></h2>

                            <div class="request-doc-items">
                                <?php foreach ($ap['items'] as $active): ?>
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

                            <?php if (!empty($ap['documents'])) : ?>
                                <form method="post" enctype="multipart/form-data">
                                    <input type="hidden" name="modal_post_id" value="<?php echo esc_attr($ap['id']); ?>">
                                    <div class="frm_form_fields">
                                        <fieldset>
                                            <legend class="frm_screen_reader">Documents Request</legend>
                                            <div class="frm_fields_container">
                                                <h4>Popis dokumenata koji ćete dobiti</h4>
                                                <ul>
                                                    <?php foreach ($ap['documents'] as $item) :
                                                        $file_id = $item['document_file']['ID'] ?? null;
                                                        $title   = get_the_title($file_id); ?>
                                                        <li><?php echo esc_html($title); ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>

                                            <div class="frm_form_field form-field frm_required_field frm_top_container">
                                                <label for="email" class="frm_primary_label">
                                                    Email <span class="frm_required" aria-hidden="true">*</span>
                                                </label>
                                                <input type="email" id="email" name="email" required>
                                            </div>

                                            <div class="frm_form_field form-field frm_required_field frm_top_container">
                                                <div class="frm_checkbox" role="group">
                                                    <label for="consent">
                                                        <input type="checkbox" name="consent" id="consent" value="1" required>
                                                        Slažem se da se moji podaci koriste za ovu svrhu.
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="frm_form_field form-field">
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
            <?php endforeach; ?>

            <?php if ($link = get_field('link')) : ?>
                <div style="max-width: 490px !important;">
                    <?php
                    get_template_part('template-parts/shortcodes/button', null, [
                        'title'  => $link['title'] ?? "",
                        'url'    => $link['url'] ?? "",
                        'newTab' => $link['newtab'] ?? "no"
                    ]);
                    ?>
                </div>
            <?php endif; ?>

        <?php else:
            $currentLanguage = apply_filters('wpml_current_language', null);
            echo $currentLanguage === 'hr'
                ? '<p>Trenutno nema postupaka jednostavne nabave.</p>'
                : '<p>There are currently no simplified procurement procedures.</p>';
        endif; ?>
    </div>
</section>
<?php endif; ?>
