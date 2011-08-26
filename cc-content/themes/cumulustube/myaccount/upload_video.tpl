<?php

View::AddMeta ('uploadify:host', HOST);
View::AddMeta ('uploadify:token', $_SESSION['token']);
View::AddMeta ('uploadify:theme', THEME);
View::AddMeta ('uploadify:limit', VIDEO_SIZE_LIMIT);
View::AddCss ('uploadify.css');
View::AddJs ('swfobject.js');
View::AddJs ('uploadify.plugin.js');
View::AddJs ('uploadify.js');
View::SetLayout ('myaccount');
View::Header();

?>

<h1><?=Language::GetText('upload_video_header')?></h1>

<div id="error" style="display:none;"></div>

<div class="block">

    <div id="selections">
        <input name="upload-method" type="radio" id="upload-choice" checked="checked" /> &nbsp; <label for="upload-choice"><?=Language::GetText('select_hdd')?></label><br />
        <input name="upload-method" type="radio" id="grab-choice" /> &nbsp; <label for="grab-choice"><?=Language::GetText('grab_youtube')?></label>
    </div>

    
    <!-- Upload Video From Computer -->
    <div id="upload-video">

        <p class="large"><?=Language::GetText('select_hdd_header')?></p>
        <p><?=Language::GetText('select_hdd_text', array ('sitename' => $config->sitename))?></p>
        <p class="big"><?=Language::GetText('filesize_limit')?>: 100MB<br />
        <?=Language::GetText('accepted_formats')?>: *.flv, *.wmv, *.avi, *.ogg, *.mpg, *.mp4, *.mov, *.m4v</p>

        <div id="upload-button-container"><a id="begin-upload" class="button" href=""><span><?=Language::GetText('begin_upload_button')?></span></a></div>
        <input type="file" name="select-file" id="select-file" />

    </div>
    <!-- END Upload Video From Computer -->




    <!-- Grab video from YouTube -->
    <div id="grab-video">

        <p class="large"><?=Language::GetText('grab_youtube_header')?></p>
        <p><?=Language::GetText('grab_youtube_text', array('link' => HOST . '/terms/', 'link2' => HOST . '/copyright/'))?></p>
        <form action="" id="grab-form">

            <div class="row-shift">i.e. <i>http://www.youtube.com/watch?v=abcdefg</i></div>

            <div class="row">
                <label><?=Language::GetText('video_url')?>:</label>
                <input class="text grabInput" id="video-url" type="text" name="url" value="" />
                <span class="loading"><img src="<?=THEME?>/images/loading.gif" alt="loading" />&nbsp;<strong><?=Language::GetText('retrieving_video')?>...</strong></span>
            </div>

            <div class="row-shift"><a href="" class="button"><span><?=Language::GetText('grab_button')?></span></a></div>

        </form>

    </div>
    <!-- END Grab video from YouTube -->

</div>

<?php View::Footer(); ?>