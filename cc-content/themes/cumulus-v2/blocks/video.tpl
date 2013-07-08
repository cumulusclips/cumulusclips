<?php $video = new Video($_id); ?>

<div class="video">
    <div>
        <a href="<?=$video->url?>/" title="<?=$video->title?>">
            <img width="165" height="92" src="<?=$config->thumb_url?>/<?=$video->filename?>.jpg" />
        </a>
        <span><?=$video->duration?></span>
    </div>
    <p><a href="<?=$video->url?>/" title="<?=$video->title?>"><?=$video->title?></a></p>
</div>