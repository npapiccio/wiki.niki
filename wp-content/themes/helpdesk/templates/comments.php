<?php
if (post_password_required()) {
  return;
}

$reply = false;
if(comments_open()) {
  $reply = true;
} 
?>

<?php if (have_comments()) : ?>
  <div id="comments" class="comments module">
    <h2><?php printf(__('Comments on %s', 'pressapps'), get_the_title()); ?></h2>

    <ol class="comment-list">
      <?php wp_list_comments(array('style' => 'ol', 'short_ping' => true, 'reply_text' => __('Reply', 'pressapps'), 'avatar_size' => 38)); ?>
    </ol>

    <?php if (get_comment_pages_count() > 1 && get_option('page_comments')) : ?>
      <nav>
        <ul class="pager">
          <?php if (get_previous_comments_link()) : ?>
            <li class="previous"><?php previous_comments_link(__('&larr; Older comments', 'pressapps')); ?></li>
          <?php endif; ?>
          <?php if (get_next_comments_link()) : ?>
            <li class="next"><?php next_comments_link(__('Newer comments &rarr;', 'pressapps')); ?></li>
          <?php endif; ?>
        </ul>
      </nav>
    <?php endif; ?>
  <?php endif; // have_comments() ?>

  <?php if (!comments_open() && get_comments_number() != '0' && post_type_supports(get_post_type(), 'comments')) : ?>
    <div class="alert alert-warning">
      <?php _e('Comments are closed.', 'pressapps'); ?>
    </div>
  </div>
<?php endif; ?>

<?php if($reply) : ?>
  <div id="respond" class="module">
    <h3><?php comment_form_title(__('Leave a Reply', 'pressapps'), __('Leave a Reply to %s', 'pressapps')); ?></h3>
    <p class="cancel-comment-reply"><?php cancel_comment_reply_link(); ?></p>
    <?php if (get_option('comment_registration') && !is_user_logged_in()) : ?>
      <p><?php printf(__('You must be <a href="%s">logged in</a> to post a comment.', 'pressapps'), wp_login_url(get_permalink())); ?></p>
    <?php else : ?>
      <form action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php" method="post" id="commentform">
        <?php if (is_user_logged_in()) : ?>
          <p>
            <?php printf(__('Logged in as <a href="%s/wp-admin/profile.php">%s</a>.', 'pressapps'), get_option('siteurl'), $user_identity); ?>
            <a href="<?php echo wp_logout_url(get_permalink()); ?>" title="<?php _e('Log out of this account', 'pressapps'); ?>"><?php _e('Log out &raquo;', 'pressapps'); ?></a>
          </p>
        <?php else : ?>
          <div class="form-group">
            <label for="author"><?php _e('Name', 'pressapps'); if ($req) _e(' (required)', 'pressapps'); ?></label>
            <input type="text" class="form-control" name="author" id="author" value="<?php echo esc_attr($comment_author); ?>" size="22" <?php if ($req) echo 'aria-required="true"'; ?>>
          </div>
          <div class="form-group">
            <label for="email"><?php _e('Email (will not be published)', 'pressapps'); if ($req) _e(' (required)', 'pressapps'); ?></label>
            <input type="email" class="form-control" name="email" id="email" value="<?php echo esc_attr($comment_author_email); ?>" size="22" <?php if ($req) echo 'aria-required="true"'; ?>>
          </div>
          <div class="form-group">
            <label for="url"><?php _e('Website', 'pressapps'); ?></label>
            <input type="url" class="form-control" name="url" id="url" value="<?php echo esc_attr($comment_author_url); ?>" size="22">
          </div>
        <?php endif; ?>
        <div class="form-group">
          <label for="comment"><?php _e('Comment', 'pressapps'); ?></label>
          <textarea name="comment" id="comment" class="form-control" rows="5" aria-required="true"></textarea>
        </div>
        <p><input name="submit" class="btn btn-primary" type="submit" id="submit" value="<?php _e('Submit Comment', 'pressapps'); ?>"></p>
        <?php comment_id_fields(); ?>
        <?php do_action('comment_form', $post->ID); ?>
      </form>
    <?php endif; ?>
  </div><!-- /#respond -->
<?php endif; ?>
