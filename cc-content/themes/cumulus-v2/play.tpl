<?php
$this->AddMeta('videoId', $video->videoId);
$this->AddCss('jscrollpane.css');
$this->AddJs('jscrollpane.plugin.js');
$this->AddJs('mousewheel.plugin.js');
$this->SetLayout('full');
?>


<div class="left">

    <h1><?=$video->title?></h1>
    <div class="message"></div>
    <?php if ($video->gated && !$loggedInUser): ?>
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
        <div id="player">
            <video width="600" height="337" controls="controls" poster="<?=$config->thumb_url?>/<?=$video->filename?>.jpg"<!--autoplay="autoplay">-->>
                <source src="<?=$config->h264Url?>/<?=$video->filename?>.mp4" type="video/mp4" />
                <source src="<?=$config->theoraUrl?>/<?=$video->filename?>.ogv" type="video/ogg" />
                <?php if ($vp8Options->enabled): ?>
                    <source src="<?=$config->vp8Url?>/<?=$video->filename?>.webm" type="video/webm" />
                <?php endif; ?>
            </video>
        </div>
        <!-- END VIDEO -->
    <?php endif; ?>



    <!-- BEGIN ACTIONS -->
    <div class="actions">
        <div class="left">
            <p class="large"><?=$video->views?></p>
            <p>
                <span class="like"><?=$rating->likes?></span>
                <span class="dislike"><?=$rating->dislikes?></span>
            </p>
        </div>
        <div class="right">
            <a class="like rating" href="" data-rating="1" title="<?=Language::GetText('like')?>"><?=Language::GetText('like')?></a>
            <a class="dislike rating" href="" data-rating="0" title="<?=Language::GetText('dislike')?>"><?=Language::GetText('dislike')?></a>
        </div>
    </div>
    <!-- END ACTIONS -->


    <!-- BEGIN Action Buttons -->
    <div class="tabs">
        <a href="" data-block="about" title="<?=Language::GetText('about')?>"><?=Language::GetText('about')?></a>
        <a href="" data-block="share" title="<?=Language::GetText('share')?>"><?=Language::GetText('share')?></a>
        <a href="" data-block="add" title="<?=Language::GetText('add')?>"><?=Language::GetText('add')?></a>
        <a href="" class="flag" data-type="video" data-id="<?=$video->videoId?>" title="<?=Language::GetText('flag')?>"><?=Language::GetText('flag')?></a>
    </div>
    <!-- END Action Buttons -->


    <div id="about" class="tab_block">
        <img width="65" height="65" src="<?=UserService::getAvatarUrl($member)?>" alt="<?=$member->username?>" />
        <div>
            <a href="" class="button_small subscribe" title="<?=Language::GetText($subscribe_text)?>" data-type="<?=$subscribe_text?>" data-user="<?=$video->userId?>"><?=Language::GetText($subscribe_text)?></a>
            <p><strong><?=Language::GetText('by')?>:</strong> <a href="<?=HOST?>/members/<?=$member->username?>/" title="<?=$member->username?>"><?=$member->username?></a></p>
            <p><strong><?=Language::GetText('date_uploaded')?>:</strong> <?=Functions::DateFormat('m/d/Y',$video->dateCreated)?></p>
            <p><strong><?=Language::GetText('tags')?>:</strong>
                <?php foreach ($video->tags as $value): ?>
                    <a href="<?=HOST?>/search/?keyword=<?=$value?>" title="<?=$value?>"><?=$value?></a>&nbsp;&nbsp;
                <?php endforeach; ?>
            </p>
        </div>
        <p><?=$video->description?></p>
    </div>


    <div id="share" class="tab_block">
        <div>
            <p class="big"><?=Language::GetText('share')?></p>
            <!-- FACEBOOK BUTTON -->
            <a class="facebook" href="" onClick="window.open('https://www.facebook.com/dialog/feed?app_id=573507612661821&display=popup&link=https://developers.facebook.com/docs/reference/dialogs/&picture=http://demo.cumulusclips.org/cc-content/uploads/thumbs/lIIKcKxnri31fJ6EoeLA.jpg&name=<?=urlencode($video->title)?>&caption=<?=urlencode($config->sitename)?>&description=<?=urlencode($video->description)?>&redirect_uri=http://demo.cumulusclips.org/videos/110/the-avengers/','mywindow','width=550,height=300');return false;">Share on Facebook</a>

            <!-- TWITTER BUTTON -->
            <a class="twitter" href="" onClick="window.open('https://twitter.com/share?url=http://demo.cumulusclips.org/videos/110/the-avengers/&text=<?=urlencode(Functions::CutOff($video->description, 140))?>','mywindow','width=650,height=400');return false;">Share on Twitter</a>

            <!-- Google +1 BUTTON -->
            <meta itemprop="name" content="<?=$video->title?>">
            <meta itemprop="description" content="<?=$video->description?>">
            <meta itemprop="image" content="<?=$config->thumb_url?>/<?=$video->filename?>.jpg">
            <a class="google" href="" onClick="window.open('https://plus.google.com/share?url=http://demo.cumulusclips.org/test.php','mywindow','width=600,height=600');return false;">Share on Google+</a>
        </div>
        
        <?php if ($video->disableEmbed == '0' && $video->gated == '0' && $video->private == '0'): ?>
            <!-- EMBED CODE -->
            <div>
                <p class="big"><?=Language::GetText('embed')?></p>
                <p><?=Language::GetText('embed_text')?></p>
                <textarea class="text" rows="5" cols="58">&lt;iframe src="<?=HOST?>/embed/<?=$video->videoId?>/" width="480" height="360" frameborder="0" allowfullscreen&gt;&lt;/iframe&gt;</textarea>
            </div>
        <?php endif; ?>
    </div>

   
    <div id="add" class="tab_block" >
        <div>
            <p class="big"><?=Language::GetText('add_to')?></p>
            <div>
                <ul>
                    <li><a href=""><?=Language::GetText('favorites')?></a></li>
                    <li><a class="added" href=""><?=Language::GetText('watch_later')?></a></li>
                    <li><strong><?=Language::GetText('playlists')?></strong></li>
                    <li><a href="">Free Videos (3)</a></li>
                    <li><a href="">Instructional Videos (17)</a></li>
                    <li><a href="">Paid Membership Videos (21)</a></li>
                </ul>
            </div>
        </div>
        <div>
            <p class="big"><?=Language::GetText('create_new_playlist')?></p>
            <div class="form">
                <form>
                    <label><?=Language::GetText('playlist_name')?></label>
                    <input type="text" name="playlist_name" />
                    <label><?=Language::GetText('visibility')?></label>
                    <select name="playlist_visibility">
                        <option><?=Language::GetText('public')?></option>
                        <option><?=Language::GetText('private')?></option>
                    </select>
                    <input class="button" type="button" value="<?=Language::GetText('create_playlist_button')?>" />
                </form>
            </div>
        </div>
    </div>


    <!-- BEGIN COMMENTS SECTION -->
    <div id="comments">
        <p class="large"><?=Language::GetText('comments_header')?></p>
        <div class="totals">
            <p><?=$comment_count?> <?=Language::GetText('comments_total')?></p>
            <p class="pagination">
                <span>1</span>
                <a href="">2</a>
                <a href="">3</a>
                <a href="">4</a>
                <a href="">5</a>
                <a href="">6</a>
                <a href="">Next &raquo;</a>
            </p>
        </div>
        
        
        <!-- BEGIN COMMENTS FORM -->
        <div class="form collapsed comments_form">
            <form action="" method="post">
            <?php if (!$loggedInUser): ?>
                <label><?=Language::GetText('name')?></label>
                <input type="text" class="text" value="" name="name" />

                <label><?=Language::GetText('email')?></label>
                <input type="text" class="text" value="" name="email" />
            <?php endif; ?>

                <label><?=Language::GetText('comments')?></label>
                <textarea class="text" rows="4" cols="50" name="comments">Comments</textarea>

                <input type="hidden" name="video_id" value="<?=$video->videoId?>" />
                <input type="hidden" name="block" value="comment" />
                <input type="hidden" name="submitted" value="TRUE" />
                <input class="button" type="submit" name="button" value="<?=Language::GetText('comments_button')?>" />
            </form>
        </div>
        <!-- END COMMENTS FORM -->
        
        
        <!-- BEGIN COMMENTS LIST -->
        <div class="comments_list">
