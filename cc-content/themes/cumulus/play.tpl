<script type="text/javascript">
    var video = {id:<?=$video->video_id?>,host:'<?=HOST?>',slug:'<?=$video->slug?>'};
</script>


    <div id="play-left">

        <!-- BEGIN VIDEO -->
        <h1><?php echo Functions::CutOff ($video->title, 70); ?></h1>
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
                <p id="rating-text"><strong><?=$rating->GetCountText()?></strong></p>
                <p id="rating-link">
                    <a class="like" href="" title="<?=Language::GetText('like')?>"><?=Language::GetText('like')?></a>
                    <a class="dislike" href="" title="<?=Language::GetText('dislike')?>"><?=Language::GetText('dislike')?></a>
                </p>
            </div>
            <div>
                <a href="" class="button-small"><span><?=Language::GetText('about')?></span></a>
                <a href="" class="button-small"><span><?=Language::GetText('subscribe')?></span></a>
                <a href="" class="button-small"><span><?=Language::GetText('share')?></span></a>
                <a href="" class="button-small"><span><?=Language::GetText('embed')?></span></a>
                <a href="" class="button-small"><span><?=Language::GetText('favorite')?></span></a>
                <a href="" class="button-small"><span><?=Language::GetText('flag')?></span></a>
            </div>

            <div id="about">
                <p>Tags: <?=$tags?></p>
                <p>Description: <?=$video->description?></p>
            </div>
            
        </div>
        <!-- END ACTIONS -->

<!--
&lt;object width=&quot;400&quot; height=&quot;300&quot; id=&quot;techievideos-player&quot; name=&quot;techievideos-player&quot; data=&quot;<?=HOST?>/p/&quot; type=&quot;application/x-shockwave-flash&quot;&gt;
&lt;param name=&quot;movie&quot; value=&quot;<?=HOST?>/p/&quot; /&gt;
&lt;param name=&quot;allowfullscreen&quot; value=&quot;true&quot; /&gt;&lt;param name=&quot;allowscriptaccess&quot; value=&quot;always&quot; /&gt;
&lt;param name=&quot;flashvars&quot; value=&quot;config=<?=HOST?>/p/c/<?=$video->filename?>/&quot; /&gt;
&lt;/object&gt;
-->





        <?php if ($comment_count > 0): ?>

            <!-- BEGIN COMMENTS -->
            <p class="large"><?=Language::GetText('comments_header')?></p>

            <?php if ($comment_count >= 5): ?>
                <!-- BEGIN View All Comments Link -->
                <p class="post-header">
                    <a id="view-comments" href="<?=HOST?>/comments/videos/<?=$video->video_id?>/" title="<?=Language::GetText('view_all_comments')?>"><?=Language::GetText('view_all_comments')?></a>
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
        <div id="comments-form" class="block">
            
            <form action="" method="post">

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

            <div class="row">
                <label><?=Language::GetText('comments')?></label>
                <textarea class="text" rows="4" cols="50" name="comments"></textarea>
            </div>

            <div class="row-shift">
                <input type="hidden" value="comment" name="action" />
                <input type="hidden" value="TRUE" name="submitted" />
                <a href="" class="button"><span><?=Language::GetText('comments_button')?></span></a>
            </div>
            </form>

        </div>
        <!-- END COMMENTS FORM -->


    </div>




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
    <br clear="all" />
