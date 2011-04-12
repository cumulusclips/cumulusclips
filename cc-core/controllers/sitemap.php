<?php

### Created on July 2, 2009
### Created by Miguel A. Hurtado
### This script displays the sitemaps


// Include required files
include ($_SERVER['DOCUMENT_ROOT'] . '/config/bootstrap.php');
App::LoadClass ('Video');
App::LoadClass ('Login');


// Establish page variables, objects, arrays, etc
header ("Content-Type: text/xml");
$xml = '<?xml version="1.0" encoding="UTF-8"?>';
$allowed_types = array ('index', 'main', 'channels', 'videos');
$type = NULL;
$limit = 9000;



### Verify if type was provided
if (isset ($_GET['type']) && in_array ($_GET['type'], $allowed_types)) {
    $type = $_GET['type'];
}



### Verify if page was provided
if (!isset ($_GET['page'])) {
    $db->Close();
    header ("HTTP/1.0 404 Not Found");
    header ("Location:" . HOST . '/notfound/');
    exit();
}



### Count number of channel xml files
$query = "SELECT COUNT(user_id) FROM users WHERE account_status = 'Active'";
$result = $db->Query ($query);
$row = $db->FetchRow($result);
if ($row[0] > $limit) {
    $iChannelFileCount = ceil ($row[0]/$limit);
} else {
    $iChannelFileCount = 1;
}



### Count number of video xml files
$query = "SELECT COUNT(video_id) FROM videos WHERE status = 6";
$result = $db->Query ($query);
$row = $db->FetchRow ($result);
if ($row[0] > $limit) {
    $iVideoFileCount = ceil ($row[0]/$limit);
} else {
    $iVideoFileCount = 1;
}



