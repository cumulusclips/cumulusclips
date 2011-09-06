<?php
$video = new Video ($_id);
$rating = Rating::GetRating ($video->video_id);
?>

<div class="block video">

    <a class="thumb" href="<?=$video->url?>/" title="<?=$video->title?>">
        <span class="duration"><?=$video->duration?></span>
        <span class="play-icon"></span>
        <img src="<?=$config->thumb_url?>/<?=$video->filename?>.jpg" alt="<?=$video->title?>" />
    </a>

    <a class="large" href="<?=$video->url?>/" title="<?=$video->title?>"><?=$video->title?></a>
    <p><?=Functions::CutOff ($video->description, 190)?></p>
    <span class="like">+<?=$rating->likes?></span>
    <span class="dislike">-<?=$rating->dislikes?></span>
    <div class="clear"></div>

</div>
