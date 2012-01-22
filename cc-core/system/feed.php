<?php

// Include required files
include_once (dirname (dirname (__FILE__)) . '/config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Video');


// Determine whether to pull member videos or all videos
if (!empty ($_GET['username']) && preg_match ('/^[a-z]+$/i', $_GET['username'])) {

    $data = array ('username' => $_GET['username'], 'status' => 'active');
    $id = User::Exist ($data);

    if ($id) {
        $user = new User ($id);
        $query_addon = " AND user_id = $user->user_id";
        $title = $user->username;
        $url = HOST . "/members/$user->username/";
    } else {
        header ('Location: ' . HOST . '/feed/');
        exit();
    }

} else {
    $query_addon = '';
    $title = $config->sitename;
    $url = HOST . '/';
}


$xml = new SimpleXMLElement ('<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"></rss>');
$feed = $xml->addChild ('channel');
$feed->addChild ('title', $title . ' - ' . Language::GetText ('video_feed'));
$meta = Language::GetMeta ('index');
$title = (empty ($meta->title)) ? $config->sitename : $meta->title;
$feed->addChild ('description', $meta->title);
$feed->addChild ('link', $url);



### Retrieve Videos
$query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND private = '0'" . $query_addon . " ORDER BY video_id DESC LIMIT 20";
$result = $db->Query($query);
while ($row = $db->FetchObj ($result)) {

    $video = new Video ($row->video_id);
    $item = $feed->addChild ('item');

    $item->addChild('title', $video->title);
    $item->addChild('description', $video->description);
    $item->addChild('link', $video->url . '/');
    
}

header ("Content-type: text/xml");

echo $xml->asXML();

?>