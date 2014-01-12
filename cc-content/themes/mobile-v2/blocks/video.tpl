<?php $video = $model; ?>

<div onclick="window.location = '<?=MOBILE_HOST?>/v/<?=$video->videoId?>/';" class="video">
    <img class="thumb" src="<?=$config->thumb_url?>/<?=$video->filename?>.jpg" alt="<?=$video->title?>" />
    <p class="title"><?=$video->title?></p>
    <strong><?=Language::GetText('duration')?>: </strong><?=$video->duration?>
    <a href="<?=MOBILE_HOST?>/v/<?=$video->videoId?>/"></a>
    <div class="clear"></div>
</div>
