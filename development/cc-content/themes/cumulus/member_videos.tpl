<?php View::Header(); ?>

<h1><?=Language::GetText('videos_by')?>: <?=$member->username?></h1>
<p><a href="<?=HOST?>/members/<?=$member->username?>/" title="<?=Language::GetText('go_to_profile')?>"><?=Language::GetText('go_to_profile')?></a></p>


<?php if ($db->Count ($result) > 0): ?>

    <?php while ($row = $db->FetchObj ($result)): ?>

        <?php
        $video = new Video ($row->video_id);
        $rating = Rating::GetRating ($row->video_id);
        ?>

        <div class="block">

            <a class="thumb" href="<?=$video->url?>/" title="<?=$video->title?>">
                <span class="duration"><?=$video->duration?></span>
                <span class="play-icon"></span>
                <img src="<?=$config->thumb_url?>/<?=$video->filename?>.jpg" alt="<?=$video->title?>" />
            </a>

            <a class="large" href="<?=$video->url?>/" title="<?=$video->title?>"><?=$video->title?></a>
            <p><?=Functions::CutOff ($video->description, 190)?></p>
            <span class="like">+<?=$rating->likes?></span>
            <span class="dislike">-<?=$rating->dislikes?></span>
            <br clear="all" />

        </div>

    <?php endwhile; ?>

    <?=$pagination->Paginate()?>

<?php else: ?>
    <div class="block"><strong><?=Language::GetText('no_member_videos')?></strong></div>
<?php endif; ?>

<?php View::Footer(); ?>