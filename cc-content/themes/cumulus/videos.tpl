
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


<?php if ($db->Count($result) > 0): ?>

    <?php while ($row = $db->FetchRow ($result)): ?>

        <?php
        $video = new Video ($row[0]);
        $rating = new Rating ($row[0]);
        $tags = implode (' ', $video->tags);
        ?>

        <div class="block">

            <a class="thumb" href="<?=HOST?>/videos/<?=$video->video_id?>/<?=$video->slug?>" title="<?=$video->title?>">
                <span class="duration"><?=$video->duration?></span>
                <span class="play-icon"></span>
                <img src="<?=$config->thumb_bucket_url?>/<?=$video->filename?>.jpg" alt="<?=$video->title?>" />
            </a>

            <a class="large" href="<?=HOST?>/videos/<?=$video->video_id?>/<?=$video->slug?>" title="<?=$video->title?>"><?=$video->title?></a>
            <p><?=Functions::CutOff ($video->description, 190)?></p>
            <span class="like">+<?=$rating->GetLikeCount()?></span>
            <span class="dislike">-<?=$rating->GetDislikeCount()?></span>
            <br clear="all" />

        </div>


<!--
<?=$config->thumb_bucket_url?>
<span><strong>Views:</strong>&nbsp;<?=$video->views?></span>
<span><strong>Uploaded On:</strong>&nbsp;<?=$video->date_uploaded?></span>
-->


    <?php endwhile; ?>

<?php else: ?>
    <div class="block"><strong><?=Language::GetText('no_videos')?></strong></div>
<?php endif; ?>

<br clear="all" />
<?=$pagination->Paginate()?>

    