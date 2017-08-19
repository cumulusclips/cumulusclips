<?php

// Establish page variables, objects, arrays, etc
$xml_header = '<?xml version="1.0" encoding="UTF-8"?>';
$limit = 45000;
$this->view->options->disableView = true;
$config = Registry::get('config');

// Count number of video xml files
$db = Registry::get('db');
$query = "SELECT COUNT(video_id) AS total FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND private = '0'";
$row = $db->fetchRow($query);
if ($row['total'] > $limit) {
    $file_count = ceil($row['total']/$limit);
} else {
    $file_count = 1;
}

// Display content based on requested xml type
if (empty($_GET['page'])) {

    // Open sitemap index
    $xml_root = '<sitemapindex></sitemapindex>';
    $xml_frame = $xml_header . $xml_root;
    $xml = new SimpleXMLElement($xml_frame);
    $xml->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

    // Add video xml files
    for ($x = 1; $x <= $file_count; $x++) {
        $sitemap = $xml->addChild('sitemap');
        $sitemap->addChild('loc', HOST . '/video-sitemap-' . $x . '.xml');
        $sitemap->addChild('lastmod', date('Y-m-d'));
    }

} elseif (is_numeric($_GET['page']) && $_GET['page'] > 0 && $_GET['page'] <= $file_count) {

    $page = $_GET['page'];
    $start = ($page*$limit)-$limit;
    $query_limit = ($page > 1) ? " LIMIT $start, $limit" : " LIMIT $limit";
    $query = "SELECT video_id, name FROM " . DB_PREFIX . "videos INNER JOIN " . DB_PREFIX . "categories ON " . DB_PREFIX . "videos.category_id = " . DB_PREFIX . "categories.category_id WHERE status = 'approved' AND private = '0'" . $query_limit;
    $result = $db->fetchAll($query);

    // Open video sitemap
    $namespace = ' xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';
    $namespace .= ' xmlns:video="http://www.google.com/schemas/sitemap-video/1.1"';

    $xml_root = '<urlset' . $namespace . '></urlset>';
    $xml_frame = $xml_header . $xml_root;
    $xml = new SimpleXMLElement($xml_frame);

    // Add video entries
    $videoMapper = new VideoMapper();
    $videoService = new VideoService();
    $ratingService = new RatingService();
    foreach ($result as $row) {
        $video = $videoMapper->getVideoById($row['video_id']);
        $url = $xml->addChild('url');

        $url->addChild('loc', $videoService->getUrl($video) . '/');
        $block = $url->addChild('video:video','','video');

        $block->addChild('content_loc', $config->h264Url . '/' . $video->filename . '.mp4');
        $block->addChild('thumbnail_loc', $config->thumbUrl . '/' . $video->filename . '.jpg');
        $block->addChild('title', '<![CDATA[' . $video->title . ']]');
        $block->addChild('description', '<![CDATA[' . $video->description . ']]');
        $block->addChild('rating', $ratingService->getFiveScaleRating($video->videoId));
        $block->addChild('view_count', $video->views);
        $block->addChild('publication_date', date('Y-m-d', strtotime($video->dateCreated)));

        foreach ($video->tags as $_value) {
            $block->addChild('tag', $_value);
        }

        $block->addChild('category', $row['name']);
        $block->addChild('family_friendly', 'yes');
        $block->addChild('duration', Functions::durationToSeconds($video->duration));
    }

} else {
    App::throw404();
}

// Output XML
header("Content-type: text/xml");
echo $xml->asXML();