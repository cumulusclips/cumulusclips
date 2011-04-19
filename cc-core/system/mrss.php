<?php

### Created on March 31, 2010
### Created by Miguel A. Hurtado
### This script displays the media rss feed


// Include required files
include ($_SERVER['DOCUMENT_ROOT'] . '/config/bootstrap.php');
App::LoadClass ('Video');


// Establish page variables, objects, arrays, etc
$header = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
header ("Content-Type: text/xml");

### Retrieve all videos
$query = "SELECT video_id FROM videos INNER JOIN categories ON videos.cat_id = categories.cat_id WHERE status = 6 ORDER BY video_id DESC";
$result = $db->Query ($query);


?><?php echo $header; ?>
<rss version="2.0" xmlns:media="http://search.yahoo.com/mrss/" xmlns:av="http://www.searchvideo.com/schemas/av/1.0">
    <channel>
        <title>Techie Videos</title>
        <link>http://www.techievideos.com/</link>
        <description>Tech Videos, Tutorials, and Screencasts</description>
        <?php while ($row = $db->FetchRow ($result)): ?>

        <?php
        $video = new Video ($row[0], $db);
        $date = new DateTime ($video->date_uploaded);
        ?>

        <item>
            <title><?php echo $video->title; ?></title>
            <link><?php echo HOST . '/videos/' . $video->video_id . '/' . $video->dashed; ?></link>
            <description><?php echo $video->description; ?></description>
            <pubDate><?php echo $date->format ('D, d M Y G:i:s'); ?> EST</pubDate>
            <media:description><?php echo $video->description; ?></media:description>
            <media:content url="<?=$config->flv_bucket_url?>/<?=$video->filename?>.flv" type="video/x-flv" duration="<?php echo DurationInSeconds ($video->duration); ?>" medium="video" bitrate="200" />
            <media:thumbnail url="<?=$config->thumb_bucket_url?>/<?=$video->filename?>.jpg" width="120" height="90" />
            <media:title><?php echo $video->title; ?></media:title>
            <media:keywords><?php echo implode (' ', $video->tags); ?></media:keywords>
            <media:category>Technology</media:category>
        </item>

        <?php endwhile; ?>
    </channel>
</rss>
