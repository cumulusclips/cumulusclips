
<?php $comment = new Comment ($_id); ?>

<div class="block comment">
    <p class="avatar-small"><img alt="<?=$comment->name?>" src="<?=$comment->avatar_url?>" /></p>
    <p><strong><?=Language::GetText('posted')?>:</strong>
        <?php if ($comment->user_id == 0): ?>

            <?php if (!empty ($comment->website)): ?>
                <a rel="nofollow" target="_blank" href="<?=$comment->website?>" title="<?=$comment->name?>"><?=$comment->name?></a>
            <?php else: ?>
                <?=$comment->name?>
            <?php endif; ?>

        <?php else: ?>
            <a href="<?=$comment->website?>" title="<?=$comment->name?>"><?=$comment->name?></a>
        <?php endif; ?>
    </p>
    <p><strong><?=Language::GetText('date_posted')?>:</strong> <?=Functions::DateFormat('m/d/Y',$comment->date_created)?></p>
    <p><a href="" class="flag" data-type="comment" data-id="<?=$comment->comment_id?>" title="<?=Language::GetText('report_abuse')?>"><?=Language::GetText('report_abuse')?></a></p>
    <p class="clear comment-text"><?=nl2br($comment->comments)?></p>
</div>
