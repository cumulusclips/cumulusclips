
<h1><?=Language::GetText('myvideos_header')?></h1>
        
<?php if ($success): ?>
    <div id="success"><?=$success?></div>
<?php endif; ?>

<?php if ($db->Count($result) > 0): ?>

    <?php while($row = $db->FetchObj ($result)): ?>

        <?php
        $video = new Video ($row->video_id);
        $rating = Rating::GetRating ($row->video_id);
        ?>

        <div class="block video">
            
            <a class="thumb" href="<?=HOST?>/videos/<?=$video->video_id?>/<?=$video->slug?>" title="<?=$video->title?>">
                <span class="duration"><?=$video->duration?></span>
                <span class="play-icon"></span>
                <img src="<?=$config->thumb_bucket_url?>/<?=$video->filename?>.jpg" alt="<?=$video->title?>" />
            </a>

            <a class="large" href="<?=HOST?>/videos/<?=$video->video_id?>/<?=$video->slug?>" title="<?=$video->title?>"><?=$video->title?></a>
            <p><?=Functions::CutOff ($video->description, 190)?></p>
            <span class="like">+<?=$rating->likes?></span>
            <span class="dislike">-<?=$rating->dislikes?></span>
            <div class="clear"></div>
            
            <div class="actions">
                <a href="<?=HOST?>/myaccount/editvideo/<?=$video->video_id?>/" title="<?=Language::GetText('edit_video')?>"><span><?=Language::GetText('edit_video')?></span></a>
                <a class="confirm" data-node="confirm_delete_video" href="<?=HOST?>/myaccount/myvideos/<?=$video->video_id?>/" title="<?=Language::GetText('delete_video')?>"><span><?=Language::GetText('delete_video')?></span></a>
            </div>


        </div>

    <?php endwhile; ?>

    <br clear="all" />
    <?=$pagination->Paginate()?>


<?php else: ?>		        
    <div class="block">
        <strong><?=Language::GetText('no_user_videos')?></strong></a>
    </div>
<?php endif; ?>