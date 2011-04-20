
<?php
$comment = new Comment ($row->comment_id);
$comment_user = new User ($comment->user_id);
?>

<div class="block comment">
    <div class="video-comment">
        <p class="thumb">
            <a href="<?=HOST?>'/members/<?=$comment_user->username?>/" title="<?=$comment_user->username?>">
                <img src="<?=$comment_user->avatar?>" width="55" height="55" alt="<?=$comment_user->username?>" />
            </a>
        </p>
        <p><?=Language::GetText('posted_by')?>: <a href="<?=HOST?>/members/<?=$comment_user->username?>/" title="<?=$comment_user->username?>"><?=$comment_user->username?></a></p>
        <p><?=Language::GetText('date_posted')?>: <?=$comment->date_created?></p>
        <p><a href="" id="comment-<?=$comment->comment_id?>" class="flag-comment" title="<?=Language::GetText('report_abuse')?>"><?=Language::GetText('report_abuse')?></a></p>
        <br clear="all" />
    </div>
    <p><?=$comment->comments?></p>
</div>
