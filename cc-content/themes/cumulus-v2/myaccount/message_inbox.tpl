<?php View::SetLayout ('myaccount'); ?>

<h1><?=Language::GetText('message_inbox_header')?></h1>

<?php if ($message): ?>
    <div class="message <?=$message_type?>"><?=$message?></div>
<?php endif; ?>

<?php if ($db->Count($result) > 0): ?>

    <form action="<?=HOST?>/myaccount/message/inbox/" method="post">
    <table id="inbox">
        <thead>
            <tr>
                <td><?=Language::GetText('purge')?></td>
                <td><?=Language::GetText('message_status')?></td>
                <td class="subject"><?=Language::GetText('subject')?></td>
                <td><?=Language::GetText('sender')?></td>
                <td><?=Language::GetText('date')?></td>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $db->FetchObj ($result)): ?>

            <?php $message = new Message ($row->message_id); ?>

            <tr>
                <td><input type="checkbox" name="delete[]" value="<?=$message->message_id?>" /></td>
                <td><?=$message->status == 'read' ? Language::GetText('read') : Language::GetText('unread')?></td>
                <td><a href="<?=HOST?>/myaccount/message/read/<?=$message->message_id?>/" title="<?=$message->subject?>"><?=$message->subject?></a></td>
                <td><?=$message->username?></td>
                <td><?=Functions::DateFormat('m/d/Y', $message->date_created)?></td>
            </tr>

        <?php endwhile; ?>
        </tbody>
    </table>
    <div class="row_btn">
        <input type="hidden" name="submitted" value="TRUE" />
        <input class="button" type="submit" name="button" value="<?=Language::GetText('purge_button')?>" />
    </div>
    </form>

    <?=$pagination->Paginate()?>

<?php else: ?>
    <div class="block">
        <strong><?=Language::GetText('no_messages')?></strong>
    </div>
<?php endif; ?>