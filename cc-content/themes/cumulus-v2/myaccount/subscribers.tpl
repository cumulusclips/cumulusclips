<?php $view->SetLayout ('myaccount'); ?>

<h1><?=Language::GetText('subscribers_header')?></h1>

<?php if (count($subscribers) >= 1): ?>
    <div class="member_list">
    <?php $userService = $view->getService('User'); ?>
    <?php foreach ($subscribers as $subscriber): ?>
        <div>
            <p><a href="<?=HOST?>/members/<?=$subscriber->username?>/" title="<?=$subscriber->username?>"><img class="picture" src="<?=$userService->getAvatarUrl($subscriber)?>" alt="<?=$subscriber->username?>" /></a></p>
            <p><a href="<?=HOST?>/members/<?=$subscriber->username?>/" title="<?=$subscriber->username?>"><?=Functions::CutOff ($subscriber->username,18)?></a></p>
        </div>
    <?php endforeach; ?>

    </div>
    <?=$pagination->Paginate()?>

<?php else: ?>
    <p><strong><?=Language::GetText('no_subscribers')?></strong></p>
<?php endif; ?>