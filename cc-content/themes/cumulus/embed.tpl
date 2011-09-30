<!DOCTYPE html>
<html>
<head>
<title><?=$video->title?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="<?=THEME?>/css/reset.css" />
<style type="text/css">
html, body, .video-unavailable, object, img {
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
    <div id="player"><?=$video->title?> - <?=Language::GetText('loading')?>...</div>
    <script type="text/javascript" src="<?=THEME?>/js/jwplayer.js"></script>
    <script type="text/javascript">
    jwplayer("player").setup({
        flashplayer : '<?=THEME?>/flash/player.swf',
        file        : '<?=$config->flv_url?>/<?=$video->filename?>.flv',
        image       : '<?=$config->thumb_url?>/<?=$video->filename?>.jpg',
        controlbar  : 'bottom',
        width       : '100%',
        height      : '100%'
    });
    </script>
    <!-- END VIDEO -->

<?php else: ?>

<div class="video-unavailable"><?=Language::GetText('video_unavailable')?></div>

<?php endif; ?>

</body>
</html>