<!--            
            <div>
                <img src="<?=$config->theme_url?>/images/avatar.gif" width="60" height="60" />
                <div>
                    <p>
                        <span><a href="">Mariano Duncan</a> 12/15/2012</span>
                        <span>
                            <a href=""><?=Language::GetText('reply')?></a>
                            <a href=""><?=Language::GetText('report_abuse')?></a>
                        </span>
                    </p>
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras et sapien vel purus molestie sagittis et ut sem. Sed consectetur, nunc a malesuada auctor, eros dolor rutrum augue, in ornare nisi augue eget arcu. Curabitur risus enim, fermentum ac aliquam vitae, gravida in ante.</p>
                </div>
            </div>
            
            <div class="child">
                <img src="<?=$config->theme_url?>/images/avatar.gif" width="60" height="60" />
                <div>
                    <p>
                        <span><a href="">Mariano Duncan</a> 12/15/2012</span>
                        <span><?=Language::GetText('reply_to')?> <a href="">Mariano Duncan</a></span>
                        <span>
                            <a href=""><?=Language::GetText('reply')?></a>
                            <a href=""><?=Language::GetText('report_abuse')?></a>
                        </span>
                    </p>
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras et sapien vel purus molestie sagittis et ut sem. Sed consectetur, nunc a malesuada auctor, eros dolor rutrum augue, in ornare nisi augue eget arcu. Curabitur risus enim, fermentum ac aliquam vitae, gravida in ante.</p>
                </div>
            </div>
            
            <div class="child">
                <img src="<?=$config->theme_url?>/images/avatar.gif" width="60" height="60" />
                <div>
                    <p>
                        <span><a href="">Mariano Duncan</a> 12/15/2012</span>
                        <span><?=Language::GetText('reply_to')?> <a href="">Mariano Duncan</a></span>
                        <span>
                            <a href=""><?=Language::GetText('reply')?></a>
                            <a href=""><?=Language::GetText('report_abuse')?></a>
                        </span>
                    </p>
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras et sapien vel purus molestie sagittis et ut sem. Sed consectetur, nunc a malesuada auctor, eros dolor rutrum augue, in ornare nisi augue eget arcu. Curabitur risus enim, fermentum ac aliquam vitae, gravida in ante.</p>
                </div>
            </div>
            
            <div class="grandchild">
                <img src="<?=$config->theme_url?>/images/avatar.gif" width="60" height="60" />
                <div>
                    <p>
                        <span><a href="">Mariano Duncan</a> 12/15/2012</span>
                        <span><?=Language::GetText('reply_to')?> <a href="">Mariano Duncan</a></span>
                        <span>
                            <a href=""><?=Language::GetText('reply')?></a>
                            <a href=""><?=Language::GetText('report_abuse')?></a>
                        </span>
                    </p>
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras et sapien vel purus molestie sagittis et ut sem. Sed consectetur, nunc a malesuada auctor, eros dolor rutrum augue, in ornare nisi augue eget arcu. Curabitur risus enim, fermentum ac aliquam vitae, gravida in ante.</p>
                </div>
            </div>
            
            <div>
                <img src="<?=$config->theme_url?>/images/avatar.gif" width="60" height="60" />
                <div>
                    <p>
                        <span><a href="">Mariano Duncan</a> 12/15/2012</span>
                        <span>
                            <a href=""><?=Language::GetText('reply')?></a>
                            <a href=""><?=Language::GetText('report_abuse')?></a>
                        </span>
                    </p>
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras et sapien vel purus molestie sagittis et ut sem. Sed consectetur, nunc a malesuada auctor, eros dolor rutrum augue, in ornare nisi augue eget arcu. Curabitur risus enim, fermentum ac aliquam vitae, gravida in ante.</p>
                </div>
            </div>-->
            
        <?php if ($comment_count > 0): ?>
            <!-- BEGIN COMMENT BLOCKS -->
            <?php $this->RepeatingBlock ('comment.tpl', $comment_list); ?>
            <!-- END COMMENT BLOCKS -->
        <?php endif; ?>
        </div>
        <!-- END COMMENTS LIST -->
        
    </div>
    <!-- END COMMENTS SECTION -->

