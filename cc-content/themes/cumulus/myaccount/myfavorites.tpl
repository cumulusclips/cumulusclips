
<h1><?=Language::GetText('myfavorites_header')?></h1>
        
<?php if ($success): ?>
    <div id="success"><?=$success?></div>
<?php endif; ?>

<?php if ($db->Count($result) > 0): ?>

    <?php while ($row = $db->FetchObj ($result)): ?>

        <?php
        $video = new Video ($row->video_id);
        $rating = new Rating ($row->video_id);
        ?>

        <div class="block video">

            <a class="thumb" href="<?=HOST?>/videos/<?=$video->video_id?>/<?=$video->slug?>" title="<?=$video->title?>">
                <span class="duration"><?=$video->duration?></span>
                <span class="play-icon"></span>
                <img src="<?=$config->thumb_bucket_url?>/<?=$video->filename?>.jpg" alt="<?=$video->title?>" />
            </a>

            <a class="large" href="<?=HOST?>/videos/<?=$video->video_id?>/<?=$video->slug?>" title="<?=$video->title?>"><?=$video->title?></a>
            <p><?=Functions::CutOff ($video->description, 190)?></p>
            <span class="like">+<?=$rating->GetLikeCount()?></span>
            <span class="dislike">-<?=$rating->GetDislikeCount()?></span>
            <div class="clear"></div>

            <div class="actions">
                <a class="confirm" data-node="confirm_remove_favorite" href="<?=HOST?>/myaccount/myfavorites/<?=$video->video_id?>/" title="<?=Language::GetText('remove_favorite')?>s"><span><?=Language::GetText('remove_favorite')?></span></a>
            </div>

        </div>

    <?php endwhile; ?>

    <br clear="all" />
    <?=$pagination->Paginate()?>

<?php else: ?>
    <div class="block">
        <strong><?=Language::GetText('no_favorites')?></strong>
    </div>
<?php endif; ?>

