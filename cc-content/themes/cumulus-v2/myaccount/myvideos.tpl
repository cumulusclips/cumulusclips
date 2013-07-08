<?php

View::SetLayout ('myaccount');
View::Header();

?>

<h1><?=Language::GetText('myvideos_header')?></h1>
        
<?php if ($message): ?>
    <div class="message <?=$message_type?>"><?=$message?></div>
<?php endif; ?>

<?php if ($db->Count($result) > 0): ?>
    
    <div class="videos_list">
    <?php while($row = $db->FetchObj ($result)): ?>

        <?php $video = new Video ($row->video_id); ?>
        <div class="video">
            <div>
                <a href="<?=$video->url?>/" title="<?=$video->title?>">
                    <img width="165" height="92" src="<?=$config->thumb_url?>/<?=$video->filename?>.jpg" />
                </a>
                <span><?=$video->duration?></span>
            </div>
            <p><a href="<?=$video->url?>/" title="<?=$video->title?>"><?=$video->title?></a></p>
            <p class="actions small">
                <a href="<?=HOST?>/myaccount/editvideo/<?=$video->video_id?>/" title="<?=Language::GetText('edit_video')?>"><span><?=Language::GetText('edit_video')?></span></a>
                <a class="right confirm" data-node="confirm_delete_video" href="<?=HOST?>/myaccount/myvideos/<?=$video->video_id?>/" title="<?=Language::GetText('delete_video')?>"><span><?=Language::GetText('delete_video')?></span></a>
            </p>
        </div>

    <?php endwhile; ?>
    </div>

    <?=$pagination->Paginate()?>

<?php else: ?>		        
    <p><strong><?=Language::GetText('no_user_videos')?></strong></p>
<?php endif; ?>

<?php View::Footer(); ?>