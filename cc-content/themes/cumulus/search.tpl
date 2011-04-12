<h1><?=Language::GetText('search_header')?></h1>
<p class="post-header"><strong><?=Language::GetText('results_for')?>: '<?php echo $cleaned; ?>'</strong></p>

<?php if ($db->Count($result) > 0): ?>

    <?php while ($row = $db->FetchObj ($result)): ?>

        <?php
        $video = new Video ($row->video_id);
        $rating = new Rating ($row->video_id);
        $tags = implode (' ', $video->tags);
        ?>

        <div class="block">

            <a class="thumb" href="<?=HOST?>/videos/<?=$video->video_id?>/<?=$video->slug?>" title="<?=$video->title?>">
                <span class="duration"><?=$video->duration?></span>
                <span class="play-icon"></span>
                <img src="<?=$config->thumb_bucket_url?>/<?=$video->filename?>.jpg" alt="<?=$video->title?>" />
            </a>

            <a class="large" href="<?=HOST?>/videos/<?=$video->video_id?>/<?=$video->slug?>" title="<?=$video->title?>"><?=Functions::CutOff ($video->title,90)?></a>
            <p><?=Functions::CutOff ($video->description, 130)?></p>
            <span class="like">+<?=$rating->GetLikeCount()?></span>
            <span class="dislike">-<?=$rating->GetDislikeCount()?></span>
            <br clear="all" />

        </div>

    <?php endwhile; ?>

<?php else: ?>
    <div class="block">
        <strong><?=Language::GetText('no_results')?></strong>
    </div>
<?php endif; ?>


<?=$pagination->Paginate()?>