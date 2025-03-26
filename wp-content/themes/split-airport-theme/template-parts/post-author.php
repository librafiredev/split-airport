<?php if ( get_the_author_meta('description') != '' ) : ?>
    <div class="post-author-wrapper full-width ">
        <?php if( function_exists('get_field') ) : ?>
            <div class="post-author-avatar pull-left">
                <?php $user = get_field('user_avatar', 'user_' . $post->post_author); ?>
                <img src="<?php echo  $user['url']; ?>" alt="Image of: <?php echo $user['title']; ?>">
            </div>
        <?php endif; ?>
        <div class="post-author-description-name-wrapper pull-left">
            <h3 class="title-author"><?php _e('Auteur', 'split'); ?>: <span class="dark-title"><?php echo  get_the_author(); ?></span></h3>
            <p><?php echo nl2br(get_the_author_meta('description')); ?></p>
        </div>
    </div>
<?php endif; // no description, no author's meta ?>