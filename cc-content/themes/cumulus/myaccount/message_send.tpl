<?php

View::SetLayout ('myaccount');
View::Header();

?>

<h1><?=Language::GetText('message_send_header')?></h1>

<?php if ($success): ?>
    <div id="success"><?=$success?></div>
<?php elseif ($error_msg): ?>
    <div id="error"><?=$error_msg?></div>
<?php endif; ?>


<div class="block">

    <form action="<?=HOST?>/myaccount/message/send/" method="post">

        <div class="row">
            <label<?=(isset ($Errors['recipient'])) ? ' class="errors"' : ''?>><?=Language::GetText('to')?>: </label>
            <input class="text" type="text" name="to" value="<?=$to?>" /> *<?=Language::GetText('members_username')?>
        </div>

        <div class="row">
            <label<?=(isset ($Errors['subject'])) ? ' class="errors"' : ''?>><?=Language::GetText('subject')?>: </label>
            <input class="text" type="text" name="subject" value="<?=$subject?>" />
        </div>

        <div class="row">
            <label<?=(isset ($Errors['message'])) ? ' class="errors"' : ''?>><?=Language::GetText('message')?>: </label>
            <textarea class="text" name="message"><?=$msg?></textarea>
        </div>

        <div class="row-shift">
            <input type="hidden" name="submitted" value="yes" />
            <input class="button" type="submit" name="button" value="<?=Language::GetText('message_send_button')?>" />
        </div>

    </form>

</div>

<?php View::Footer(); ?>