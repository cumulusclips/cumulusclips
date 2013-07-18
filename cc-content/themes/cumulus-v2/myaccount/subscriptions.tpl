<?php View::SetLayout ('myaccount'); ?>

<h1><?=Language::GetText('subscriptions_header')?></h1>

<?php if ($message): ?>
    <div class="message <?=$message_type?>"><?=$message?></div>
<?php endif; ?>


<?php if ($db->Count($result) > 0): ?>

    <div class="member_list">
    <?php while ($row = $db->FetchObj ($result)): ?>

        <?php
        $sub = new Subscription($row->sub_id);
        $member = new User($sub->member);
        ?>

        <div>
            <p><a href="<?=HOST?>/members/<?=$member->username?>/" title="<?=$member->username?>"><img src="<?=$member->getAvatarUrl()?>" alt="<?=$member->username?>" /></a></p>
            <p><a href="<?=HOST?>/members/<?=$member->username?>/" title="<?=$member->username?>"><?=Functions::CutOff ($member->username,18)?></a></p>
            <p class="actions small"><a class="confirm" data-node="confirm_subscription" href="<?=HOST?>/myaccount/subscriptions/<?=$member->user_id?>/" title="<?=Language::GetText('unsubscribe')?>"><?=Language::GetText('unsubscribe')?></a></p>
        </div>

    <?php endwhile; ?>

    </div>
    <?=$pagination->Paginate()?>

<?php else: ?>
    <p><strong><?=Language::GetText('no_subscriptions')?></strong></p>
<?php endif; ?>