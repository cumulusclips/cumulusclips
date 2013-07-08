<?php View::Header(); ?>

<h1><?=Language::GetText('videos_by')?>: <?=$member->username?></h1>
<p><a href="<?=HOST?>/members/<?=$member->username?>/" title="<?=Language::GetText('go_to_profile')?>"><?=Language::GetText('go_to_profile')?></a></p>

<?php if (count($video_list) > 0): ?>
    <div class="videos_list">
        <?php View::RepeatingBlock('video.tpl', $video_list) ?>
    </div>
    <?=$pagination->Paginate()?>
<?php else: ?>
    <p><strong><?=Language::GetText('no_member_videos')?></strong></p>
<?php endif; ?>

<?php View::Footer(); ?>