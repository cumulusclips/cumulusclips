<!DOCTYPE html>
<html>
<head>
<title><?=$video->title?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="<?=$config->theme_url?>/css/reset.css" />
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
}
.video-unavailable p {
    padding:50px;
    line-height:1.5em;
}
</style>
<body>

<?php if ($video && $video->disable_embed == '0'): ?>

    <!-- BEGIN VIDEO -->
    <div id="player"><?=$video->title?> - <?=Language::GetText('loading')?>...</div>
    <script type="text/javascript" src="<?=$config->theme_url?>/js/jwplayer.js"></script>
    <script type="text/javascript">
    jwplayer("player").setup({
        flashplayer : '<?=$config->theme_url?>/flash/player.swf',
        file        : '<?=$config->flv_url?>/<?=$video->filename?>.flv',
        image       : '<?=$config->thumb_url?>/<?=$video->filename?>.jpg',
        controlbar  : 'bottom',
        width       : '100%',
        height      : '100%'
    });
    </script>
    <!-- END VIDEO -->

<?php elseif ($video && $video->disable_embed == '1'): ?>
    <?php $link = HOST . "/videos/$video->video_id/$video->slug/"; ?>
    <div class="video-unavailable"><p><?=Language::GetText('embed_disabled', array ('link' => $link, 'sitename' => $config->sitename))?></p></div>
<?php else: ?>
    <div class="video-unavailable"><p><?=Language::GetText('video_unavailable')?></p></div>
<?php endif; ?>

</body>
</html>