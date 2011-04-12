<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="keywords" content="Techie, Videos, Techie Videos, Tech Videos, How-to videos, Screencasts, Tutorials, Do-it-yourself videos" />
<meta name="description" content="How-to and instructional videos on tech related tasks as well as screencasts, tutorials, and news for techies and tech enthusiasts" />
<title><?php echo $page_title; ?></title>
<link rel="shortcut icon" href="/images/favicon.ico" type="image/x-icon" />
<link href="/css/main.css" rel="stylesheet" type="text/css" />
<link href="/css/pages.css" rel="stylesheet" type="text/css" />
<!--[if lte IE 6]><link href="/css/ie6.css" rel="stylesheet" type="text/css" /><![endif]-->
<link rel="alternate" type="application/rss+xml" title="Recently Added Videos" href="http://feeds.feedburner.com/TechieVideos" />
<script type="text/javascript" src="/js/functions.js"></script>
</head>
<body>
<?php include (THEMES . '/blocks/header.tpl'); ?>

    <div id="left">

        <div class="short-block-header" id="video-categories-header"><h1>Video Categories</h1></div>
        <div class="short-block">
            <p><a href="<?php echo HOST; ?>/videos/recent/" title="Most Recent Videos">Most Recent</a></p>
            <p><a href="<?php echo HOST; ?>/videos/most-viewed/" title="Most Viewed Videos">Most Views</a></p>
            <p><a href="<?php echo HOST; ?>/videos/most-discussed/" title="Most Discussed Videos">Most Discussed</a></p>
            <br /><br />
            <ul id="cat-list">

                <?php while ($cat = $db->FetchAssoc ($result_cats)): ?>
                    <?php $dashed = str_replace (' ','-',$cat['cat_name']); ?>
                    <li><a href="<?=HOST?>/videos/<?=$dashed?>/" title="<?=$cat['cat_name']?>"><?=$cat['cat_name']?></a></li>
                <?php endwhile; ?>

            </ul>
        </div>

        <p id="ad-title">Advertisement</p>
        <div id="side-ad">
            <?php include ($sidebar_ad_250); ?>
        </div>

    </div>
    
    
    
    
    <div id="right">
    
        <div class="block-header" id="browse-videos-header">
            <h1>Browse Tech Videos</h1><h2>Viewing: <?php echo ($category)?$category:'All'; ?> Videos</h2>
        </div>
		
        <?php if ($db->Count($result) > 0): ?>

            <?php while ($row = $db->FetchRow ($result)): ?>

                <?php
                $video = new Video ($row[0], $db);
                $rating = new Rating ($row[0], $db);
                $tags = implode (' ', $video->tags);
                ?>

                <div class="block">
                    <p class="thumb">
                    <a class="video-thumb" href="<?=HOST?>/videos/<?=$video->video_id?>/<?=$video->dashed?>/" title="<?=$video->title?>">
                        <span class="play-button"></span><img src="<?=$config->thumb_bucket_url?>/<?=$video->filename?>.jpg" alt="<?=$video->title?>" />
                        <span class="duration"><?=$video->duration?></span>
                    </a>
                    </p>

                    <h3><a href="<?=HOST?>/videos/<?=$video->video_id?>/<?=$video->dashed?>/" title="<?=$video->title?>"><?=$video->title?></a></h3>
                    <p><?=Functions::CutOff ($video->description, 190)?></p>
                    <p class="search-video-stats">
                        <span><strong>Views:</strong>&nbsp;<?=$video->views?></span>
                        <span><strong>Uploaded On:</strong>&nbsp;<?=$video->date_uploaded?></span>
                    </p>
                    <p class="video-ratings">
                        <span class="thumbs-up green-text">(<?=$rating->GetLikeCount()?>+)</span>
                        <span class="thumbs-down red-text">(<?=$rating->GetDislikeCount()?>-)</span>
                    </p>
                    <br clear="all" />
                </div>

            <?php endwhile; ?>

        <?php else: ?>
            <div class="block"><strong>No Videos are Available</strong></div>
        <?php endif; ?>

        <br clear="all" />
        <?=$pagination->Paginate()?>
    </div>
    <br clear="all" />


<?php include (THEMES . '/blocks/footer.tpl'); ?>