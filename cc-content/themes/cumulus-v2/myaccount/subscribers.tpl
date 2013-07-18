<?php View::SetLayout ('myaccount'); ?>

<h1><?=Language::GetText('subscribers_header')?></h1>

<?php if ($db->Count($result) >= 1): ?>
    <div class="member_list">
        
    <?php while ($row = $db->FetchObj ($result)): ?>
        <?php $member = new User($row->user_id); ?>
        <div>
            <p><a href="<?=HOST?>/members/<?=$member->username?>/" title="<?=$member->username?>"><img class="picture" src="<?=$member->getAvatarUrl()?>" alt="<?=$member->username?>" /></a></p>
            <p><a href="<?=HOST?>/members/<?=$member->username?>/" title="<?=$member->username?>"><?=Functions::CutOff ($member->username,18)?></a></p>
        </div>
    <?php endwhile; ?>

    </div>
    <?=$pagination->Paginate()?>

<?php else: ?>
    <p><strong><?=Language::GetText('no_subscribers')?></strong></p>
<?php endif; ?>