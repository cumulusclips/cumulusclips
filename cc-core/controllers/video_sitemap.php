<?php

### Created on June 30, 200
### Created by Miguel A. Hurtado
### This script displays the video Sitemap


// Include required files
include ($_SERVER['DOCUMENT_ROOT'] . '/config/bootstrap.php');
App::LoadClass ('Video');
App::LoadClass ('Rating');


// Establish page variables, objects, arrays, etc
header ("Content-Type: text/xml");
$xml = '<?xml version="1.0" encoding="UTF-8"?>';
$limit = 9000;



### Verify if page was provided
if (!isset ($_GET['page'])) {
    $db->Close();
    header ("HTTP/1.0 404 Not Found");
    header ("Location:" . HOST . '/notfound/');
    exit();
}



### Count number of video xml files
$query = "SELECT COUNT(video_id) FROM videos WHERE status = 6";
$result = $db->Query ($query);
$row = $db->FetchRow ($result);
if ($row[0] > $limit) {
    $iFileCount = ceil ($row[0]/$limit);
} else {
    $iFileCount = 1;
}



### Display content based on requested xml type
if ($_GET['page'] == '') {

    $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

    // List video xml files
    for ($x = 1; $x <= $iFileCount; $x++) {
        $xml .= '<sitemap>';
        $xml .= '<loc>' . HOST . '/video-sitemap-' . $x . '.xml</loc>';
        $xml .= '<lastmod>' . date ('Y-m-d') . '</lastmod>';
        $xml .= '</sitemap>';
    }

    $xml .= '</sitemapindex>';

} elseif (is_numeric ($_GET['page']) && $_GET['page'] > 0 && $_GET['page'] <= $iFileCount) {

    $page = $_GET['page'];
    $iStart = ($page*$limit)-$limit;
    $sAddOn = ($page > 1) ? " LIMIT $iStart, $limit" : " LIMIT $limit";

    $query = "SELECT video_id, cat_name FROM videos INNER JOIN categories ON videos.cat_id = categories.cat_id WHERE status = 6" . $sAddOn;
    $result = $db->Query ($query);
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">';

    while ($row = $db->FetchAssoc ($result)) {

        $video = new Video ($row['video_id'], $db);
        $rating = new Rating ($row['video_id'], $db);

        $xml .= '<url>';
            $xml .= '<loc>' . HOST . '/videos/' . $video->video_id . '/' . $video->dashed . '/</loc>';
            $xml .= '<video:video>';
                $xml .= '<video:content_loc>' . $config->flv_bucket_url . '/' . $video->filename . '.flv</video:content_loc>';
                $xml .= '<video:thumbnail_loc>' . $config->thumb_bucket_url . '/' . $video->filename . '.jpg</video:thumbnail_loc>';
                $xml .= '<video:title>' . Functions::CutOff ($video->title,90) . '</video:title>';
                $xml .= '<video:description>' . Functions::CutOff ($video->description,2040) . '</video:description>';
                $xml .= '<video:rating>' . $rating->GetRating() . '.0</video:rating>';
                $xml .= '<video:view_count>' . $video->views . '</video:view_count>';
                $date = new DateTime ($video->date_uploaded);
                $xml .= '<video:publication_date>' . $date->format('Y-m-d') . '</video:publication_date>';

                foreach ($video->tags as $value) {
                    $xml .= '<video:tag>' . $value . '</video:tag>';
                }

                $xml .= '<video:category>' . $row['cat_name'] . '</video:category>';
                $xml .= '<video:family_friendly>yes</video:family_friendly>';
                $xml .= '<video:duration>' . DurationInSeconds ($video->duration) . '</video:duration>';
            $xml .= '</video:video>';
        $xml .= '</url>';

    }

    $xml .= '</urlset>';

} else {
    $db->Close();
    header ("HTTP/1.0 404 Not Found");
    header ("Location:" . HOST . '/notfound/');
    exit();
}

// Output XML
echo $xml;

?>