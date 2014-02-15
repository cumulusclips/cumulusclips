<?php
$comment = $model;
if ($comment->userId == 0) {
    $name = $comment->name;
} else {
    $name = '<a href="' . $comment->website . '" title="' . $comment->name . '">' . $comment->name . '</a>';
}
?>

<div>
    <img width="60" height="60" alt="<?=$comment->name?>" src="<?=$comment->avatar_url?>" />
    <div>
        <p>
            <span><?=$name?> <?=Functions::DateFormat('m/d/Y',$comment->dateCreated)?></span>
            <span>
                <a href=""><?=Language::GetText('reply')?></a>
                <a class="flag" data-type="comment" data-id="<?=$comment->commentId?>" href=""><?=Language::GetText('report_abuse')?></a>
            </span>
        </p>
        <p><?=nl2br($comment->comments)?></p>
    </div>
</div>