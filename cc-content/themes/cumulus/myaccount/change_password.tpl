<?php

View::SetLayout ('myaccount');
View::Header();

?>

<h1><?=Language::GetText('change_password_header')?></h1>

<?php if ($message): ?>
    <div id="message" class="<?=$message_type?>"><?=$message?></div>
<?php endif; ?>


<div class="block">

    <p class="row-shift"><?=Language::GetText('change_password_text')?></p>
    
    <form action="<?=HOST?>/myaccount/change-password/" method="post">

        <div class="row">
            <label<?=(isset ($errors['password']) || isset ($errors['match'])) ? ' class="errors"' : ''?>><?=Language::GetText('new_password')?>:</label>
            <input class="text" type="password" name="password" />
        </div>

        <div class="row">
            <label<?=(isset ($errors['confirm_password']) || isset ($errors['match'])) ? ' class="errors"' : ''?>><?=Language::GetText('confirm_password')?>:</label>
            <input class="text" type="password" name="confirm_password" />
        </div>

        <div class="row-shift">
            <input type="hidden" name="submitted" value="TRUE" />
            <input class="button" type="submit" name="button" value="<?=Language::GetText('change_password_button')?>" />
        </div>
        
    </form>

</div>

<?php View::Footer(); ?>