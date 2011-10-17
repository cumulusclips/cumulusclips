<?php View::Header(); ?>

<h1><?=Language::GetText('contact_header')?></h1>

<?php if ($message): ?>
    <div id="message" class="<?=$message_type?>"><?=$message?></div>
<?php endif; ?>


<div class="block">

    <p><?=Language::GetText('contact_text')?></p>

    <form action="<?=HOST?>/contact/" method="post" id="contact-form">

        <div class="row">
            <label for="name" class="<?=(!empty ($Errors['name'])) ? 'errors' : ''?>"><?=Language::GetText('name')?>:</label>
            <input class="text" type="text" name="name" value="<?=(!empty ($Errors) && !empty ($name)) ? htmlspecialchars ($name) : ''?>" />
        </div>

        <div class="row">
            <label for="email" class="<?=(!empty ($Errors['email'])) ? 'errors' : ''?>"><?=Language::GetText('email')?>:</label>
            <input class="text" type="text" name="email" value="<?=(!empty ($Errors) && !empty ($email)) ? $email : ''?>" />
        </div>
        
        <div class="row">
            <label for="feedback" class="<?=(!empty ($Errors['feedback'])) ? 'errors' : ''?>"><?=Language::GetText('message')?>:</label>
            <textarea name="feedback" class="text" cols="40" rows="9"><?=(!empty ($Errors) && !empty ($feedback)) ? htmlspecialchars ($feedback) : ''?></textarea>
        </div>

        <div class="row-shift">
            <input type="hidden" value="TRUE" name="submitted" />
            <input class="button" type="submit" name="button" value="<?=Language::GetText('contact_button')?>" />
        </div>
        
    </form>

</div>

<?php View::Footer(); ?>