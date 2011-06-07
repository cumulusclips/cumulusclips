
<?php $comment = new Comment ($_id); ?>

<div class="block comment">
    <div class="video-comment">
        <p class="thumb">
            <img width="50" height="50" src="<?=$comment->avatar?>" />
            <?php if (!empty ($comment->website)): ?>
                <a href="<?=$comment->website?>/" title="<?=$comment->name?>"><?=$comment->name?></a>
            <?php else: ?>
                <?=$comment->name?>
            <?php endif; ?>
        </p>
        <p><?=Language::GetText('date_posted')?>: <?=$comment->date_created?></p>
        <p><a href="" class="flag" data-type="comment" data-id="<?=$comment->comment_id?>" title="<?=Language::GetText('report_abuse')?>"><?=Language::GetText('report_abuse')?></a></p>
        <br clear="all" />
    </div>
    <p><?=$comment->comments?></p>
</div>
