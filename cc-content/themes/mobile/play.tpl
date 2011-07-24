<?php View::Header(); ?>

<div id="play">

    <div class="block">
        
        <h1><?=$video->title?></h1>
        <video width="100%" controls="controls" poster="<?=$config->thumb_bucket_url?>/<?=$video->filename?>.jpg">
            <source src="<?=$config->mp4_bucket_url?>/<?=$video->filename?>.mp4" type="video/mp4" />
<!--            <source src="<?=HOST?>/Wildlife.mp4" type="video/mp4" />-->
            <?=Language::GetText('search_header')?>
        </video>


        <p><strong><?=Language::GetText('duration')?>: </strong><?=$video->duration?></p>
        <p><strong><?=Language::GetText('by')?>: </strong><?=$video->username?></p>
        <p><strong><?=Language::GetText('description')?>: </strong><?=$video->description?></p>
                
    </div>

</div>

<?php View::Footer(); ?>