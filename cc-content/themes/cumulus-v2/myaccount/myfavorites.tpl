<?php $this->SetLayout ('myaccount'); ?>

<h1><?=Language::GetText('myfavorites_header')?></h1>
        
<?php if ($message): ?>
    <div class="message <?=$message_type?>"><?=$message?></div>
<?php endif; ?>

<?php if ($db->Count($result) > 0): ?>

    <div class="videos_list">
    <?php while ($row = $db->FetchObj ($result)): ?>

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
                <a class="confirm" data-node="confirm_remove_favorite" href="<?=HOST?>/myaccount/myfavorites/<?=$video->video_id?>/" title="<?=Language::GetText('remove_favorite')?>s"><span><?=Language::GetText('remove_favorite')?></span></a>
            </p>
        </div>

    <?php endwhile; ?>
    </div>

    <?=$pagination->Paginate()?>

<?php else: ?>
    <p><strong><?=Language::GetText('no_favorites')?></strong></p>
<?php endif; ?>