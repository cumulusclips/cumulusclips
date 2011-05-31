<h1><?=Language::GetText('members_header')?></h1>

<?php if ($db->Count ($result) > 0): ?>


    <?php while ($row = $db->FetchObj ($result)): ?>

        <?php $member = new User ($row->user_id); ?>
        <div class="member">

            <p class="picture"><a href="<?=HOST?>/members/<?=$member->username?>/" title="<?=$member->username?>">
                <img src="<?=$member->avatar?>" alt="<?=$member->username?>" />
            </a></p>
            <a class="large" href="<?=HOST?>/members/<?=$member->username?>/" title="<?=$member->username?>"><?=Functions::CutOff ($member->username,18)?></a>
            <p><strong><?=Language::GetText('member_since')?>: </strong><?=$member->date_created?></p>
            <p><strong><?=Language::GetText('videos_uploaded')?>: </strong><?=$member->video_count?></p>

        </div>

    <?php endwhile; ?>


<?php else: ?>

    <div class="block">
        <strong><?=Language::GetText('no_members')?></strong>
    </div>

<?php endif; ?>

<br clear="all" />
<?=$pagination->Paginate()?>
