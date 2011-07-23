<div id="play">
    <div class="rounded">
        
    <?php if ($display): ?>
        <h1><?=$video->title?></h1>
        <a href="<?=$config->mp4_bucket_url?>/<?=$video->filename?>.mp4"><span></span><img src="<?=$config->thumb_bucket_url?>/<?=$video->filename?>.jpg" /></a>

        <p><strong>Duration: </strong><?=$video->duration?></p>
        <p><strong>By: </strong><?=$video->username?></p>
        <br />
        <p><strong>Description: </strong><?=$video->description?></p>

    <?php else: ?>
        <p><strong>Video Not Found!</strong></p>
    <?php endif; ?>
                
    </div>
</div>