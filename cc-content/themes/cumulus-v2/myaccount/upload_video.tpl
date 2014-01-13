<?php
$view->addMeta('uploadify:theme', $config->theme_url);
$view->addMeta('uploadify:buttonText', Language::getText('browse_files_button'));
$view->addCss('uploadify.css');
$view->addJs('uploadify.plugin.js');
$view->addJs('uploadify.js');
$view->setLayout('myaccount');
?>

<h1><?=Language::getText('upload_video_header')?></h1>

<div class="message"></div>

<p><?=Language::getText('upload_video_text')?></p>
<p class="big"><?=Language::getText('filesize_limit')?>: <?=round($config->video_size_limit/1048576)?>MB</p>
<p class="big"><?=Language::getText('uploadify_supported_formats') . ': ' . implode(', ', $config->accepted_video_formats)?></p>

<form name="uploadify" action="<?=HOST?>/myaccount/upload/validate/">
    <input id="upload" type="file" name="upload" />
    <input id="upload_button" class="button" type="button" value="<?=Language::getText('upload_video_button')?>" />
    <input type="hidden" name="uploadLimit" id="uploadLimit" value="<?=$config->video_size_limit?>" />
    <input type="hidden" name="uploadTimestamp" id="uploadTimestamp" value="<?=$timestamp?>" />
    <input type="hidden" name="uploadToken" id="uploadToken" value="<?=session_id()?>" />
    <input type="hidden" name="fileTypes" id="fileTypes" value="<?=htmlspecialchars(json_encode($config->accepted_video_formats))?>" />
    <input type="hidden" name="uploadType" id="uploadType" value="video" />
    <input type="hidden" name="debugUpload" id="debugUpload" value="<?=(isset($_GET['debugUpload']) ? 'true' : 'false')?>" />
    
    <div id="upload_status">
        <div class="title"></div>
        <div class="progress">
            <a href="" title="<?=Language::getText('cancel')?>"><?=Language::getText('cancel')?></a>
            <div class="meter">
                <div class="fill"></div>
            </div>
            <div class="percentage">0%</div>
        </div>
    </div>
    
</form>