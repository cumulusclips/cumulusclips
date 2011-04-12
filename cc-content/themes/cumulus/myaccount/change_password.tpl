
<h1><?=Language::GetText('password_header')?></h1>

<?php if ($error_msg): ?>
    <div id="error"><?=$error_msg?></div>
<?php elseif ($success): ?>
    <div id="success"><?=$success?></div>
<?php endif; ?>


<div class="block">

    <p class="row-shift"><?=Language::GetText('password_text')?></p>
    
    <form action="<?=HOST?>/myaccount/change-password/" method="post">

        <div class="row">
            <label<?=(isset ($Errors['password']) || isset ($Errors['match'])) ? ' class="errors"' : ''?>><?=Language::GetText('new_password')?>:</label>
            <input class="text" type="password" name="password" />
        </div>

        <div class="row">
            <label<?=(isset ($Errors['confirm_password']) || isset ($Errors['match'])) ? ' class="errors"' : ''?>><?=Language::GetText('confirm_password')?>:</label>
            <input class="text" type="password" name="confirm_password" />
        </div>

        <div class="row-shift">
            <input type="hidden" name="submitted" value="TRUE" />
            <input type="submit" name="button" value="Change Password" />
            <a href="" class="button"><span><?=Language::GetText('password_button')?></span></a>
        </div>
        
    </form>

</div>
    