<?php
$this->addMeta('videoId', $video->videoId);
$this->addMeta('theme', THEME);
$this->AddCss('jscrollpane.css');
$this->AddJs('jscrollpane.plugin.js');
$this->AddJs('mousewheel.plugin.js');
$this->SetLayout('full');
?>

<h1><?=$video->title?></h1>

<div class="left">

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
        <a href="" data-block="addToPlaylist" title="<?=Language::GetText('add')?>"><?=Language::GetText('add')?></a>
        <a href="" class="flag" data-type="video" data-id="<?=$video->videoId?>" title="<?=Language::GetText('flag')?>"><?=Language::GetText('flag')?></a>
    </div>
    <!-- END Action Buttons -->


    <div id="about" class="tab_block">
        <?php $avatar = $this->getService('User')->getAvatarUrl($member); ?>
        <img width="65" height="65" src="<?=($avatar) ? $avatar : THEME . '/images/avatar.gif'?>" alt="<?=$member->username?>" />
        <div>
            <a href="" class="button_small subscribe" title="<?=Language::GetText($subscribe_text)?>" data-type="<?=$subscribe_text?>" data-user="<?=$video->userId?>"><?=Language::GetText($subscribe_text)?></a>
            <p><strong><?=Language::GetText('by')?>:</strong> <a href="<?=HOST?>/members/<?=$member->username?>/" title="<?=$member->username?>"><?=$member->username?></a></p>
            <p><strong><?=Language::GetText('date_uploaded')?>:</strong> <?=date('m/d/Y', strtotime($video->dateCreated))?></p>
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

   
    <div id="addToPlaylist" class="tab_block" >
        <?php if ($loggedInUser): ?>
            <div>
                <p class="big"><?=Language::GetText('add_to')?></p>
                    <div>
                        <ul>
                            <?php $playlistService = $this->getService('Playlist'); ?>
                            <li><a data-playlist_id="<?=$favoritesList->playlistId?>" class="<?=($playlistService->checkListing($video, $favoritesList)) ? 'added' : ''?>" href=""><?=Language::GetText('favorites')?></a></li>
                            <li><a data-playlist_id="<?=$watchLaterList->playlistId?>" class="<?=($playlistService->checkListing($video, $watchLaterList)) ? 'added' : ''?>" href=""><?=Language::GetText('watch_later')?></a></li>
                            <?php if (count($userPlaylists) > 0): ?>
                                <li><strong><?=Language::GetText('playlists')?></strong></li>
                                <?php foreach ($userPlaylists as $userPlaylist): ?>
                                    <li><a data-playlist_id="<?=$userPlaylist->playlistId?>" class="<?=($playlistService->checkListing($video, $userPlaylist)) ? 'added' : ''?>" href=""><?=$userPlaylist->name?> (<?=count($userPlaylist->entries)?>)</a></li>
                                <?php endforeach; ?>
                            <?php endif; ?>
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
                            <option value="public"><?=Language::GetText('public')?></option>
                            <option value="private"><?=Language::GetText('private')?></option>
                        </select>
                        <input type="hidden" name="action" value="create" />
                        <input type="hidden" name="video_id" value="<?=$video->videoId?>" />
                        <input class="button" type="submit" value="<?=Language::GetText('create_playlist_button')?>" />
                    </form>
                </div>
            </div>
        <?php else: ?>
            <p class="big"><?=Language::GetText('add_to')?></p>
            <p><?=Language::GetText('playlist_login', array('host' => HOST))?></p>
        <?php endif; ?>
    </div>


    <!-- BEGIN COMMENTS SECTION -->
    <div id="comments">
        <p class="large"><?=Language::GetText('comments_header')?></p>
        <div class="totals">
            <p><span><?=$commentCount?></span> <?=Language::GetText('comments_total')?></p>
        </div>
        
        
        <!-- BEGIN COMMENTS FORM -->
        <div class="form collapsed commentForm">
            <form action="" method="post">
            <?php if (!$loggedInUser): ?>
                <label><?=Language::GetText('name')?></label>
                <input type="text" class="text" value="" name="name" />

                <label><?=Language::GetText('email')?></label>
                <input type="text" class="text" value="" name="email" />
            <?php endif; ?>
                
                <div class="commentContainer">
                    <label><?=Language::GetText('comments')?></label>
                    <textarea class="text" rows="4" cols="50" name="comments" title="<?=Language::GetText('comments')?>"><?=Language::GetText('comments')?></textarea>
                </div>
                
                <a class="cancel" href=""><?=Language::GetText('cancel')?></a>
                <input type="hidden" name="video_id" value="<?=$video->videoId?>" />
                <input type="hidden" name="block" value="comment" />
                <input type="hidden" name="submitted" value="TRUE" />
                <input type="hidden" name="parentCommentId" value="" />
                <input class="button" type="submit" name="button" value="<?=Language::GetText('comments_button')?>" />
            </form>
        </div>
        <!-- END COMMENTS FORM -->
        
        
        <!-- BEGIN COMMENTS LIST -->
        <div class="commentList">
            <?php if ($commentCount > 0): ?>
                <?php $commentService = $this->getService('Comment'); ?>
                <?php foreach ($commentList as $comment): ?>
                    <div class="comment" data-comment="<?=$comment->commentId?>">
                        <?php $avatar = $commentService->getCommentAvatar($comment); ?>
                        <img width="60" height="60" alt="<?=$comment->name?>" src="<?=($avatar) ? $avatar : THEME . '/images/avatar.gif'?>" />
                        <div>
                            <p>
                                <span class="commentAuthor"><?=getCommentAuthorText($comment)?></span>
                                <span class="commentDate"><?=date('m/d/Y', strtotime($comment->dateCreated))?></span>
                                <span class="commentAction">
                                    <a href=""><?=Language::GetText('reply')?></a>
                                    <a class="flag" data-type="comment" data-id="<?=$comment->commentId?>" href=""><?=Language::GetText('report_abuse')?></a>
                                </span>
                            </p>
                            <p><?=nl2br($comment->comments)?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <!-- END COMMENTS LIST -->
        
        <?php if ($commentCount > 5): ?>
            <div class="loadMoreComments">
                <a href="" class="button" data-loading_text="<?=Language::GetText('loading')?>"><?=Language::GetText('load_more')?></a>
            </div>
        <?php endif; ?>
        
    </div>
    <!-- END COMMENTS SECTION -->

