
<?php $comment = new Comment ($_id); ?>

<div class="block comment">
    <div class="video-comment">
        <p class="thumb">
            <?php if (!empty ($comment->website)): ?>
                <a href="<?=$comment->website?>/" title="<?=$comment->name?>"><?=$comment->name?></a>
            <?php else: ?>
                <?=$comment->name?>
            <?php endif; ?>
        </p>
        <p><?=Language::GetText('date_posted')?>: <?=$comment->date_created?></p>
        <p><a href="" id="comment-<?=$comment->comment_id?>" class="flag-comment" title="<?=Language::GetText('report_abuse')?>"><?=Language::GetText('report_abuse')?></a></p>
        <br clear="all" />
    </div>
    <p><?=$comment->comments?></p>
</div>
