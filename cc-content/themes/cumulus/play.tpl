<script type="text/javascript">
    var video = {id:<?=$video->video_id?>,host:'<?=HOST?>',slug:'<?=$video->slug?>'};
</script>


    <div id="play-left">

        <!-- BEGIN VIDEO -->
        <h1><?php echo Functions::CutOff ($video->title, 70); ?></h1>
        <div id="now-playing-block">
            <div id="player">
                <a
                    href="<?php echo $config->flv_bucket_url; ?>/<?php echo $video->filename; ?>.flv"
                    style="display:block;width:600px;height:400px;"
                    id="video">
                </a>
            </div>
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
            
        </div>
        <!-- END ACTIONS -->





        <?php if ($count[0] > 0): ?>

        <!-- BEGIN COMMENTS -->
        <p class="large"><?=Language::GetText('comments_header')?></p>

        <!-- BEGIN View All Comments Link -->
        <?php if ($count[0] > 5): ?>
            <p class="post-header">
                <a id="view-comments" href="<?=HOST?>/comments/videos/<?=$video->video_id?>/" title="<?=Language::GetText('view_all_comments')?>"><?=Language::GetText('view_all_comments')?></a>
                &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;<strong><?=$count[0]?> <?=Language::GetText('comments_total')?></strong>
            </p>
        <?php endif; ?>
        <!-- END View All Comments Link -->

            

        <!-- BEGIN COMMENT BLOCKS -->
        <?php while ($row = $db->FetchRow ($result_comments)): ?>

            <?php
            $comment = new Comment ($row[0]);
            $comment_user = new User ($comment->user_id);
            ?>

            <div class="block comment">
                <div class="video-comment">
                    <p class="thumb">
                        <a href="<?=HOST?>'/members/<?=$comment_user->username?>/" title="<?=$comment_user->username?>">
                            <img src="<?=$comment_user->avatar?>" width="55" height="55" alt="<?=$comment_user->username?>" />
                        </a>
                    </p>
                    <p><?=Language::GetText('posted_by')?>: <a href="<?=HOST?>/members/<?=$comment_user->username?>/" title="<?=$comment_user->username?>"><?=$comment_user->username?></a></p>
                    <p><?=Language::GetText('date_posted')?>: <?=$comment->date_created?></p>
                    <p><a href="" id="comment-<?=$comment->comment_id?>" class="flag-comment" title="<?=Language::GetText('report_abuse')?>"><?=Language::GetText('report_abuse')?></a></p>
                    <br clear="all" />
                </div>
                <p><?=$comment->comments?></p>
            </div>

        <?php endwhile; ?>
        <!-- END COMMENT BLOCKS -->

        <?php endif; ?>



<!--
&lt;object width=&quot;400&quot; height=&quot;300&quot; id=&quot;techievideos-player&quot; name=&quot;techievideos-player&quot; data=&quot;<?=HOST?>/p/&quot; type=&quot;application/x-shockwave-flash&quot;&gt;
&lt;param name=&quot;movie&quot; value=&quot;<?=HOST?>/p/&quot; /&gt;
&lt;param name=&quot;allowfullscreen&quot; value=&quot;true&quot; /&gt;&lt;param name=&quot;allowscriptaccess&quot; value=&quot;always&quot; /&gt;
&lt;param name=&quot;flashvars&quot; value=&quot;config=<?=HOST?>/p/c/<?=$video->filename?>/&quot; /&gt;
&lt;/object&gt;
-->





        <!-- START COMMENTS FORM -->
        <p class="large"><?=Language::GetText('comments_post_header')?></p>
        <div class="block">
            
            <form action="" method="post" id="video-comments-form">

            <div class="row">
                <input type="text" class="text defaultText" title="<?=Language::GetText('name')?>" value="<?=Language::GetText('name')?>" />
            </div>

            <div class="row">
                <input type="text" class="text defaultText" title="<?=Language::GetText('email')?>" value="<?=Language::GetText('email')?>" />
            </div>

            <div class="row">
                <input type="text" class="text defaultText" title="<?=Language::GetText('website')?> (<?=Language::GetText('optional')?>)" value="<?=Language::GetText('website')?> (<?=Language::GetText('optional')?>)" />
            </div>

            <div class="row">
                <textarea class="text defaultText" rows="4" cols="50" title="<?=Language::GetText('comments')?>" name="comments"><?=Language::GetText('comments')?></textarea>
            </div>

            <div class="row_btn">
                <input type="hidden" value="TRUE" name="submitted" />
                <input type="hidden" value="comment" name="action" />
                <input type="submit" value="button" name="submit" />
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
