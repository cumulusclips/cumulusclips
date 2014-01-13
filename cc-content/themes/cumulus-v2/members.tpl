<h1><?=Language::GetText('members_header')?></h1>

<?php if (count($userResults) > 0): ?>
    <div class="member_list">
    <?php $userMapper = new UserMapper(); ?>
    <?php foreach ($userResults as $member): ?>
        <div>
            <p class="avatar"><a href="<?=HOST?>/members/<?=$member->username?>/" title="<?=$member->username?>">
                <img src="<?=$view->getService('User')->getAvatarUrl($member)?>" alt="<?=$member->username?>" />
            </a></p>
            <a href="<?=HOST?>/members/<?=$member->username?>/" title="<?=$member->username?>"><?=Functions::CutOff($member->username,18)?></a>
            <p><strong><?=Language::GetText('joined')?>: </strong><?=Functions::DateFormat('m/d/Y',$member->dateCreated)?></p>
            <p><strong><?=Language::GetText('videos')?>: </strong><?=$userMapper->getVideoCount($member->userId)?></p>
        </div>
    <?php endforeach; ?>
    </div>
    <?=$pagination->Paginate()?>

<?php else: ?>
    <p><strong><?=Language::GetText('no_members')?></strong></p>
<?php endif; ?>