</div>
<!-- END PLAY LEFT -->





<!-- BEGIN PLAY RIGHT -->
<div class="right">
    
    <?php if ($playlist): ?>
        <div id="playlistVideos">
            <header>
                <p class="big"><?=$this->getService('Playlist')->getPlaylistName($playlist)?></p>
                <?php $playlistAuthor = $this->getMapper('User')->getUserById($playlist->userId); ?>
                <p><strong><?=Language::GetText('by')?>:</strong> <a href="<?=HOST?>/members/<?=$playlistAuthor->username?>/"><?=$playlistAuthor->username?></a></p>
            </header>
            
            <div class="videos_list">
            <?php $videoService = $this->getService('Video'); ?>
            <?php foreach ($playlistVideos as $playlistVideo): ?>
                
                <div class="video video_small <?=($playlistVideo->videoId == $video->videoId) ? 'active' : ''?>">
                    <div class="thumbnail">
                        <a href="<?=$videoService->getUrl($playlistVideo)?>/?playlist=<?=$playlist->playlistId?>" title="<?=$playlistVideo->title?>">
                            <img width="100" height="56" src="<?=$config->thumb_url?>/<?=$playlistVideo->filename?>.jpg" />
                        </a>
                        <?php $playlistId = ($loggedInUser) ? $this->getService('Playlist')->getUserSpecialPlaylist($loggedInUser, 'watch_later')->playlistId : ''; ?>
                        <span class="watchLater"><a data-video="<?=$playlistVideo->videoId?>" data-playlist="<?=$playlistId?>" href="" title="<?=Language::GetText('watch_later')?>"><?=Language::GetText('watch_later')?></a></span>
                        <span class="duration"><?=$playlistVideo->duration?></span>
                    </div>
                    <div>
                        <p><a href="<?=$videoService->getUrl($playlistVideo)?>/?playlist=<?=$playlist->playlistId?>" title="<?=$playlistVideo->title?>"><?=$playlistVideo->title?></a></p>
                        <p><strong><?=Language::GetText('by')?>:</strong> <a href="<?=HOST?>/members/<?=$playlistVideo->username?>/" title="<?=$playlistVideo->username?>"><?=$playlistVideo->username?></a></p>
                        <p><strong><?=Language::GetText('views')?>:</strong> <?=$playlistVideo->views?></p>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
    
    <?php $this->Block ('ad300.tpl'); ?>

    
    <!-- BEGIN RELATED VIDEOS -->
    <div class="related_videos">
        <p class="large"><?=Language::GetText('suggestions_header')?></p>
        <?php if (count($relatedVideos) > 0): ?>

            <div class="video_list">
            <?php $videoService = $this->getService('Video'); ?>
            <?php foreach ($relatedVideos as $relatedVideo): ?>
                <div class="video video_medium">
                    <div class="thumbnail">
                        <a href="<?=$videoService->getUrl($relatedVideo)?>/" title="<?=$relatedVideo->title?>">
                            <img width="125" height="70" src="<?=$config->thumb_url?>/<?=$relatedVideo->filename?>.jpg" />
                        </a>
                        <?php $playlistId = ($loggedInUser) ? $this->getService('Playlist')->getUserSpecialPlaylist($loggedInUser, 'watch_later')->playlistId : ''; ?>
                        <span class="watchLater"><a data-video="<?=$relatedVideo->videoId?>" data-playlist="<?=$playlistId?>" href="" title="<?=Language::GetText('watch_later')?>"><?=Language::GetText('watch_later')?></a></span>
                        <span class="duration"><?=$relatedVideo->duration?></span>
                    </div>
                    <div>
                        <p><a href="<?=$videoService->getUrl($relatedVideo)?>/" title="<?=$relatedVideo->title?>"><?=$relatedVideo->title?></a></p>
                        <p><strong><?=Language::GetText('by')?>:</strong> <a href="<?=HOST?>/members/<?=$relatedVideo->username?>/" title="<?=$relatedVideo->username?>"><?=$relatedVideo->username?></a></p>
                        <p><strong><?=Language::GetText('views')?>:</strong> <?=$relatedVideo->views?></p>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>

        <?php else: ?>
                <strong><?=Language::GetText('no_suggestions')?></strong>
        <?php endif; ?>
    </div>
    <!-- END RELATED VIDEOS -->


</div>
<!-- END PLAY RIGHT -->
<br clear="all" />
