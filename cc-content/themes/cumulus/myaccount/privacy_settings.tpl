
<h1><?=Language::GetText('privacy_header')?></h1>

<?php if ($errors): ?>
    <div id="error"><?=$errors?></div>
<?php elseif ($success): ?>
    <div id="success"><?=$success?></div>
<?php endif; ?>

<div class="block">

    <form action="<?=HOST?>/myaccount/privacy-settings/" method="post" id="privacy-form">

        <p class="large"><?=Language::GetText('email')?></p>

        <p><strong><?=Language::GetText('alert_comment')?>:</strong></p>
        <div class="row">
            <select class="dropdown" size="1" name="video_comment">
                <option value="yes"<?=(isset ($data['video_comment']) && $data['video_comment'] == 'yes' || $privacy->OptCheck ('video_comment')) ? ' selected="selected"' : ''?>><?=Language::GetText('yes')?></option>
                <option value="no"<?=(isset ($data['video_comment']) && $data['video_comment'] == 'no' || !$privacy->OptCheck ('video_comment')) ?' selected="selected"' : ''?>><?=Language::GetText('no')?></option>
            </select>
        </div>

        <div class="row">
            <p><strong><?=Language::GetText('alert_video')?>:</strong></p>
            <select class="dropdown" size="1" name="new_video">
                <option value="yes"<?=(isset ($data['new_video']) && $data['new_video'] == 'yes' || $privacy->OptCheck ('new_video')) ? ' selected="selected"' : ''?>><?=Language::GetText('yes')?></option>
                <option value="no"<?=(isset ($data['new_video']) && $data['new_video'] == 'no' || !$privacy->OptCheck ('new_video')) ? ' selected="selected"' : ''?>><?=Language::GetText('no')?></option>
            </select>
        </div>

        <div class="row">
            <p><strong><?=Language::GetText('alert_message')?>:</strong></p>
            <select class="dropdown" size="1" name="new_message">
                <option value="yes"<?=(isset ($data['new_message']) && $data['new_message'] == 'yes' || $privacy->OptCheck ('new_message')) ? ' selected="selected"' : ''?>><?=Language::GetText('yes')?></option>
                <option value="no"<?=(isset ($data['new_message']) && $data['new_message'] == 'no' || !$privacy->OptCheck ('new_message')) ? ' selected="selected"' : ''?>><?=Language::GetText('no')?></option>
            </select>
        </div>

        <div class="row">
            <p><strong><?=Language::GetText('alert_newsletter')?>:</strong></p>
            <select class="dropdown" size="1" name="newsletter">
                <option value="yes"<?php if (isset ($data['newsletter']) && $data['newsletter'] == 'yes') { echo ' selected="selected"'; } elseif ($privacy->OptCheck ('newsletter')) { echo ' selected="selected"'; } ?>><?=Language::GetText('yes')?></option>
                <option value="no"<?php if (isset ($data['newsletter']) && $data['newsletter'] == 'no') { echo ' selected="selected"'; } elseif (!$privacy->OptCheck ('newsletter')) { echo ' selected="selected"'; } ?>><?=Language::GetText('no')?></option>
            </select>
        </div>

        <div class="row">
            <input type="hidden" name="submitted" value="TRUE" />
            <a href="" class="button"><span><?=Language::GetText('privacy_button')?></span></a>
        </div>

    </form>

</div>