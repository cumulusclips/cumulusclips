<?php
View::AddJs('jcycle.plugin.js');
View::AddJs('jcycle.js');
if (!$logged_in) View::AddSidebarBlock('home_login.tpl');
?>


<h1><?=Language::GetText('home_header')?></h1>

<!-- BEGIN SLIDESHOW -->
<div id="slideshow">
    
    <!--
    Slides 1-3 are for demonstration purposes only. They are merely for
    displaying your ability to add custom slides. You may remove them as you
    wish.
    -->
          
    <!-- BEGIN SLIDE 1 -->
    <div>
        <img width="600" height="287" src="<?=$config->theme_url?>/images/slide1.jpg" />
        <div class="slide_text">
            <div>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam at mauris et lorem porta imperdiet et sollicitudin diam. Maecenas leo elit, pretium a pellentesque quis, vehicula quis est. Aliquam sed orci at eros scelerisque venenatis.</p>
                <a class="button" href="">Watch Now</a>
            </div>
        </div>
    </div>
    <!-- END SLIDE 1 -->
          
    <!-- BEGIN SLIDE 2 -->
    <div>
        <img width="600" height="287" src="<?=$config->theme_url?>/images/slide2.jpg" />
        <div class="slide_text">
            <div>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam at mauris et lorem porta imperdiet et sollicitudin diam. Maecenas leo elit, pretium a pellentesque quis, vehicula quis est. Aliquam sed orci at eros scelerisque venenatis.</p>
                <a class="button" href="">Watch Now</a>
            </div>
        </div>
    </div>
    <!-- END SLIDE 2 -->
          
    <!-- BEGIN SLIDE 3 -->
    <div>
        <img width="600" height="287" src="<?=$config->theme_url?>/images/slide3.jpg" />
        <div class="slide_text">
            <div>
                <p>Angels and Christmas tree at the Rockefeller Centre, Manhattan -- Kay Francis/Photolibrary &copy; (Bing United States) Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam at mauris et lorem porta imperdiet et sollicitudin diam.</p>
                <a class="button" href="">Watch Now</a>
            </div>
        </div>
    </div>
    <!-- END SLIDE 3 -->
    
    <!-- BEGIN FEATURED VIDEO SLIDES -->
    <?php foreach ($featured_videos as $featuredVideo): ?>
        <div>
            <img src="<?=$config->thumb_url?>/<?=$featuredVideo->filename?>.jpg" />
            <div class="slide-text">
                <p class="large"><u><?=Language::GetText('featured')?>:</u> <?=$featuredVideo->title?></p>
                <p><?=Functions::CutOff ($featuredVideo->description, 80)?></p>
                <a class="button" href="<?=$featured->url?>/">Watch Now</a>
            </div>
        </div>
    <?php endforeach; ?>
    <!-- END FEATURED VIDEO SLIDES -->
   
</div>
<!-- END SLIDESHOW -->
                    
<!-- SLIDESHOW SLIDE LINKS -->
<div id="slideshow_nav"></div>
                
<!-- BEGIN RECENT VIDEOS -->
<div class="recent_videos">

    <h1>Recent Videos</h1>

    <div class="videos_list">
        <?php if (!empty ($recent_videos)): ?>
            <?php View::RepeatingBlock('video.tpl', $recent_videos); ?>
        <?php else: ?>
            <div class="block"><strong><?=Language::GetText('no_videos')?></strong></div>
        <?php endif; ?>
    </div>

</div>
<!-- END RECENT VIDEOS --> 