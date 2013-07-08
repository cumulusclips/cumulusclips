<?php View::Header(); ?>

<h1><?=$member->username?></h1>

<div class="message"></div>

<!-- BEGIN Member Avatar/Profile Information -->
<div id="profile_avatar">
    <img src="<?=$member->getAvatarUrl()?>" alt="<?=$member->username?>" />
    <p><a class="button_small subscribe" data-user="<?=$member->user_id?>" data-type="<?=$subscribe_text?>" href=""><?=Language::GetText($subscribe_text)?></a></p>
</div>
<div id="profile_info">
    <p>
        <a href="<?=HOST?>/myaccount/message/send/<?=$member->username?>/" title="<?=Language::GetText('send_message')?>"><?=Language::GetText('send_message')?></a>&nbsp;&nbsp;&nbsp;
        <a class="flag" data-type="member" data-id="<?=$member->user_id?>" href="" title="<?=Language::GetText('report_abuse')?>"><?=Language::GetText('report_abuse')?></a>&nbsp;&nbsp;&nbsp;
        <a href="<?=HOST?>/feed/<?=$member->username?>/" title="<?=Language::GetText('member_rss')?>"><?=Language::GetText('member_rss')?></a>
    </p>
    <p><strong><?=Language::GetText('joined')?>:</strong>&nbsp; <?=Functions::DateFormat('m/d/Y',$member->date_created)?></p>
    <p><strong><?=Language::GetText('profile_views')?>:</strong>&nbsp; <?=$member->views?></p>
    <p><strong><?=Language::GetText('subscribers')?>:</strong>&nbsp; <?php echo $sub_count[0]; ?></p>
    <?php if (!empty ($member->website)): ?>
        <p><strong><?=Language::GetText('website')?>:</strong>&nbsp; 
        <a href="<?=$member->website?>" target="_blank" rel="nofollow"><?=Functions::CutOff ($member->website, 40);?></a></p>
    <?php endif; ?>
    <p><?=nl2br($member->about_me)?></p>
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
            <?php View::RepeatingBlock('video.tpl', $result_videos) ?>
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

<?php View::Footer(); ?>