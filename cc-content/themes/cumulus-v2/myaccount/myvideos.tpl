<?php View::setLayout('myaccount'); ?>

<h1><?=Language::getText('myvideos_header')?></h1>
        
<?php if ($message): ?>
    <div class="message <?=$message_type?>"><?=$message?></div>
<?php endif; ?>

<?php if (count($userVideos) > 0): ?>
    
    <div class="videos_list">
    <?php foreach($userVideos as $video): ?>

        <div class="video">
            <?php if (in_array($video->status, array('pendingConversion', 'processing', 'pendingApproval'))): ?>
                <div><img width="165" height="92" src="<?=$config->theme_url?>/images/video_construction.png" /></div>
                <p><strong><?=Language::getText($video->status)?></strong> - <?=$video->title?></p>
            <?php else: ?>
                <div>
                    <a href="<?=View::getService('Video')->getUrl($video)?>/" title="<?=$video->title?>">
                        <img width="165" height="92" src="<?=$config->thumb_url?>/<?=$video->filename?>.jpg" />
                    </a>
                    <span><?=$video->duration?></span>
                </div>
                <p><a href="<?=View::getService('Video')->getUrl($video)?>/" title="<?=$video->title?>"><?=$video->title?></a></p>
            <?php endif; ?>
            <p class="actions small">
                <a href="<?=HOST?>/myaccount/editvideo/<?=$video->videoId?>/" title="<?=Language::getText('edit_video')?>"><span><?=Language::getText('edit_video')?></span></a>
                <a class="right confirm" data-node="confirm_delete_video" href="<?=HOST?>/myaccount/myvideos/<?=$video->videoId?>/" title="<?=Language::getText('delete_video')?>"><span><?=Language::getText('delete_video')?></span></a>
            </p>
        </div>

    <?php endforeach; ?>
    </div>

    <?=$pagination->paginate()?>

<?php else: ?>		        
    <p><strong><?=Language::getText('no_user_videos')?></strong></p>
<?php endif; ?>