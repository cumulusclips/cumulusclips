<h1><?=Language::GetText('members_header')?></h1>

<?php if ($db->Count ($result) > 0): ?>
    <div class="member_list">
    <?php while ($row = $db->FetchObj ($result)): ?>

        <?php $member = new User ($row->user_id); ?>
        <div>
            <p class="avatar"><a href="<?=HOST?>/members/<?=$member->username?>/" title="<?=$member->username?>">
                <img src="<?=$member->getAvatarUrl()?>" alt="<?=$member->username?>" />
            </a></p>
            <a href="<?=HOST?>/members/<?=$member->username?>/" title="<?=$member->username?>"><?=Functions::CutOff ($member->username,18)?></a>
            <p><strong><?=Language::GetText('joined')?>: </strong><?=Functions::DateFormat('m/d/Y',$member->date_created)?></p>
            <p><strong><?=Language::GetText('videos')?>: </strong><?=$member->video_count?></p>
        </div>

    <?php endwhile; ?>
    </div>
    <?=$pagination->Paginate()?>

<?php else: ?>
    <p><strong><?=Language::GetText('no_members')?></strong></p>
<?php endif; ?>