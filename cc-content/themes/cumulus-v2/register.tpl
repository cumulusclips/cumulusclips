<?php $view->AddJs ('username.js'); ?>

<h1><?=Language::GetText('register_header')?></h1>

<?php if ($message): ?>
    <div class="message <?=$message_type?>"><?=$message?></div>
<?php endif; ?>

<?=Language::GetText('register_text', array ('sitename' => $config->sitename))?>

<div class="form">

    <form action="<?=HOST?>/register/" method="post">
        <label class="<?=(isset ($errors['email'])) ? 'error' : ''?>"><?=Language::GetText('email')?>:</label>
        <input name="email" type="text" class="text" value="<?=(isset ($errors, $data['email'])) ? $data['email'] : ''?>" />

        <label class="<?=(isset ($errors['username'])) ? 'error' : ''?>"><?=Language::GetText('username')?>:</label>
        <input name="username" type="text" class="text" id="username" value="<?=(isset ($errors, $data['username'])) ? $data['username']:''?>" maxlength="30" />
        <p id="status"></p>
        <p class="hint"><?=Language::GetText('username_req')?></p>

        <label class="<?=(isset ($errors['password']) || isset($errors['match'])) ? 'error' : ''?>"><?=Language::GetText('password')?>:</label>
        <input name="password" type="password" class="text" value="<?=(isset ($errors, $data['password'])) ? $data['password']:''?>" />

        <label class="<?=(isset ($errors['password_confirm']) || isset($errors['match'])) ? 'error' : ''?>"><?=Language::GetText('confirm_password')?>:</label>
        <input name="password_confirm" type="password" class="text" value="<?=(isset ($errors, $data['password'])) ? $data['password']:''?>" />

        <input type="hidden" name="submitted" value="TRUE" />
        <input class="button" type="submit" name="button" value="<?=Language::GetText('register_button')?>" />
    </form>

</div>