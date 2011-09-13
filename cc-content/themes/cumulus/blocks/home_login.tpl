
<div id="home-login" class="block">
    <span id="tab"><?=Language::GetText('account_login')?></span>

    <form action="<?=HOST?>/login/" method="post">

        <div class="row">
            <input type="text" class="text defaultText" title="<?=Language::GetText('username')?>" value="<?=Language::GetText('username')?>" name="username" />
        </div>

        <div class="row">
            <input type="text" class="text defaultText defaultTextPassword" title="<?=Language::GetText('password')?>" value="<?=Language::GetText('password')?>" name="defaultTextPassword" />
            <input style="display:none;" type="password" class="text defaultText" name="password" />
        </div>

        <div>
            <input type="hidden" name="submitted_login" value="TRUE" />
            <input class="button" type="submit" name="button" value="<?=Language::GetText('login_button')?>" />
        </div>

        <p><a href="<?=HOST?>/register/" title=""><?=Language::GetText('no_account')?></a></p>
        <p><a href="<?=HOST?>/login/forgot/" title=""><?=Language::GetText('forgot_your_login')?></a></p>

    </form>

</div>
