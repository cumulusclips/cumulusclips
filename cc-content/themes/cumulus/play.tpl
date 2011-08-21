<?php

View::AddMeta('baseURL', HOST);
View::AddJs('flowplayer.plugin.js');
View::AddJs('play.js');
View::SetLayout('full');
View::Header();

?>

<script type="text/javascript">
    var video = {id:<?=$video->video_id?>,host:'<?=HOST?>',slug:'<?=$video->slug?>'};
</script>


    <div id="play-left">

        <!-- BEGIN VIDEO -->
        <h1><?php echo Functions::CutOff ($video->title, 70); ?></h1>

        <div id="message"></div>
        
        <div id="player">
            <a
                href="<?php echo $config->flv_bucket_url; ?>/<?php echo $video->filename; ?>.flv"
                style="display:block;width:600px;height:400px;"
                id="video">
            </a>
        </div>
        <!-- END VIDEO -->



        <!-- BEGIN ACTIONS -->
        <div class="block">

            <div id="actions">
                <p class="large"><?=$video->views?></p>
                <p><span id="rating-text">
                    <span class="green-text"><?=$rating->like_text?> (<?=$rating->likes?>+)</span> / 
                    <span class="red-text"><?=$rating->dislike_text?> (<?=$rating->dislikes?>-)</span>
                </span></p>
                <p id="rating-link">
                    <a class="like rating" href="" data-video="<?=$video->video_id?>" data-rating="1" title="<?=Language::GetText('like')?>"><?=Language::GetText('like')?></a>
                    <a class="dislike rating" href="" data-video="<?=$video->video_id?>" data-rating="0" title="<?=Language::GetText('dislike')?>"><?=Language::GetText('dislike')?></a>
                </p>
            </div>

            <div>
                <a href="" class="button-small"><?=Language::GetText('about')?></a>
                <a href="" class="button-small subscribe" data-type="<?=$subscribe_text?>" data-member="<?=$video->user_id?>"><?=Language::GetText($subscribe_text)?></a>
                <a href="" class="button-small"><?=Language::GetText('share')?></a>
                <a href="" class="button-small"><?=Language::GetText('embed')?></a>
                <a href="" class="button-small favorite" data-video="<?=$video->video_id?>"><?=Language::GetText('favorite')?></a>
                <a href="" class="button-small flag" data-type="video" data-id="<?=$video->video_id?>"><?=Language::GetText('flag')?></a>
            </div>

            <div id="about">
                <p>
                    <img src="<?=$member->avatar?>" alt="<?=$member->username?>" />
                    <a class="big" href="<?=HOST?>/members/<?=$member->username?>/" title="<?=$member->username?>"><?=$member->username?></a>
                </p>
                <p>Date Uploaded: <?=$video->date_created?></p>
                <p>Tags: <?=$tags?></p>
                <p>Description: <?=$video->description?></p>
            </div>

            <div id="embed">
                <textarea class="text">
                    &lt;object width=&quot;400&quot; height=&quot;300&quot; id=&quot;cc-player&quot; name=&quot;cc-player&quot; data=&quot;<?=HOST?>/p/&quot; type=&quot;application/x-shockwave-flash&quot;&gt;
                    &lt;param name=&quot;movie&quot; value=&quot;<?=HOST?>/p/&quot; /&gt;
                    &lt;param name=&quot;allowfullscreen&quot; value=&quot;true&quot; /&gt;&lt;param name=&quot;allowscriptaccess&quot; value=&quot;always&quot; /&gt;
                    &lt;param name=&quot;flashvars&quot; value=&quot;config=<?=HOST?>/p/c/<?=$video->filename?>/&quot; /&gt;
                    &lt;/object&gt;
                </textarea>
            </div>

            <div id="share">
                <!-- FACEBOOK BUTTON -->
                <div class="share-button">
                    <iframe src="http://www.facebook.com/plugins/like.php?href=<?=HOST?>/videos/<?=$video->video_id?>/<?=$video->slug?>/&amp;layout=box_count&amp;show_faces=false&amp;width=50&amp;action=like&amp;font&amp;colorscheme=light&amp;height=65" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:50px; height:65px;" allowTransparency="true"></iframe>
                </div>

                <!-- TWITTER BUTTON -->
                <div class="share-button">
                    <a href="http://twitter.com/share" class="twitter-share-button" data-count="vertical">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>
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





        <?php if ($comment_count > 0): ?>

            <!-- BEGIN COMMENTS -->
            <p class="large"><?=Language::GetText('comments_header')?></p>

            <?php if ($comment_count >= 5): ?>
                <!-- BEGIN View All Comments Link -->
                <p class="post-header">
                    <a id="view-comments" href="<?=HOST?>/videos/<?=$video->video_id?>/comments/" title="<?=Language::GetText('view_all_comments')?>"><?=Language::GetText('view_all_comments')?></a>
                    &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;<strong><?=$comment_count?> <?=Language::GetText('comments_total')?></strong>
                </p>
                <!-- END View All Comments Link -->
            <?php endif; ?>


            <!-- BEGIN COMMENT BLOCKS -->
            <div id="comments">
                <?php View::RepeatingBlock ('comment.tpl', $comment_list); ?>
            </div>
            <!-- END COMMENT BLOCKS -->
            
            <!-- END COMMENTS -->
        <?php else: ?>
            <div id="comments"></div>
        <?php endif; ?>





        <!-- START COMMENTS FORM -->
        <p class="large"><?=Language::GetText('comments_post_header')?></p>
        <div class="block">
            
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
                <input type="hidden"name="submitted" value="TRUE" />
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
        <p class="large"><?=Language::GetText('suggestions_header')?></p>
        <div class="block" id="related-videos">
        
            <?php if ($db->Count ($result_related) > 0): ?>
        	
                <?php while ($row = $db->FetchObj ($result_related)): ?>
        		
                    <?php $related_video = new Video ($row->video_id); ?>
        			
                    <div class="other-video">
                        <p class="thumb">
                        <a class="video-thumb" href="<?=HOST?>/videos/<?=$related_video->video_id?>/<?=$related_video->slug?>/" title="<?=$related_video->title?>">
                            <span class="play-button"></span>
                            <span class="duration"><?=$related_video->duration?></span>
                            <img src="<?=$config->thumb_bucket_url?>/<?=$related_video->filename?>.jpg" alt="<?=$related_video->title?>" />
                        </a>
                        </p>
                        <p><a href="<?=HOST?>/videos/<?=$related_video->video_id?>/<?=$related_video->slug?>/" title="<?=$related_video->title?>"><?=$related_video->title?></a></p>
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