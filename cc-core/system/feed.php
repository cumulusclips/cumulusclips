<?php

### Created on March 22, 2009
### Created by Miguel A. Hurtado
### This script display the rss feed page


// Include required files
include ($_SERVER['DOCUMENT_ROOT'] . '/config/bootstrap.php');
App::LoadClass ('Login');
App::LoadClass ('User');
App::LoadClass ('Video');


// Establish page variables, objects, arrays, etc
header ("Content-type: text/xml");
$user = NULL;
$query_addon = " ORDER BY video_id DESC";
$xml = '';



// Determine whether to pull channel videos or all videos
if (isset ($_GET['username']) && !empty ($_GET['username'])) {
    $data = array ('username' => $_GET['username']);
    $id = User::Exist($data, $db);
    if ($id) {
        $user = new User ($id, $db);
        $query_addon = " AND user_id = $user->user_id ORDER BY video_id DESC";
    } else {
//        Login::Forward('/feed/');
        header ('Location: http://feeds.feedburner.com/TechieVideos');
        exit();
    }
}



### Pull Videos
$query = "SELECT video_id FROM videos WHERE status = 6" . $query_addon . " LIMIT 20";
$result = $db->Query($query);



### Establish Feed Variables
$title = ($user)?$user->username:'TechieVideos.com - Video Feeds';
$url = ($user)?HOST . '/channels/' . $user->username:HOST;




$xml .= '<?xml version="1.0"?>';
$xml .= '<rss version="2.0">';
$xml .= '<channel>';
$xml .= '<title>' . $title . '</title>';
$xml .= '<description>View all recently added tech videos, screencasts, and tutorials from members all across the tech industry.</description>';
$xml .= '<link>' . $url . '/</link>';

while ($row = $db->FetchRow ($result)) {

    $video = new Video ($row[0], $db);

    $xml .= "\t" . '<item>';
    $xml .= "\n\t\t" . '<title>' . $video->title . '</title>';
    $xml .= "\n\t\t" . '<description><![CDATA[';

    $xml .= '<p>' . $video->description . '<br />';
    $xml .= '<a href="' . HOST . '/videos/' . $video->video_id . '/' . $video->dashed . '/" title="' . $video->title . '">';
    $xml .= '<img src="' . $config->thumb_bucket_url . '/' . $video->filename . '.jpg" alt="' . $video->title . '" />';
    $xml .= '</a></p>';

    $xml .= ']]></description>';
    $xml .= "\n\t\t" . '<link>' . HOST . '/videos/' . $video->video_id . '/' . $video->dashed . '/</link>';
    $xml .= "\n\t" . '</item>' . "\n";

}

$xml .= '</channel>';
$xml .= '</rss>';

echo $xml;

?>