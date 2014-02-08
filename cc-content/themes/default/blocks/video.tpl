<?php $video = $model ?>

<div class="video">
    <div>
        <a href="<?=$this->getService('Video')->getUrl($video)?>/" title="<?=$video->title?>">
            <img width="165" height="92" src="<?=$config->thumb_url?>/<?=$video->filename?>.jpg" />
        </a>
        <span><?=$video->duration?></span>
    </div>
    <p><a href="<?=VideoService::getUrl($video)?>/" title="<?=$video->title?>"><?=$video->title?></a></p>
</div>