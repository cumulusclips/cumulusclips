
<h1><?=Language::GetText('subscribers_header')?></h1>

<?php if ($db->Count($result) >= 1): ?>

    <?php while ($row = $db->FetchObj ($result)): ?>

        <?php $member = new User ($row->user_id); ?>
        <div class="member">

            <p align="center">
                <a href="<?=HOST?>/members/<?=$member->username?>/" title="<?=$member->username?>"><img src="<?=$member->avatar?>" width="100" height="100" alt="<?=$member->username?>" /></a>
                <a class="large" href="<?=HOST?>/members/<?=$member->username?>/" title="<?=$member->username?>"><?=Functions::CutOff ($member->username,18)?></a>
            </p>
            
        </div>

    <?php endwhile; ?>

    <br clear="all" />
    <?=$pagination->Paginate()?>

<?php else: ?>
    <div class="block">
        <strong><?=Language::GetText('no_subscribers')?></strong>
    </div>
<?php endif; ?>