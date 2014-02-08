<?php
$comment = new Comment($_id);
if ($comment->user_id == 0) {
    $name = $comment->name;
} else {
    $name = '<a href="' . $comment->website . '" title="' . $comment->name . '">' . $comment->name . '</a>';
}
?>

<div>
    <img width="60" height="60" alt="<?=$comment->name?>" src="<?=$comment->avatar_url?>" />
    <div>
        <p>
            <span><?=$name?> <?=Functions::DateFormat('m/d/Y',$comment->date_created)?></span>
            <span>
                <a href=""><?=Language::GetText('reply')?></a>
                <a class="flag" data-type="comment" data-id="<?=$comment->comment_id?>" href=""><?=Language::GetText('report_abuse')?></a>
            </span>
        </p>
        <p><?=nl2br($comment->comments)?></p>
    </div>
</div>