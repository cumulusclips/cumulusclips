
<p class="large"><?=Language::GetText('recent_posts_header')?></p>
<div class="block">

    <?php if ($logged_in && $logged_in == $member->user_id): ?>
        <form id="status-form">
            <p class="big"><?=Language::GetText('update_status')?></p>
            <textarea name="post" class="text"></textarea>
            <input type="hidden" name="submitted" value="TRUE" />
            <a class="button-small" href=""><?=Language::GetText('post_update_button')?></a>
        </form>
    <?php endif; ?>

    <div id="status-posts">
        <?php View::RepeatingBlock('post.tpl', $post_list); ?>
    </div>

</div>
