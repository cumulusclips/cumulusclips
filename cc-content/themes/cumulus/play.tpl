<?php

View::AddMeta('videoID', $video->video_id);
View::SetLayout('full');
View::Header();

?>


<div id="play-left">

    <h1><?=$video->title?></h1>

    <div id="message"></div>

    
    <?php if ($video->gated == '1' && !$logged_in): ?>

        <div id="player-gated">
            <img src="<?=$config->thumb_url?>/<?=$video->filename?>.jpg" alt="" />
            <div>
                <?=Language::GetText('gated_video')?><br />
                <a href="<?=HOST?>/login/" class="button-small"><?=Language::GetText('login')?></a>
                <a href="<?=HOST?>/register/" class="button-small"><?=Language::GetText('register')?></a>
            </div>
        </div>
    
    <?php else: ?>

        <!-- BEGIN VIDEO -->
        <div id="player"><?=$video->title?> - <?=Language::GetText('loading')?>...</div>
        <script type="text/javascript" src="<?=$config->theme_url?>/js/jwplayer.js"></script>
        <script type="text/javascript">
        jwplayer("player").setup({
            flashplayer : '<?=$config->theme_url?>/flash/player.swf',
            autostart   : true,
            file        : '<?=$config->flv_url?>/<?=$video->filename?>.flv',
            image       : '<?=$config->thumb_url?>/<?=$video->filename?>.jpg',
            controlbar  : 'bottom',
            width       : 600,
            height      : 450
        });
        </script>
        <!-- END VIDEO -->

    <?php endif; ?>



    <!-- BEGIN ACTIONS -->
    <div class="block view-play-actions">

        <div id="actions">
            <p class="large"><?=$video->views?></p>
            <p>
                <span class="like-text"><?=$rating->like_text?> (<?=$rating->likes?>+)</span> /
                <span class="dislike-text"><?=$rating->dislike_text?> (<?=$rating->dislikes?>-)</span>
            </p>
            <p id="rating-link">
                <a class="like rating" href="" data-rating="1" title="<?=Language::GetText('like')?>"><?=Language::GetText('like')?></a>
                <a class="dislike rating" href="" data-rating="0" title="<?=Language::GetText('dislike')?>"><?=Language::GetText('dislike')?></a>
            </p>
        </div>

        
        <!-- BEGIN Action Buttons -->
        <div id="buttons">
            <a href="" class="button-small showhide" data-block="about"><?=Language::GetText('about')?></a>
            <a href="" class="button-small subscribe" data-type="<?=$subscribe_text?>" data-user="<?=$video->user_id?>"><?=Language::GetText($subscribe_text)?></a>
            <a href="" class="button-small showhide" data-block="share"><?=Language::GetText('share')?></a>

            <?php if ($video->disable_embed == '0' && $video->gated == '0' && $video->private == '0'): ?>
                <a href="" class="button-small showhide" data-block="embed"><?=Language::GetText('embed')?></a>
            <?php endif; ?>

            <a href="" class="button-small favorite"><?=Language::GetText('favorite')?></a>
            <a href="" class="button-small flag" data-type="video" data-id="<?=$video->video_id?>"><?=Language::GetText('flag')?></a>
        </div>
        <!-- END Action Buttons -->


        <div id="about" class="showhide-block">
            <p class="avatar-small"><img src="<?=$member->avatar_url?>" alt="<?=$member->username?>" /></p>
            <p><strong><?=Language::GetText('by')?>:</strong> <a href="<?=HOST?>/members/<?=$member->username?>/" title="<?=$member->username?>"><?=$member->username?></a></p>
            <p><strong><?=Language::GetText('date_uploaded')?>:</strong> <?=Functions::DateFormat('m/d/Y',$video->date_created)?></p>
            <p><strong><?=Language::GetText('tags')?>:</strong>
                <?php foreach ($video->tags as $value): ?>
                    <a href="<?=HOST?>/search/?keyword=<?=$value?>" title="<?=$value?>"><?=$value?></a>&nbsp;&nbsp;
                <?php endforeach; ?>
            </p>
            <p class="clear"><strong><?=Language::GetText('description')?>:</strong> <?=$video->description?></p>
        </div>

        <?php if ($video->disable_embed == '0' && $video->gated == '0' && $video->private == '0'): ?>
            <div id="embed" class="showhide-block">
                <?=Language::GetText('embed_text')?>
                <textarea class="text" rows="5" cols="58">&lt;iframe src="<?=HOST?>/embed/<?=$video->video_id?>/" width="480" height="360" frameborder="0" allowfullscreen&gt;&lt;/iframe&gt;</textarea>
            </div>
        <?php endif; ?>

        <div id="share" class="showhide-block">
            <!-- FACEBOOK BUTTON -->
            <div class="share-button">
                <iframe src="http://www.facebook.com/plugins/like.php?href=<?=$video->url?>/&amp;layout=box_count&amp;show_faces=false&amp;width=50&amp;action=like&amp;font&amp;colorscheme=light&amp;height=65" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:50px; height:65px;" allowTransparency="true"></iframe>
            </div>

            <!-- TWITTER BUTTON -->
            <div class="share-button">
                <a href="http://twitter.com/share" class="twitter-share-button" data-count="vertical">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>
            </div>

            <!-- Google +1 BUTTON -->
            <div class="share-button">
                <script type="text/javascript" src="https://apis.google.com/js/plusone.js"></script>
                <g:plusone size="tall"></g:plusone>
            </div>

            <!-- DIGG BUTTON -->
            <div class="share-button">
                <script type="text/javascript">(function() {var s = document.createElement('SCRIPT'), s1 = document.getElementsByTagName('SCRIPT')[0];s.type = 'text/javascript';s.async = true;s.src = 'http://widgets.digg.com/buttons.js';s1.parentNode.insertBefore(s, s1);})();</script><a class="DiggThisButton DiggMedium"></a>
            </div>

            <!-- STUMBLEUPON BUTTON -->
            <div class="share-button">
                <script src="http://www.stumbleupon.com/hostedbadge.php?s=5"></script>
            </div>
        </div>

    </div>
    <!-- END ACTIONS -->





    <!-- BEGIN COMMENTS -->
    <div id="comments" class="view-play-comments">

        <?php if ($comment_count > 0): ?>

            <p class="large space"><?=Language::GetText('comments_header')?></p>

            <?php if ($comment_count >= 5): ?>
                <!-- BEGIN View All Comments Link -->
                <p class="post-header">
                    <a id="view-comments" href="<?=$comments_url?>/" title="<?=Language::GetText('view_all_comments')?>"><?=Language::GetText('view_all_comments')?></a>
                    &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;<strong><?=$comment_count?> <?=Language::GetText('comments_total')?></strong>
                </p>
                <!-- END View All Comments Link -->
            <?php endif; ?>

            <!-- BEGIN COMMENT BLOCKS -->
                <?php View::RepeatingBlock ('comment.tpl', $comment_list); ?>
            <!-- END COMMENT BLOCKS -->

        <?php endif; ?>

    </div>
    <!-- END COMMENTS -->





    <!-- BEGIN COMMENTS FORM -->
    <p class="large space"><?=Language::GetText('comments_post_header')?></p>
    <div class="block view-play-comments-form">

        <form id="comments-form" action="" method="post">

        <?php if (!$logged_in): ?>

            <div class="row">
                <label><?=Language::GetText('name')?></label>
                <input type="text" class="text" value="" name="name" />
            </div>

            <div class="row">
                <label><?=Language::GetText('email')?></label>
                <input type="text" class="text" value="" name="email" />
            </div>

            <div class="row">
                <label><?=Language::GetText('website')?> (<?=Language::GetText('optional')?>)</label>
                <input type="text" class="text" value="" name="website" />
            </div>

        <?php endif; ?>

        <div class="row">
            <label><?=Language::GetText('comments')?></label>
            <textarea class="text" rows="4" cols="50" name="comments"></textarea>
        </div>

        <div class="row-shift">
            <input type="hidden" name="video_id" value="<?=$video->video_id?>" />
            <input type="hidden" name="block" value="comment" />
            <input type="hidden" name="submitted" value="TRUE" />
            <input class="button" type="submit" name="button" value="<?=Language::GetText('comments_button')?>" />
        </div>
        </form>

    </div>
    <!-- END COMMENTS FORM -->

