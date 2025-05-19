<?php extract($args); ?>
<?php $currentLanguage = apply_filters('wpml_current_language', null); ?>

<?php if (!empty($term)): ?>
    <p class="no-flight">
        <?php echo $currentLanguage === 'hr' 
            ? 'Nema letova za' 
            : 'No flights found for'; ?>
        <span class="search-data__term"><?php echo esc_html($term); ?></span>
    </p>
<?php else: ?>
    <p class="no-flight">
        <?php echo $currentLanguage === 'hr' 
            ? 'Nema letova' 
            : 'No flights'; ?>
    </p>
<?php endif; ?>
