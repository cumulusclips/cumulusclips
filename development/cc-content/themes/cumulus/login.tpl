<?php View::Header(); ?>

<h1><?=Language::GetText('login_header')?></h1>

<?php if ($message): ?>
    <div id="message" class="<?=$message_type?>"><?=$message?></div>
<?php endif; ?>

<div class="block" id="login-block">

    <form action="<?=HOST?>/login/" method="post">

        <div class="row">
            <label class="<?=$login_submit && !$username ? 'errors':''?>"><?=Language::GetText('username')?>:</label>
            <input name="username" type="text" class="text" value="<?=(isset($_COOKIE['username']) ? $_COOKIE['username'] : ($login_submit && $username ? $username : ''));?>" />
        </div>

        <div class="row">
            <label class="<?=($login_submit && !$password) ? 'errors' : ''?>"><?=Language::GetText('password')?>:</label>
            <input class="text" type="password" name="password" value="<?=isset ($_COOKIE['password']) ? $_COOKIE['password'] : ''?>" />
        </div>

        <div class="row-shift">
            <input name="remember" type="checkbox" id="remember" value="TRUE" />
            <label for="remember"> &nbsp;<?=Language::GetText('remember_me')?></label>
        </div>

        <div class="row-shift">
            <input type="hidden" name="submitted_login" value="TRUE" />
            <input class="button" type="submit" name="button" value="<?=Language::GetText('login_button')?>" />
        </div>

        <div class="row-shift">
            <p><a href="<?=HOST?>/register/"><?=Language::GetText('no_account')?></a></p>
            <p><a href="#" data-block="forgot-block" class="showhide"><?=Language::GetText('forgot_your_login')?></a></p>
        </div>

    </form>

</div>


<div class="block" id="forgot-block"<?=($forgot_submit) ? ' style="display:block;"' : ''?>>

    <form action="<?=HOST?>/login/" method="post">
    <h2><?=Language::GetText('forgot_header')?></h2>
    <p><?=Language::GetText('forgot_text')?></p>
    
    <div class="row">
        <label class="<?=($forgot_submit && $message_type == 'error') ? 'errors' : ''?>"><?=Language::GetText('email')?>:</label>
        <input class="text" type="text" id="email" name="email" />
    </div>

    <div class="row-shift">
        <input type="hidden" name="submitted_forgot" value="TRUE" />
        <input class="button" type="submit" name="button" value="<?=Language::GetText('forgot_button')?>" />
    </div>
    </form>

</div>

<?php View::Footer(); ?>