<?php

View::SetLayout ('myaccount');
View::Header();

?>

<h1><?=Language::GetText('privacy_settings_header')?></h1>

<?php if ($message): ?>
    <div class="message <?=$message_type?>"><?=$message?></div>
<?php endif; ?>

<div class="form">
    <form action="<?=HOST?>/myaccount/privacy-settings/" method="post">

        <label><?=Language::GetText('alert_comment')?>:</label>
        <select class="dropdown" size="1" name="video_comment">
            <option value="1"<?=(isset ($data['video_comment']) && $data['video_comment'] == '1' || $privacy->OptCheck ('video_comment')) ? ' selected="selected"' : ''?>><?=Language::GetText('yes')?></option>
            <option value="0"<?=(isset ($data['video_comment']) && $data['video_comment'] == '0' || !$privacy->OptCheck ('video_comment')) ?' selected="selected"' : ''?>><?=Language::GetText('no')?></option>
        </select>

        <label><?=Language::GetText('alert_video')?>:</label>
        <select class="dropdown" size="1" name="new_video">
            <option value="1"<?=(isset ($data['new_video']) && $data['new_video'] == '1' || $privacy->OptCheck ('new_video')) ? ' selected="selected"' : ''?>><?=Language::GetText('yes')?></option>
            <option value="0"<?=(isset ($data['new_video']) && $data['new_video'] == '0' || !$privacy->OptCheck ('new_video')) ? ' selected="selected"' : ''?>><?=Language::GetText('no')?></option>
        </select>

        <label><?=Language::GetText('alert_message')?>:</label>
        <select class="dropdown" size="1" name="new_message">
            <option value="1"<?=(isset ($data['new_message']) && $data['new_message'] == '1' || $privacy->OptCheck ('new_message')) ? ' selected="selected"' : ''?>><?=Language::GetText('yes')?></option>
            <option value="0"<?=(isset ($data['new_message']) && $data['new_message'] == '0' || !$privacy->OptCheck ('new_message')) ? ' selected="selected"' : ''?>><?=Language::GetText('no')?></option>
        </select>

        <input type="hidden" name="submitted" value="TRUE" />
        <input class="button" type="submit" name="button" value="<?=Language::GetText('privacy_settings_button')?>" />
    </form>
</div>

<?php View::Footer(); ?>