
<h1><?=Language::GetText('message_read_header')?></h1>
<div class="block" id="message-display">

    <p><a href="<?=HOST?>/myaccount/message/inbox/" title="<?=Language::GetText('return_inbox')?>"><?=Language::GetText('return_inbox')?></a></p>
    <p><strong><?=Language::GetText('sender')?>: </strong>&nbsp;&nbsp;&nbsp;<?=$message->username?></p>
    <p><strong><?=Language::GetText('date')?>: </strong>&nbsp;&nbsp;&nbsp;<?=$message->date?></p>
    <p><strong><?=Language::GetText('subject')?>: </strong>&nbsp;&nbsp;&nbsp;<?=$message->subject?></p>
    <div id="message-body"><?=nl2br ($message->message)?></div>
    <a class="button" href="<?=HOST?>/myaccount/message/reply/<?=$message->message_id?>/"><span><?=Language::GetText('reply')?></span></a>
    <a class="button" href="<?=HOST?>/myaccount/message/inbox/<?=$message->message_id?>/"><span><?=Language::GetText('delete')?></span></a>

</div>