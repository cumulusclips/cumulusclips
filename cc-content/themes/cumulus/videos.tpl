<?php View::Header(); ?>

<!--
<h1>Video Categories</h1>
<div class="short-block">
    <p><a href="<?php echo HOST; ?>/videos/recent/" title="Most Recent Videos">Most Recent</a></p>
    <p><a href="<?php echo HOST; ?>/videos/most-viewed/" title="Most Viewed Videos">Most Views</a></p>
    <p><a href="<?php echo HOST; ?>/videos/most-discussed/" title="Most Discussed Videos">Most Discussed</a></p>
    <br /><br />
    <ul id="cat-list">

        <?php while ($cat = $db->FetchAssoc ($result_cats)): ?>
            <?php $dashed = str_replace (' ','-',$cat['cat_name']); ?>
            <li><a href="<?=HOST?>/videos/<?=$dashed?>/" title="<?=$cat['cat_name']?>"><?=$cat['cat_name']?></a></li>
        <?php endwhile; ?>

    </ul>
</div>
-->



<h1><?=Language::GetText('videos_header')?></h1>
<h2><?=Language::GetText('viewing')?>: <?php echo ($category)?$category:'All'; ?> Videos</h2>


<?php if (!empty ($browse_videos)): ?>
    <?php View::RepeatingBlock('video.tpl', $browse_videos); ?>
    <?=$pagination->Paginate()?>
<?php else: ?>
    <div class="block"><strong><?=Language::GetText('no_videos')?></strong></div>
<?php endif; ?>

<?php View::Footer(); ?>