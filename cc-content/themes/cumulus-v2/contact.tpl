<h1><?=Language::GetText('contact_header')?></h1>

<?php if ($message): ?>
    <div class="message <?=$message_type?>"><?=$message?></div>
<?php endif; ?>

<p><?=Language::GetText('contact_text')?></p>

<div class="form wide">
    <form action="<?=HOST?>/contact/" method="post">
        <label for="name" class="<?=(!empty ($Errors['name'])) ? 'error' : ''?>"><?=Language::GetText('name')?>:</label>
        <input type="text" name="name" value="<?=(!empty ($Errors) && !empty ($name)) ? htmlspecialchars ($name) : ''?>" />

        <label for="email" class="<?=(!empty ($Errors['email'])) ? 'error' : ''?>"><?=Language::GetText('email')?>:</label>
        <input type="text" name="email" value="<?=(!empty ($Errors) && !empty ($email)) ? $email : ''?>" />

        <label for="feedback" class="<?=(!empty ($Errors['feedback'])) ? 'error' : ''?>"><?=Language::GetText('message')?>:</label>
        <textarea name="feedback" cols="40" rows="9"><?=(!empty ($Errors) && !empty ($feedback)) ? htmlspecialchars ($feedback) : ''?></textarea>

        <input type="hidden" value="TRUE" name="submitted" />
        <input class="button" type="submit" name="button" value="<?=Language::GetText('contact_button')?>" />
    </form>
</div>