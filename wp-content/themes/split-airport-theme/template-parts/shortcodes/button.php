<?php extract($args);  
?>

<div class="content-button">
    <a <?php if ($newTab === 'yes') echo 'target="_blank"'; ?> href="<?php echo $url; ?>"><?php echo $title; ?></a>
</div>