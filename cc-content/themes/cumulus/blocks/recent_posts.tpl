<script type="text/javascript" src="/cc-content/themes/cumulus/js/jquery.min.js"></script>
<script type="text/javascript">
$('document').ready(function(){
    $('#update-status .text').focus(function(){
        $(this).css('height', '80');
        $('#update-status .button-small').css('display', 'inline-block');
    });
});
</script>

<p class="large"><?=Language::GetText('recent_posts_header')?></p>
<div class="block">

    <?php //if ($logged_in): ?>
        <form id="status-form">
            <p class="big"><?=Language::GetText('update_status')?></p>
            <textarea name="post" cols="30" rows="1" class="text"></textarea>
            <input type="hidden" name="submitted" value="TRUE" />
            <a class="button-small" href=""><span><?=Language::GetText('post_update_button')?></span></a>
        </form>
    <?php //endif; ?>

    <div id="status-posts">
        <?php View::RepeatingBlock('post.tpl', $post_list); ?>
    </div>

</div>