### Display content based on requested xml type
switch ($type) {

    case 'index':

        if ($_GET['page'] != '') {
            $db->Close();
            header ("HTTP/1.0 404 Not Found");
            header ("Location:" . HOST . '/notfound/');
            exit();
        }

        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        $xml .= '<sitemap>';
        $xml .= '<loc>' . HOST . '/sitemap-main.xml</loc>';
        $xml .= '<lastmod>' . date ('Y-m-d') . '</lastmod>';
        $xml .= '</sitemap>';

        // List channel xml files
        for ($x = 1; $x <= $iChannelFileCount; $x++) {
            $loc = ($x == 1) ? HOST . '/sitemap-channels.xml' : HOST . '/sitemap-channels-' . $x . '.xml';
            $xml .= '<sitemap>';
            $xml .= '<loc>' . $loc . '</loc>';
            $xml .= '<lastmod>' . date ('Y-m-d') . '</lastmod>';
            $xml .= '</sitemap>';
        }

        // List video xml files
        for ($x = 1; $x <= $iVideoFileCount; $x++) {
            $loc = ($x == 1) ? HOST . '/sitemap-videos.xml' : HOST . '/sitemap-videos-' . $x . '.xml';
            $xml .= '<sitemap>';
            $xml .= '<loc>' . $loc . '</loc>';
            $xml .= '<lastmod>' . date ('Y-m-d') . '</lastmod>';
            $xml .= '</sitemap>';
        }

        $xml .= '</sitemapindex>';
        break;

    case 'main':

        if ($_GET['page'] != '') {
            $db->Close();
            header ("HTTP/1.0 404 Not Found");
            header ("Location:" . HOST . '/notfound/');
            exit();
        }

        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
            $xml .= '<url>';
              $xml .= '<loc>' . HOST . '/</loc>';
              $xml .= '<lastmod>' . date ('Y-m-d') . '</lastmod>';
              $xml .= '<changefreq>weekly</changefreq>';
              $xml .= '<priority>1.0</priority>';
           $xml .= '</url>';
            $xml .= '<url>';
              $xml .= '<loc>' . HOST . '/videos/</loc>';
              $xml .= '<lastmod>' . date ('Y-m-d') . '</lastmod>';
              $xml .= '<changefreq>daily</changefreq>';
              $xml .= '<priority>0.9</priority>';
           $xml .= '</url>';
            $xml .= '<url>';
              $xml .= '<loc>' . HOST . '/channels/</loc>';
              $xml .= '<lastmod>' . date ('Y-m-d') . '</lastmod>';
              $xml .= '<changefreq>daily</changefreq>';
              $xml .= '<priority>0.9</priority>';
           $xml .= '</url>';
            $xml .= '<url>';
              $xml .= '<loc>' . HOST . '/about/</loc>';
              $xml .= '<lastmod>' . date ('Y-m-d') . '</lastmod>';
              $xml .= '<changefreq>monthly</changefreq>';
              $xml .= '<priority>0.7</priority>';
           $xml .= '</url>';
            $xml .= '<url>';
              $xml .= '<loc>' . HOST . '/contact/</loc>';
              $xml .= '<lastmod>' . date ('Y-m-d') . '</lastmod>';
              $xml .= '<changefreq>monthly</changefreq>';
              $xml .= '<priority>0.7</priority>';
           $xml .= '</url>';
            $xml .= '<url>';
              $xml .= '<loc>' . HOST . '/terms/</loc>';
              $xml .= '<lastmod>' . date ('Y-m-d') . '</lastmod>';
              $xml .= '<changefreq>yearly</changefreq>';
              $xml .= '<priority>0.5</priority>';
           $xml .= '</url>';
            $xml .= '<url>';
              $xml .= '<loc>' . HOST . '/privacy/</loc>';
              $xml .= '<lastmod>' . date ('Y-m-d') . '</lastmod>';
              $xml .= '<changefreq>yearly</changefreq>';
              $xml .= '<priority>0.5</priority>';
           $xml .= '</url>';
            $xml .= '<url>';
              $xml .= '<loc>' . HOST . '/copyright/</loc>';
              $xml .= '<lastmod>' . date ('Y-m-d') . '</lastmod>';
              $xml .= '<changefreq>yearly</changefreq>';
              $xml .= '<priority>0.5</priority>';
           $xml .= '</url>';
            $xml .= '<url>';
              $xml .= '<loc>' . HOST . '/advertising/</loc>';
              $xml .= '<lastmod>' . date ('Y-m-d') . '</lastmod>';
              $xml .= '<changefreq>monthly</changefreq>';
              $xml .= '<priority>0.7</priority>';
           $xml .= '</url>';
       $xml .= '</urlset>';
        break;


    case 'channels':

        // Verify if page number was provided
        if ($_GET['page'] != '') {  // sitemap-channels-[0-9].xml
            if (is_numeric ($_GET['page']) && $_GET['page'] > 1 && $_GET['page'] <= $iChannelFileCount) {
                $page = trim ($_GET['page']);
            } else {
                $db->Close();
                header ("HTTP/1.0 404 Not Found");
                header ("Location:" . HOST . '/notfound/');
                exit();
            }
        } else {    // sitemap-channels.xml
            $page = 1;
        }
        $iStart = ($page*$limit)-$limit;
        $sAddOn = ($page > 1) ? " LIMIT $iStart, $limit" : " LIMIT $limit";
        
        $query = "SELECT user_id, username FROM users WHERE account_status = 'Active'" . $sAddOn;
        $result = $db->Query ($query);
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        while ($row = $db->FetchRow ($result)) {
            $xml .= '<url>';
            $xml .= '<loc>' . HOST . '/channels/' . $row[1] . '/</loc>';
            $xml .= '<lastmod>' . date ('Y-m-d') . '</lastmod>';
            $xml .= '<changefreq>weekly</changefreq>';
            $xml .= '<priority>0.8</priority>';
            $xml .= '</url>';
        }
        $xml .= '</urlset>';
        break;


    case 'videos':

        // Verify if page number was provided
        if ($_GET['page'] != '') {  // sitemap-videos-[0-9].xml
            if (is_numeric ($_GET['page']) && $_GET['page'] > 1 && $_GET['page'] <= $iVideoFileCount) {
                $page = trim ($_GET['page']);
            } else {
                $db->Close();
                header ("HTTP/1.0 404 Not Found");
                header ("Location:" . HOST . '/notfound/');
                exit();
            }
        } else {    // sitemap-videos.xml
            $page = 1;
        }
        $iStart = ($page*$limit)-$limit;
        $sAddOn = ($page > 1) ? " LIMIT $iStart, $limit" : " LIMIT $limit";

        $query = "SELECT video_id FROM videos WHERE status = 6" . $sAddOn;
        $result = $db->Query ($query);
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        while ($row = $db->FetchRow ($result)) {
            $video = new Video ($row[0], $db);
            $xml .= '<url>';
            $xml .= '<loc>' . HOST . '/videos/' . $video->video_id . '/' . $video->dashed . '/</loc>';
            $xml .= '<lastmod>' . date ('Y-m-d') . '</lastmod>';
            $xml .= '<changefreq>weekly</changefreq>';
            $xml .= '<priority>1.0</priority>';
            $xml .= '</url>';
        }
        $xml .= '</urlset>';
        break;


    default:

        $db->Close();
        header ("HTTP/1.0 404 Not Found");
        header ("Location:" . HOST . '/notfound/');
        exit();
        break;

}

echo $xml;

?>