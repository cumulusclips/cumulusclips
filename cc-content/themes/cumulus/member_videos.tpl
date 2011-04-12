<h1><?=$member->username?>'s <?php echo ($viewing == 'favorites')?'Favorite Videos':'Videos'; ?></h1>
<p><a href="<?=HOST?>/members/<?=$member->username?>/" title="Go to Profile">Go to Profile</a></p>


<?php if ($db->Count ($result) > 0): ?>

    <?php while ($row = $db->FetchObj ($result)): ?>

        <?php
        $video = new Video ($row->video_id);
        $rating = new Rating ($row->video_id);
        $title = Functions::CutOff ($video->title, 45);
        ?>

        <div class="video">
            <a class="video-thumb" href="<?=HOST?>/videos/<?=$video->video_id?>/<?=$video->slug?>/" title="<?=$video->title?>">
                <span class="play-button"></span>
                <span class="duration"><?=$video->duration?></span>
                <img src="<?=$config->thumb_bucket_url?>/<?=$video->filename?>.jpg" alt="<?=$video->title?>" />
            </a>
            <h2><a href="<?=HOST?>/videos/<?=$video->video_id?>/<?=$video->dashed?>/" title="<?=$video->title?>"><?=$title?></a></h2>
            <p><strong>By:</strong> <a href="<?=HOST?>/channels/<?=$video->username?>/" title="<?=$video->username?>"><?=$video->username?></a></p>
        </div>

    <?php endwhile; ?>

<?php else: ?>
    <div class="block"><strong>No videos have been added yet!</strong></div>
<?php endif; ?>

<br clear="all" />
<?=$pagination->Paginate()?>
