<?php View::Header(); ?>

<h1><?=Language::GetText('comments_header')?></h1>

<div id="message"></div>

<p class="big">
    <?=Language::GetText('comments_for')?>:
    <a href="<?=$video_url?>/" title="<?=$video->title?>"><?=$video->title?></a>
</p>


<?php if ($total_comments > 0): ?>

    <!-- BEGIN Video comments loop -->
    <?php View::RepeatingBlock('comment.tpl', $comment_list); ?>
    <!-- END videos comments loop -->
    
    <?=$pagination->Paginate()?>

<?php else: ?>
    <div class="block"><strong><?=Language::GetText('no_comments')?></strong></div>
<?php endif; ?>

<?php View::Footer(); ?>