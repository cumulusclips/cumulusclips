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
        <div id="update-status">
            <p class="big">Update your Status</p>
            <textarea name="update" cols="30" rows="1" class="text"></textarea>
            <a class="button-small" href=""><span>Post Update</span></a>
        </div>
    <?php //endif; ?>

    
    <?php while ($row = $db->FetchObj ($result_posts)): ?>

        <?php $post = new Post ($row->post_id); ?>
        <p class="post">
            <?=$post->post?><br />
            <strong><?=Functions::TimeSince (strtotime ($post->date_created))?></strong>
        </p>
    
    <?php endwhile; ?>

</div>
