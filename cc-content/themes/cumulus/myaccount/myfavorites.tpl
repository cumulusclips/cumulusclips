<?php

View::SetLayout ('myaccount');
View::Header();

?>

<h1><?=Language::GetText('myfavorites_header')?></h1>
        
<?php if ($message): ?>
    <div id="message" class="<?=$message_type?>"><?=$message?></div>
<?php endif; ?>

<?php if ($db->Count($result) > 0): ?>

    <?php while ($row = $db->FetchObj ($result)): ?>

        <?php
        $video = new Video ($row->video_id);
        $rating = Rating::GetRating ($row->video_id);
        ?>

        <div class="block video">

            <a class="thumb" href="<?=$video->url?>/" title="<?=$video->title?>">
                <span class="duration"><?=$video->duration?></span>
                <span class="play-icon"></span>
                <img src="<?=$config->thumb_url?>/<?=$video->filename?>.jpg" alt="<?=$video->title?>" />
            </a>

            <a class="large" href="<?=$video->url?>/" title="<?=$video->title?>"><?=$video->title?></a>
            <p><?=Functions::CutOff ($video->description, 190)?></p>
            <span class="like">+<?=$rating->likes?></span>
            <span class="dislike">-<?=$rating->dislikes?></span>
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

<?php View::Footer(); ?>