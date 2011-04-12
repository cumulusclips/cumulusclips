
<h1><?=Language::GetText('profile_header')?></h1>

<?php if ($error_msg): ?>
    <div id="error"><?=$error_msg?></div>
<?php elseif ($success_msg): ?>
    <div id="success"><?=$success_msg?></div>
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
            <textarea class="text" name="about_me"><?=(isset ($data['about_me'])) ? $data['about_me'] : $user->about_me?></textarea>
        </div>

        <div class="row-shift">
            <input type="hidden" value="yes" name="submitted" />
            <a href="" class="button"><span><?=Language::GetText('profile_button')?></span></a>
        </div>
        
    </form>

</div>
