<?php View::SetLayout ('myaccount'); ?>

<h1><?=Language::GetText('subscriptions_header')?></h1>

<?php if ($message): ?>
    <div class="message <?=$message_type?>"><?=$message?></div>
<?php endif; ?>


<?php if ($subscriptions > 0): ?>

    <div class="member_list">
    <?php $userService = View::getService('User'); ?>
    <?php foreach ($subscriptions as $subscription): ?>
        <div>
            <p><a href="<?=HOST?>/members/<?=$subscription->username?>/" title="<?=$subscription->username?>"><img src="<?=$userService->getAvatarUrl($subscription)?>" alt="<?=$subscription->username?>" /></a></p>
            <p><a href="<?=HOST?>/members/<?=$subscription->username?>/" title="<?=$subscription->username?>"><?=Functions::CutOff ($subscription->username,18)?></a></p>
            <p class="actions small"><a class="confirm" data-node="confirm_subscription" href="<?=HOST?>/myaccount/subscriptions/<?=$subscription->userId?>/" title="<?=Language::GetText('unsubscribe')?>"><?=Language::GetText('unsubscribe')?></a></p>
        </div>
    <?php endforeach; ?>

    </div>
    <?=$pagination->Paginate()?>

<?php else: ?>
    <p><strong><?=Language::GetText('no_subscriptions')?></strong></p>
<?php endif; ?>