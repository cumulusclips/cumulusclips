<?php View::Header(); ?>

<h1><?=Language::GetText('search_header')?></h1>

<p><strong><?=Language::GetText('results_for')?>: '<em><?=$cleaned?></em>'</strong></p>

<?php if (!empty ($search_videos)): ?>
    <div class="videos_list">
    <?php foreach ($search_videos as $videoId): ?>
        <?php $video = new Video($videoId); ?>
        <div class="video">
            <div>
                <a href="<?=$video->url?>/" title="<?=$video->title?>">
                    <img width="165" height="92" src="<?=$config->thumb_url?>/<?=$video->filename?>.jpg" />
                </a>
                <span><?=$video->duration?></span>
            </div>
            <div>
                <p class="big"><a href="<?=$video->url?>/" title="<?=$video->title?>"><?=$video->title?></a></p>
                <p class="small">
                    <strong><?=Language::GetText('by')?>:</strong> <a href="<?=HOST?>/members/<?=$video->username?>/" title="<?=$video->username?>"><?=$video->username?></a>,
                    <strong><?=Language::GetText('views')?>:</strong> <?=$video->views?>
                </p>
                <p><?=Functions::CutOff($video->description, 300)?></p>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
    <?=$pagination->Paginate()?>
<?php else: ?>
    <p><strong><?=Language::GetText('no_results')?></strong></p>
<?php endif; ?>

<?php View::Footer(); ?>