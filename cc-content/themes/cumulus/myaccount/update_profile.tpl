<?php

View::AddMeta ('uploadify:theme', $config->theme_url);
View::AddMeta ('uploadify:fileExt', ';*.gif;*.png;*.jpg;*.jpeg');
View::AddMeta ('uploadify:fileDesc', Language::GetText('uploadify_supported_formats') . ': (*.gif) (*.png) (*.jpg) (*.jpeg)');
View::AddCss ('uploadify.css');
View::AddJs ('swfobject.js');
View::AddJs ('uploadify.plugin.js');
View::AddJs ('uploadify.js');
View::SetLayout ('myaccount');
View::Header();

?>

<h1><?=Language::GetText('profile_header')?></h1>

<?php if ($message): ?>
    <div id="message" class="<?=$message_type?>"><?=$message?></div>
<?php else: ?>
    <div id="message"></div>
<?php endif; ?>


<div class="block">

    <p class="row-shift large"><?=Language::GetText('personal_header')?></p>
    <p class="row-shift"><?=Language::GetText('asterisk')?></p>
    <form action="<?=HOST?>/myaccount/profile/" method="post" id="update-profile-form">

        <div class="row<?=(isset ($Errors['first_name'])) ? ' errors' : ''?>">
            <label><?=Language::GetText('first_name')?>:</label>
            <input class="text" type="text" name="first_name" value="<?=(isset ($data['first_name'])) ? $data['first_name'] : $user->first_name?>" />
        </div>

        <div class="row<?=(isset ($Errors['last_name'])) ? ' errors' : ''?>">
            <label><?=Language::GetText('last_name')?>:</label>
            <input class="text" type="text" name="last_name" value="<?=(isset ($data['last_name'])) ? $data['last_name'] : $user->last_name?>" />
        </div>

        <div class="row<?=(isset ($Errors['email'])) ? ' errors' : ''?>">
            <label>*<?=Language::GetText('email')?>:</label>
            <input class="text" type="text" name="email" value="<?=(isset ($data['email'])) ? $data['email'] : $user->email?>" />
        </div>

        <div class="row<?=(isset ($Errors['website'])) ? ' errors' : ''?>">
            <label><?=Language::GetText('website')?>:</label>
            <input class="text" type="text" name="website" value="<?=(isset ($data['website'])) ? $data['website'] : $user->website?>" />
        </div>

        <div class="row<?=(isset ($Errors['about_me'])) ? ' errors' : ''?>">
            <label><?=Language::GetText('about_me')?>:</label>
            <textarea class="text" name="about_me" rows="10" cols="45"><?=(isset ($data['about_me'])) ? $data['about_me'] : $user->about_me?></textarea>
        </div>

        <div class="row-shift">
            <input type="hidden" value="yes" name="submitted" />
            <input class="button" type="submit" name="button" value="<?=Language::GetText('profile_button')?>" />
        </div>
        
    </form>

</div>




    
<h1 id="update-avatar"><?=Language::GetText('update_avatar_header')?></h1>
<div class="block">

    <div id="avatar-left">
        <p class="avatar"><span><img alt="<?=Language::GetText('current_avatar')?>" src="<?=$user->avatar_url?>"></span></p>
        <?=Language::GetText('current_avatar')?><br />
        <a class="confirm" data-node="confirm_reset_avatar" href="<?=HOST?>/myaccount/profile/reset/"><?=Language::GetText('reset_avatar')?></a>
    </div>

    <div id="avatar-right">
        <form action="<?=HOST?>/">
        </form>
        <?=Language::GetText('update_avatar_text')?>

        <div class="upload-box">
            <form name="uploadify" action="<?=HOST?>/myaccount/upload/avatar/">
                <input id="browse-button" class="button" type="button" name="browse-button" value="<?=Language::GetText('browse_files_button')?>" />
                <input id="upload-button" class="button" type="button" name="upload-button" value="<?=Language::GetText('update_avatar_button')?>" />
                <input type="file" name="upload" id="upload" />
                <input type="hidden" name="type" id="type" value="avatar" />
                <input type="hidden" name="limit" id="limit" value="<?=1024*30?>" />
                <input type="hidden" name="timestamp" id="timestamp" value="<?=$timestamp?>" />
                <input type="hidden" name="token" id="token" value="<?=session_id()?>" />
            </form>
        </div>
    </div>
    
    <div class="clear"></div>

</div>

<?php View::Footer(); ?>