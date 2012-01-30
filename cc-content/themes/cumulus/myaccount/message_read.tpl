<?php

View::SetLayout ('myaccount');
View::Header();

?>

<h1><?=Language::GetText('message_read_header')?></h1>
<div class="block" id="message-display">

    <p><a href="<?=HOST?>/myaccount/message/inbox/" title="<?=Language::GetText('return_inbox')?>"><?=Language::GetText('return_inbox')?></a></p>
    <p><strong><?=Language::GetText('sender')?>: </strong>&nbsp;&nbsp;&nbsp;<?=$message->username?></p>
    <p><strong><?=Language::GetText('date')?>: </strong>&nbsp;&nbsp;&nbsp;<?=Functions::DateFormat('m/d/Y', $message->date_created)?></p>
    <p><strong><?=Language::GetText('subject')?>: </strong>&nbsp;&nbsp;&nbsp;<?=$message->subject?></p>
    <div id="message-body"><?=nl2br ($message->message)?></div>
    <a class="button" href="<?=HOST?>/myaccount/message/reply/<?=$message->message_id?>/"><?=Language::GetText('reply')?></a>
    <a class="button" href="<?=HOST?>/myaccount/message/inbox/<?=$message->message_id?>/"><?=Language::GetText('delete')?></a>

</div>

<?php View::Footer(); ?>