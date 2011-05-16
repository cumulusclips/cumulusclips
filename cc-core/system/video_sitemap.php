<?php

### Created on June 30, 200
### Created by Miguel A. Hurtado
### This script displays the video Sitemap


// Include required files
include ('../config/bootstrap.php');
App::LoadClass ('Video');
App::LoadClass ('Rating');
Plugin::Trigger ('video_sitemap.start');


// Establish page variables, objects, arrays, etc
$xml_header = '<?xml version="1.0" encoding="UTF-8"?>';
$limit = 45000;



### Verify if page was provided
if (!isset ($_GET['page'])) App::Throw404();



### Count number of video xml files
$query = "SELECT COUNT(video_id) FROM " . DB_PREFIX . "videos WHERE status = 6";
$result = $db->Query ($query);
$row = $db->FetchRow ($result);
if ($row[0] > $limit) {
    $file_count = ceil ($row[0]/$limit);
} else {
    $file_count = 1;
}



### Display content based on requested xml type
if (empty ($_GET['page'])) {

    // Open sitemap index
    Plugin::Trigger ('video_sitemap.sitemapindex');
    $xml_root = '<sitemapindex></sitemapindex>';
    $xml_frame = $xml_header . $xml_root;
    $xml = new SimpleXMLElement ($xml_frame);
    $xml->addAttribute ('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

    // Add video xml files
    for ($x = 1; $x <= $file_count; $x++) {
        $sitemap = $xml->addChild ('sitemap');
        $sitemap->addChild ('loc', HOST . '/video-sitemap-' . $x . '.xml');
        $sitemap->addChild ('lastmod', date ('Y-m-d'));
    }


} elseif (is_numeric ($_GET['page']) && $_GET['page'] > 0 && $_GET['page'] <= $file_count) {

    $page = $_GET['page'];
    $start = ($page*$limit)-$limit;
    $query_limit = ($page > 1) ? " LIMIT $start, $limit" : " LIMIT $limit";
    $query = "SELECT video_id, cat_name FROM " . DB_PREFIX . "videos INNER JOIN " . DB_PREFIX . "categories ON " . DB_PREFIX . "videos.cat_id = " . DB_PREFIX . "categories.cat_id WHERE status = 6" . $query_limit;
    $result = $db->Query ($query);

    // Open video sitemap
    Plugin::Trigger ('video_sitemap.sitemap');
    $namespace = ' xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';
    $namespace .= ' xmlns:video="http://www.google.com/schemas/sitemap-video/1.1"';

    $xml_root = '<urlset' . $namespace . '></urlset>';
    $xml_frame = $xml_header . $xml_root;
    $xml = new SimpleXMLElement ($xml_frame);

    // Add video entries
    while ($row = $db->FetchObj ($result)) {

        $video = new Video ($row->video_id);
        $url = $xml->addChild ('url');

        $url->addChild ('loc', HOST . '/videos/' . $video->video_id . '/' . $video->slug . '/');
        $block = $url->addChild ('video:video','','video');

        $block->addChild ('content_loc', $config->flv_bucket_url . '/' . $video->filename);
        $block->addChild ('thumbnail_loc', $config->thumb_bucket_url . '/' . $video->filename);
        $block->addChild ('title', Functions::CutOff ($video->title,90));
        $block->addChild ('description', Functions::CutOff ($video->description,2040));
        $block->addChild ('rating', Rating::GetFiveScaleRating ($row->video_id));
        $block->addChild ('view_count', $video->views);
        $block->addChild ('publication_date', date ('Y-m-d', strtotime ($video->date_created)));

        foreach ($video->tags as $_value) {
            $block->addChild ('tag', $_value);
        }

        $block->addChild ('category', $row->cat_name);
        $block->addChild ('family_friendly', 'yes');
        $block->addChild ('duration', Functions::DurationInSeconds ($video->duration));

    }

} else {
    App::Throw404();
}

// Output XML
Plugin::Trigger ('video_sitemap.output');
header ("Content-type: text/xml");
echo $xml->asXML();

?>