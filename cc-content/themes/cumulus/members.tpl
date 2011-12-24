<?php View::SetLayout ('full'); ?>
<?php View::Header(); ?>

<h1><?=Language::GetText('members_header')?></h1>

<?php if ($db->Count ($result) > 0): ?>

    <?php while ($row = $db->FetchObj ($result)): ?>

        <?php $member = new User ($row->user_id); ?>
        <div class="block member">
            <p class="avatar"><a href="<?=HOST?>/members/<?=$member->username?>/" title="<?=$member->username?>">
                <img src="<?=$member->avatar_url?>" alt="<?=$member->username?>" />
            </a></p>
            <a class="large" href="<?=HOST?>/members/<?=$member->username?>/" title="<?=$member->username?>"><?=Functions::CutOff ($member->username,18)?></a>
            <p><strong><?=Language::GetText('joined')?>: </strong><?=Functions::DateFormat('m/d/Y',$member->date_created)?></p>
            <p><strong><?=Language::GetText('videos')?>: </strong><?=$member->video_count?></p>
        </div>

    <?php endwhile; ?>

    <br clear="all" />
    <?=$pagination->Paginate()?>

<?php else: ?>
    <div class="block">
        <strong><?=Language::GetText('no_members')?></strong>
    </div>
<?php endif; ?>

<?php View::Footer(); ?>