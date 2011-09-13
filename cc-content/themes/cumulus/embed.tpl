<!DOCTYPE html>
<html>
<head>
<title><?=$video->title?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="<?=THEME?>/css/reset.css" />
<link rel="stylesheet" href="<?=THEME?>/css/video-js.css" />
<style type="text/css">
html, body, .video-unavailable, .video-js-box, video, object, img {
    width:100% !important;
    height:100% !important;
    overflow:hidden;
}
.video-unavailable {
    background-color:#000;
    text-align:center;
    color:#FFF;
    font-size:16px;
    font-family:arial,helvetica,sans-serif;
    padding-top:50px;
}
</style>
<body>

<?php if ($video): ?>

<!-- BEGIN VIDEO -->
<div class="video-js-box">
    <video class="video-js" controls preload poster="<?=$config->thumb_url?>/<?=$video->filename?>.jpg">

        <source src="<?=$config->h264_url?>/<?=$video->filename?>.mp4" type='video/mp4; codecs="avc1.42E01E, mp4a.40.2"' />
        <source src="<?=$config->theora_url?>/<?=$video->filename?>.ogg" type='video/ogg; codecs="theora, vorbis"' />

        <!-- BEGIN FLASH FALLBACK -->
        <object id="flash_fallback_1" class="vjs-flash-fallback" type="application/x-shockwave-flash" data="<?=THEME?>/flash/flowplayer-3.2.7.swf">
            <param name="movie" value="<?=THEME?>/flash/flowplayer-3.2.7.swf" />
            <param name="allowfullscreen" value="true" />
            <param name="flashvars" value='config={"playlist":["<?=$config->thumb_url?>/<?=$video->filename?>.jpg", {"url": "<?=$config->h264_url?>/<?=$video->filename?>.mp4","autoPlay":false,"autoBuffering":true}]}' />
            <img src="<?=$config->thumb_url?>/<?=$video->filename?>.jpg" alt="<?=$video->title?>" title="<?=Language::GetText('no_playback')?>" />
        </object>
        <!-- END FLASH FALLBACK -->

    </video>
</div>
<!-- END VIDEO -->

<?php else: ?>

<div class="video-unavailable"><?=Language::GetText('video_unavailable')?></div>

<?php endif; ?>

<script type="text/javascript" src="<?=THEME?>/js/video.js"></script>
<script type="text/javascript">VideoJS.setupAllWhenReady();</script>
</body>
</html>