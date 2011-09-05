<?php

View::SetLayout ('myaccount');
View::Header();

?>

<h1><?=Language::GetText('message_send_header')?></h1>

<?php if ($message): ?>
    <div id="message" class="<?=$message_type?>"><?=$message?></div>
<?php endif; ?>


<div class="block">

    <form action="<?=HOST?>/myaccount/message/send/" method="post">

        <div class="row">
            <label<?=(isset ($errors['recipient'])) ? ' class="errors"' : ''?>><?=Language::GetText('to')?>: </label>
            <input class="text" type="text" name="to" value="<?=$to?>" /> *<?=Language::GetText('members_username')?>
        </div>

        <div class="row">
            <label<?=(isset ($errors['subject'])) ? ' class="errors"' : ''?>><?=Language::GetText('subject')?>: </label>
            <input class="text" type="text" name="subject" value="<?=$subject?>" />
        </div>

        <div class="row">
            <label<?=(isset ($errors['message'])) ? ' class="errors"' : ''?>><?=Language::GetText('message')?>: </label>
            <textarea class="text" name="message" cols="45" rows="10"><?=$msg?></textarea>
        </div>

        <div class="row-shift">
            <input type="hidden" name="submitted" value="yes" />
            <input class="button" type="submit" name="button" value="<?=Language::GetText('message_send_button')?>" />
        </div>

    </form>

</div>

<?php View::Footer(); ?>