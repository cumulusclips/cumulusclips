
<div class="form">
    <form action="<?=HOST?>/login/" method="post">
        <label><?=Language::GetText('username')?>:</label>
        <input type="text" name="username" value="" />

        <label><?=Language::GetText('password')?>:</label>
        <input type="password" name="password" value="" />

        <input id="remember_me" type="checkbox" name="remember" />
        <label for="remember_me"><?=Language::GetText('remember_me')?></label>
        <input type="hidden" name="submitted_login" value="true" />
        <input class="button" type="submit" value="<?=Language::GetText('login_button')?>" />
    </form>
    <p><a href="<?=HOST?>/register/"><?=Language::GetText('no_account')?></a></p>
</div>
