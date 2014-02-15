<h1><?=$member->username?></h1>

<div class="message"></div>

<!-- BEGIN Member Avatar/Profile Information -->
<div id="profile_avatar">
    <?php $avatar = $this->getService('User')->getAvatarUrl($member); ?>
    <img src="<?=($avatar) ? $avatar : THEME . '/images/avatar.gif'?>" alt="<?=$member->username?>" />
    <p><a class="button_small subscribe" data-user="<?=$member->userId?>" data-type="<?=$subscribe_text?>" href=""><?=Language::GetText($subscribe_text)?></a></p>
</div>
<div id="profile_info">
    <p>
        <a href="<?=HOST?>/myaccount/message/send/<?=$member->username?>/" title="<?=Language::GetText('send_message')?>"><?=Language::GetText('send_message')?></a>&nbsp;&nbsp;&nbsp;
        <a class="flag" data-type="user" data-id="<?=$member->userId?>" href="" title="<?=Language::GetText('report_abuse')?>"><?=Language::GetText('report_abuse')?></a>&nbsp;&nbsp;&nbsp;
    </p>
    <p><strong><?=Language::GetText('joined')?>:</strong>&nbsp; <?=Functions::DateFormat('m/d/Y',$member->dateCreated)?></p>
    <p><strong><?=Language::GetText('profile_views')?>:</strong>&nbsp; <?=$member->views?></p>
    <p><strong><?=Language::GetText('subscribers')?>:</strong>&nbsp; <?php echo $sub_count[0]; ?></p>
    <?php if (!empty ($member->website)): ?>
        <p><strong><?=Language::GetText('website')?>:</strong>&nbsp; 
        <a href="<?=$member->website?>" target="_blank" rel="nofollow"><?=Functions::CutOff ($member->website, 40);?></a></p>
    <?php endif; ?>
    <p><?=nl2br($member->aboutMe)?></p>
</div>
<!-- END Member Avatar/Profile Information -->

<div class="tabs keepOne">
    <a href="" data-block="member_videos" title="<?=Language::GetText('videos')?>"><?=Language::GetText('videos')?></a>
    <a href="" data-block="member_playlists" title="<?=Language::GetText('playlists')?>"><?=Language::GetText('playlists')?></a>
    <a href="" data-block="member_activity" title="<?=Language::GetText('activity')?>"><?=Language::GetText('activity')?></a>
</div>

<!-- BEGIN Member's Videos -->
<div id="member_videos" class="tab_block" style="display:block;">
    <p class="large"><?=Language::GetText('videos')?></p>
    <?php if (count($result_videos) >= 1): ?>
        <div class="videos_list">
            <?php $this->RepeatingBlock('video.tpl', $result_videos) ?>
        </div>
    <?php else: ?>
        <p><strong><?=Language::GetText('no_member_videos')?></strong></p>
    <?php endif; ?>
</div>
<!-- END Member's Videos -->

<!-- BEGIN Member's Playlists -->
<div id="member_playlists" class="tab_block">
    <p class="large"><?=Language::GetText('playlists')?></p>
    <p><strong><?=Language::GetText('no_member_playlists')?></strong></p>
</div>
<!-- END Member's Playlists -->

<!-- BEGIN Member's Activity -->
<div id="member_activity" class="tab_block">
    <p class="large"><?=Language::GetText('activity')?></p>
    <p><strong><?=Language::GetText('no_member_activity')?></strong></p>
</div>
<!-- END Member's Activity -->