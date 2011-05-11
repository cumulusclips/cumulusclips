<!-- BEGIN SLIDESHOW CONTAINER -->
<div id="slideshow-container">

    <!-- SLIDESHOW PREVIOUS/NEXT BUTTONS -->
    <a href="" class="previous"><span></span></a>
    <a href="" class="next"><span></span></a>


    <!-- BEGIN SLIDESHOW -->
    <div id="slideshow">

        <!-- BEGIN SLIDE 1 -->
        <div>
            <img src="<?=THEME?>/images/slide1.jpg" />
            <div class="slide-text">
                Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                Duis a porta ante. Vivamus et lorem eget mi faucibus
                dapibus. Fusce et leo id dolor tincidunt euismod sed a
                tellus. Nullam viverra feugiat magna, in malesuada nibh
                accumsan id. Aenean ipsum justo, facilisis a condimentum
                sit amet, sagittis non tellus.
            </div>
        </div>
        <!-- END SLIDE 1 -->


        <!-- BEGIN SLIDE 2 -->
        <div>
            <img src="<?=THEME?>/images/slide2.jpg" />
            <div class="slide-text">
                Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                Duis a porta ante. Vivamus et lorem eget mi faucibus
                dapibus. Fusce et leo id dolor tincidunt euismod sed a
                tellus. Nullam viverra feugiat magna, in malesuada nibh
                accumsan id. Aenean ipsum justo, facilisis a condimentum
                sit amet, sagittis non tellus.
            </div>
        </div>
        <!-- END SLIDE 2 -->


        <!-- BEGIN SLIDE 3 -->
        <div>
            <img src="<?=THEME?>/images/slide3.jpg" />
            <div class="slide-text">
                Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                Duis a porta ante. Vivamus et lorem eget mi faucibus
                dapibus. Fusce et leo id dolor tincidunt euismod sed a
                tellus. Nullam viverra feugiat magna, in malesuada nibh
                accumsan id. Aenean ipsum justo, facilisis a condimentum
                sit amet, sagittis non tellus.
            </div>
        </div>
        <!-- END SLIDE 3 -->

    </div>
    <!-- END SLIDESHOW -->


    <!-- SLIDESHOW SLIDE LINKS -->
    <div id="slide-count"></div>

</div>
<!-- END SLIDESHOW CONTAINER -->



<div class="huge"><?=Language::GetText('home_header')?></div>

<?php while($row = $db->FetchObj ($result_recent)): ?>

    <?php $video = new Video ($row->video_id); ?>
    <?php $rating = Rating::GetRating ($video->video_id); ?>

    <div class="block">

        <a class="thumb" href="<?=HOST?>/videos/<?=$video->video_id?>/<?=$video->slug?>/" title="">
            <span class="duration"><?=$video->duration?></span>
            <span class="play-icon"></span>
            <img src="<?=$config->thumb_bucket_url?>/<?=$video->filename?>.jpg" alt="" />
        </a>

        <a class="large" href="<?=HOST?>/videos/<?=$video->video_id?>/<?=$video->slug?>/" title=""><?=$video->title?></a>
        <p><?=$video->description?></p>
        <span class="like">+<?=$rating->likes?></span>
        <span class="dislike">-<?=$rating->dislikes?></span>
        <br clear="all" />

    </div>

<?php endwhile; ?>