</div>
<!-- END PLAY LEFT -->





<!-- BEGIN PLAY RIGHT -->
<div id="play-right">


    <?php View::Block ('ad300.tpl'); ?>


    <!-- BEGIN RELATED VIDEOS -->
    <p class="large space"><?=Language::GetText('suggestions_header')?></p>
    <div class="block view-play-related-videos" id="related-videos">

        <?php if ($db->Count ($result_related) > 0): ?>

            <?php while ($row = $db->FetchObj ($result_related)): ?>

                <?php $related_video = new Video ($row->video_id); ?>

                <div class="other-video">
                    <p class="thumb">
                    <a class="video-thumb" href="<?=$related_video->url?>/" title="<?=$related_video->title?>">
                        <span class="play-button"></span>
                        <span class="duration"><?=$related_video->duration?></span>
                        <img src="<?=$config->thumb_url?>/<?=$related_video->filename?>.jpg" alt="<?=$related_video->title?>" />
                    </a>
                    </p>
                    <p><a href="<?=$related_video->url?>/" title="<?=$related_video->title?>"><?=$related_video->title?></a></p>
                    <p><strong><?=Language::GetText('by')?>:</strong> <a href="<?=HOST?>/members/<?=$related_video->username?>/" title="<?=$related_video->username?>"><?=$related_video->username?></a></p>
                    <p><strong><?=Language::GetText('views')?>:</strong> <?=$related_video->views?></p>
                </div>

            <?php endwhile; ?>

        <?php else: ?>
                <strong><?=Language::GetText('no_suggestions')?></strong>
        <?php endif; ?>

    </div>
    <!-- END RELATED VIDEOS -->


</div>
<!-- END PLAY RIGHT -->
<br clear="all" />

<?php View::Footer(); ?>