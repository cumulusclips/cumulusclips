<div id="search">
    <div id="search-form">
        <form action="/search/" method="post">
        <input type="text" name="keyword" id="search-field" title="Search Videos..." value="" />
        <input type="hidden" name="submitted" value="TRUE" />
        <input id="search-button" type="image" value="Search Tech Videos" src="/images/search-button.png">
        </form>
    </div>
        
    <h1>Search <span>Videos</span></h1>



    
    <?php if ($Errors): ?>

        <div id="search-padding" class="rounded">
            <?=$Errors?>
        </div>

    <?php elseif ($Success): ?>	
				
        <p><strong>Results for: </strong><?=$keyword?></p>

        <?php if ($count < 1): ?>
            <div id="search-padding" class="rounded">
                <strong>No results found!</strong>
            </div>
        <?php else: ?>

            <div class="rounded">
                <div class="video-list">
                <?php while ($row = $db->FetchRow ($result)): ?>

                    <?php $video = new Video ($row[0], $db); ?>
                    <div class="video">
                        <img class="thumb" src="<?=$config->thumb_bucket_url?>/<?=$video->filename?>.jpg" height="56" width="75" />
                        <p class="title"><?=$video->title?></p>
                        <strong>Duration: </strong><?=$video->duration?>
                        <a href="/v/<?=$video->video_id?>/"></a>
                        <div class="clear"></div>
                    </div>

                <?php endwhile; ?>

                <?php if ($count > $config->max_list): ?>
                    <div id="load-more">
                        <form>
                        <input type="hidden" id="loadLocation" name="loadLocation" value="search" />
                        <input type="hidden" id="submitted" name="submitted" value="true" />
                        <input type="hidden" id="keyword" name="keyword" value="<?=$keyword?>" />
                        <input type="hidden" id="max" name="max" value="<?=$count?>" />
                        <input type="hidden" id="start" name="start" value="<?=$config->max_list?>" />
                        </form>
                        <div>
                            <span id="loading-text" style="display:none;">Loading...</span>
                            <span id="load-more-text">Load 20 More</span>
                        </div>
                    </div>
                <?php endif; ?>

                </div>
            </div>

        <?php endif; ?>
						
    <?php else: ?>

        <div id="search-padding" class="rounded">
            <p>When searching, use keywords that are likely to be
            included in the video's title, it's description or as its tags.</p>
            <p>&nbsp;</p>
            <p>For a greater number of results use short, general
            keywords; i.e. to find a videos on "replacing ram on a notebook"
            search "ram notebook". The opposite applies as well. If you
            want for a narrower result set then use longer keywords phrases.</p>
        </div>

    <?php endif; ?>

</div>