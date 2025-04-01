<?php extract($args);  
?>

<div class="content-button">
    <a <?php if ($newTab === 'yes') echo 'target="_blank"'; ?> href="<?php echo $url; ?>">
        <?php echo file_get_contents(get_template_directory() . '/assets/images/link-arrow.svg'); ?>
        <?php echo $title; ?>
    </a>
</div>