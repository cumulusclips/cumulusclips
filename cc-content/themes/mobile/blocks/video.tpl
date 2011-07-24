<?php $video = new Video ($_id); ?>


<div onclick="window.location = '<?=MOBILE_HOST?>/v/<?=$video->video_id?>/';" class="video">
    <img class="thumb" src="<?=$config->thumb_bucket_url?>/<?=$video->filename?>.jpg" alt="<?=$video->title?>" />
    <p class="title"><?=$video->title?></p>
    <strong><?=Language::GetText('duration')?>: </strong><?=$video->duration?>
    <a href="<?=MOBILE_HOST?>/v/<?=$video->video_id?>/"></a>
    <div class="clear"></div>
</div>
