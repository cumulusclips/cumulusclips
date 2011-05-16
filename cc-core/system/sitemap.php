<?php

### Created on July 2, 2009
### Created by Miguel A. Hurtado
### This script displays the sitemaps


// Include required files
include ('../config/bootstrap.php');
App::LoadClass ('Video');


// Establish page variables, objects, arrays, etc
$xml_header = '<?xml version="1.0" encoding="UTF-8"?>';
$allowed_types = array ('index', 'main', 'members', 'videos');
$type = null;
$limit = 9000;



### Verify if type was provided
if (isset ($_GET['type']) && in_array ($_GET['type'], $allowed_types)) {
    $type = $_GET['type'];
} else {
    App::Throw404();
}



### Verify if page was provided
if (!isset ($_GET['page'])) App::Throw404();



### Count number of member xml/sitemap files
$query = "SELECT COUNT(user_id) FROM " . DB_PREFIX . "users WHERE account_status = 'Active'";
$result = $db->Query ($query);
$row = $db->FetchRow($result);
if ($row[0] > $limit) {
    $member_file_count = ceil ($row[0]/$limit);
} else {
    $member_file_count = 1;
}



### Count number of video xml/sitemap files
$query = "SELECT COUNT(video_id) FROM " . DB_PREFIX . "videos WHERE status = 6";
$result = $db->Query ($query);
$row = $db->FetchRow ($result);
if ($row[0] > $limit) {
    $video_file_count = ceil ($row[0]/$limit);
} else {
    $video_file_count = 1;
}





### Display content based on requested xml type
switch ($type) {

    case 'index':

        if (!empty ($_GET['page'])) App::Throw404();

        // Open xml sitemap index
        $xml_root = '<sitemapindex></sitemapindex>';
        $xml_frame = $xml_header . $xml_root;
        $xml = new SimpleXMLElement ($xml_frame);
        $xml->addAttribute ('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        // Add main xml document
        $sitemap = $xml->addChild ('sitemap');
        $sitemap->addChild ('loc', HOST . '/sitemap-main.xml');
        $sitemap->addChild ('lastmod', date ('Y-m-d'));

        // Add member xml documents
        for ($x = 1; $x <= $member_file_count; $x++) {
            $loc = ($x == 1) ? HOST . '/sitemap-members.xml' : HOST . '/sitemap-members-' . $x . '.xml';
            $sitemap = $xml->addChild ('sitemap');
            $sitemap->addChild ('loc', $loc);
            $sitemap->addChild ('lastmod', date ('Y-m-d'));
        }

        // Add video xml documents
        for ($x = 1; $x <= $video_file_count; $x++) {
            $loc = ($x == 1) ? HOST . '/sitemap-videos.xml' : HOST . '/sitemap-videos-' . $x . '.xml';
            $sitemap = $xml->addChild ('sitemap');
            $sitemap->addChild ('loc', $loc);
            $sitemap->addChild ('lastmod', date ('Y-m-d'));
        }
        break;




    case 'main':

        if (!empty ($_GET['page'])) App::Throw404();
        
        // Open main xml document
        $xml_root = '<urlset></urlset>';
        $xml_frame = $xml_header . $xml_root;
        $xml = new SimpleXMLElement ($xml_frame);
        $xml->addAttribute ('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');     

        // Add site root '/'
        $url = $xml->addChild ('url');
        $url->addChild ('loc', HOST . '/');
        $url->addChild ('lastmod', date ('Y-m-d'));
        $url->addChild ('changefreq', 'weekly');
        $url->addChild ('priority', '1.0');

        // Add videos page '/videos/'
        $url = $xml->addChild ('url');
        $url->addChild ('loc', HOST . '/videos/');
        $url->addChild ('lastmod', date ('Y-m-d'));
        $url->addChild ('changefreq', 'daily');
        $url->addChild ('priority', '0.9');

        // Add members page '/members/'
        $url = $xml->addChild ('url');
        $url->addChild ('loc', HOST . '/members/');
        $url->addChild ('lastmod', date ('Y-m-d'));
        $url->addChild ('changefreq', 'daily');
        $url->addChild ('priority', '0.9');

        // Add contact page '/contact/'
        $url = $xml->addChild ('url');
        $url->addChild ('loc', HOST . '/members/');
        $url->addChild ('lastmod', date ('Y-m-d'));
        $url->addChild ('changefreq', 'monthly');
        $url->addChild ('priority', '0.7');
        break;




    case 'members':

        // Verify if page number was provided
        if ($_GET['page'] != '') {  // sitemap-members-[0-9].xml
            if (is_numeric ($_GET['page']) && $_GET['page'] >= 1 && $_GET['page'] <= $member_file_count) {
                $page = trim ($_GET['page']);
            } else {
                App::Throw404();
            }
        } else {    // sitemap-members.xml
            $page = 1;
        }

        // Retrive members to display on this sitemap
        $start = ($page*$limit)-$limit;
        $query_limit = ($page > 1) ? " LIMIT $start, $limit" : " LIMIT $limit";
        $query = "SELECT username FROM " . DB_PREFIX . "users WHERE account_status = 'Active'" . $query_limit;
        $result = $db->Query ($query);

        // Open member xml document
        $xml_root = '<urlset></urlset>';
        $xml_frame = $xml_header . $xml_root;
        $xml = new SimpleXMLElement ($xml_frame);
        $xml->addAttribute ('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        // Add member profile pages
        while ($row = $db->FetchObj ($result)) {
            $url = $xml->addChild ('url');
            $url->addChild ('loc', HOST . '/members/' . $row->username . '/');
            $url->addChild ('lastmod', date ('Y-m-d'));
            $url->addChild ('changefreq', 'weekly');
            $url->addChild ('priority', '0.8');
        }
        break;




    case 'videos':

        // Verify if page number was provided
        if ($_GET['page'] != '') {  // sitemap-videos-[0-9].xml
            if (is_numeric ($_GET['page']) && $_GET['page'] > 1 && $_GET['page'] <= $video_file_count) {
                $page = trim ($_GET['page']);
            } else {
                App::Throw404();
            }
        } else {    // sitemap-videos.xml
            $page = 1;
        }

        // Retrive videos to display on this sitemap
        $start = ($page*$limit)-$limit;
        $query_limit = ($page > 1) ? " LIMIT $start, $limit" : " LIMIT $limit";
        $query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE status = 6" . $query_limit;
        $result = $db->Query ($query);

        // Open video xml document
        $xml_root = '<urlset></urlset>';
        $xml_frame = $xml_header . $xml_root;
        $xml = new SimpleXMLElement ($xml_frame);
        $xml->addAttribute ('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        // Add video pages
        while ($row = $db->FetchObj ($result)) {
            $video = new Video ($row->video_id);
            $url = $xml->addChild ('url');
            $url->addChild ('loc', HOST . '/videos/' . $video->video_id . '/' . $video->dashed . '/');
            $url->addChild ('lastmod', date ('Y-m-d'));
            $url->addChild ('changefreq', 'weekly');
            $url->addChild ('priority', '1.0');
        }
        break;

}

header ("Content-type: text/xml");
echo $xml->asXML();

?>