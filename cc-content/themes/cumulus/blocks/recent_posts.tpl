
<p class="large space"><?=Language::GetText('recent_posts_header')?></p>
<div class="block">

    <?php if ($logged_in && $logged_in == $member->user_id): ?>
        <form id="status-form">
            <p class="big"><?=Language::GetText('update_status')?></p>
            <textarea name="post" class="text"></textarea>
            <input type="hidden" name="submitted" value="TRUE" />
            <input class="button-small" type="submit" name="button" value="<?=Language::GetText('post_update_button')?>" />
        </form>
    <?php endif; ?>

    <div id="status-posts">
        <?php if (!empty ($post_list)): ?>
            <?php View::RepeatingBlock('post.tpl', $post_list); ?>
        <?php else: ?>
            <span id="no-updates"><strong><?=Language::GetText('no_updates')?></strong></span>
        <?php endif; ?>
    </div>

</div>
