<?php

$supported_formats = Language::GetText ('uploadify_supported_formats') . ':' . Functions::GetVideoTypes ('fileDesc');
View::AddMeta ('uploadify:theme', $config->theme_url);
View::AddMeta ('uploadify:fileExt', Functions::GetVideoTypes ('fileExt'));
View::AddMeta ('uploadify:fileDesc', $supported_formats);
View::AddCss ('uploadify.css');
View::AddJs ('swfobject.js');
View::AddJs ('uploadify.plugin.js');
View::AddJs ('uploadify.js');
View::SetLayout ('myaccount');
View::Header();

?>

<h1><?=Language::GetText('upload_video_header')?></h1>

<div id="message"></div>

<div id="upload-video" class="block">

    <p><?=Language::GetText('upload_video_text')?></p>
    <p class="big"><?=Language::GetText('filesize_limit')?>: 100MB</p>
    <p class="big"><?=$supported_formats?></p>


    <div class="upload-box">
        <form name="uploadify" action="<?=HOST?>/myaccount/upload/validate/">
            <input id="browse-button" class="button" type="button" name="browse-button" value="<?=Language::GetText('browse_files_button')?>" />
            <input id="upload-button" class="button" type="button" name="upload-button" value="<?=Language::GetText('upload_video_button')?>" />
            <input type="file" name="upload" id="upload" />
            <input type="hidden" name="limit" id="limit" value="<?=$config->video_size_limit?>" />
            <input type="hidden" name="timestamp" id="timestamp" value="<?=$timestamp?>" />
            <input type="hidden" name="token" id="token" value="<?=session_id()?>" />
        </form>
    </div>

</div>

<?php View::Footer(); ?>