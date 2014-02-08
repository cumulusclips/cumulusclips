<?php $this->SetLayout('myaccount'); ?>

<h1><?=Language::GetText('message_read_header')?></h1>
<div id="message_display">
    <p><a href="<?=HOST?>/myaccount/message/inbox/" title="<?=Language::GetText('return_inbox')?>"><?=Language::GetText('return_inbox')?></a></p>
    <p><strong><?=Language::GetText('sender')?>: </strong>&nbsp;&nbsp;&nbsp;<?=$message->username?></p>
    <p><strong><?=Language::GetText('date')?>: </strong>&nbsp;&nbsp;&nbsp;<?=Functions::DateFormat('m/d/Y', $message->dateCreated)?></p>
    <p><strong><?=Language::GetText('subject')?>: </strong>&nbsp;&nbsp;&nbsp;<?=htmlspecialchars($message->subject)?></p>
    <div id="message_body"><?=nl2br(htmlspecialchars($message->message))?></div>
    <a class="button" href="<?=HOST?>/myaccount/message/reply/<?=$message->messageId?>/"><?=Language::GetText('reply')?></a>
    <a class="button" href="<?=HOST?>/myaccount/message/inbox/<?=$message->messageId?>/"><?=Language::GetText('delete')?></a>
</div>