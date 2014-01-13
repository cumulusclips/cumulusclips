<?php

$view->AddCss ('video-js.css');
$view->AddJs ('video.js');
$view->AddJs ('play.js');

?>

<div id="play">
    <div class="block">
        
        <h1><?=$video->title?></h1>

        <div class="video-js-box">
            <video class="video-js" width="100%" height="264" controls preload poster="<?=$config->thumb_url?>/<?=$video->filename?>.jpg">

                <source src="<?=$config->mobile_url?>/<?=$video->filename?>.mp4" type='video/mp4; codecs="avc1.42E01E, mp4a.40.2"' />
                <object id="flash_fallback_1" class="vjs-flash-fallback" width="100%" height="264" type="application/x-shockwave-flash" data="http://releases.flowplayer.org/swf/flowplayer-3.2.1.swf">
                    <param name="movie" value="http://releases.flowplayer.org/swf/flowplayer-3.2.1.swf" />
                    <param name="allowfullscreen" value="true" />
                    <param name="flashvars" value='config={"playlist":["<?=$config->thumb_url?>/<?=$video->filename?>.jpg", {"url": "<?=$config->mobile_url?>/<?=$video->filename?>.mp4","autoPlay":false,"autoBuffering":true}]}' />
                    <img src="<?=$config->mobile_url?>/<?=$video->filename?>.jpg" width="100%" height="264" alt="Poster Image" title="No video playback capabilities." />
                </object>

            </video>
        </div>

        <p><strong><?=Language::GetText('duration')?>: </strong><?=$video->duration?></p>
        <p><strong><?=Language::GetText('by')?>: </strong><?=$video->username?></p>
        <p><strong><?=Language::GetText('description')?>: </strong><?=$video->description?></p>
                
    </div>
</div>