</div>
<!-- END PLAY LEFT -->





<!-- BEGIN PLAY RIGHT -->
<div class="right">
    
    <?php $this->Block ('ad300.tpl'); ?>

    
    <!-- BEGIN RELATED VIDEOS -->
    <div class="related_videos">
        <p class="large"><?=Language::GetText('suggestions_header')?></p>
        <?php if (count($result_related) > 0): ?>

            <div class="video_list">
            <?php while ($row = $db->FetchObj ($result_related)): ?>
                <?php $related_video = new Video ($row->video_id); ?>
                <div class="video_medium">
                    <div>
                        <a href="<?=$related_video->url?>/" title="<?=$related_video->title?>">
                            <img width="125" height="70" src="<?=$config->thumb_url?>/<?=$related_video->filename?>.jpg" />
                        </a>
                        <span><?=$related_video->duration?></span>
                    </div>
                    <div>
                        <p><a href="<?=$related_video->url?>/" title="<?=$related_video->title?>"><?=$related_video->title?></a></p>
                        <p><strong><?=Language::GetText('by')?>:</strong> <a href="<?=HOST?>/members/<?=$related_video->username?>/" title="<?=$related_video->username?>"><?=$related_video->username?></a></p>
                        <p><strong><?=Language::GetText('views')?>:</strong> <?=$related_video->views?></p>
                    </div>
                </div>
            <?php endwhile; ?>
            </div>

        <?php else: ?>
                <strong><?=Language::GetText('no_suggestions')?></strong>
        <?php endif; ?>
    </div>
    <!-- END RELATED VIDEOS -->


</div>
<!-- END PLAY RIGHT -->
<br clear="all" />