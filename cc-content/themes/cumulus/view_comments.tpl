
<h1>View All Comments</h1>

<div id="message"></div>

<p class="big">
    Viewing Comments For:
    <a href="<?=HOST?>/videos/<?=$video->video_id?>/<?=$video->slug?>/" title="<?=$video->title?>"><?=$video->title?></a>
</p>


<?php if ($total_comments > 0): ?>

    <!-- BEGIN Video comments loop -->
    <?php View::RepeatingBlock('comment.tpl', $comment_list); ?>
    <!-- END videos comments loop -->
    
<?php else: ?>
    <div class="block"><strong>No comments have been posted for this video yet!</strong></div>
<?php endif; ?>

<?=$pagination->Paginate()?>