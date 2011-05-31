<?php

### Created on February 28, 2009
### Created by Miguel A. Hurtado
### This script displays the site homepage


// Include required files
include ('../cc-core/config/admin.bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Video');
App::LoadClass ('Privacy');
App::LoadClass ('EmailTemplate');
App::LoadClass ('Pagination');


// Establish page variables, objects, arrays, etc
Plugin::Trigger ('admin.videos.start');
//$logged_in = User::LoginCheck(HOST . '/login/');
//$admin = new User ($logged_in);
$content = 'videos.tpl';
$records_per_page = 9;
$url = ADMIN . '/videos.php';
$query_string = array();
$categories = array();
$message = null;
$sub_header = null;



// Retrieve Category names
$query = "SELECT cat_id, cat_name FROM " . DB_PREFIX . "categories";
$result = $db->Query ($query);
while ($row = $db->FetchObj ($result)) {
    $categories[$row->cat_id] = $row->cat_name;
}



### Handle "Delete" video if requested
if (!empty ($_GET['delete']) && is_numeric ($_GET['delete'])) {

    // Validate video id
    if (Video::Exist (array ('video_id' => $_GET['delete']))) {
        Video::Delete($_GET['delete']);
        $message = 'Video has been deleted';
        $message_type = 'success';
    }

}


### Handle "Unban" video if requested
else if (!empty ($_GET['unban']) && is_numeric ($_GET['unban'])) {

    // Validate video id
    if (Video::Exist (array ('video_id' => $_GET['unban']))) {
        $video = new Video ($_GET['unban']);
        $video->Update (array ('status' => 6));
        $message = 'Video has been unbanned';
        $message_type = 'success';
    }

}


### Handle "Ban" video if requested
else if (!empty ($_GET['ban']) && is_numeric ($_GET['ban'])) {

    // Validate video id
    if (Video::Exist (array ('video_id' => $_GET['ban']))) {
        $video = new Video ($_GET['ban']);
        $video->Update (array ('status' => 7));
        $message = 'Video has been banned';
        $message_type = 'success';
    }

}


### Handle "Approve" video if requested
else if (!empty ($_GET['approve']) && is_numeric ($_GET['approve'])) {

    // Validate video id
    if (Video::Exist (array ('video_id' => $_GET['approve']))) {
        
        // Update video
        $video = new Video ($_GET['approve']);
        $video->Update (array ('status' => 6));
        
        
        // Send subscribers notification if opted-in
        $query = "SELECT user_id FROM " . DB_PREFIX . "subscriptions WHERE member = $video->user_id";
        $result_alert = $db->Query ($query);
        while ($opt = $db->FetchRow ($result_alert)) {

            $subscriber = new User ($opt[0]);
            $privacy = Privacy::LoadByUser ($opt[0]);
            if ($privacy->OptCheck ('new_video')) {
                $template = new EmailTemplate ('/new_video.htm');
                $template_data = array (
                    'host'      => HOST,
                    'email'  => $subscriber->email,
                    'channel'   => $user->username,
                    'title'     => $video->title,
                    'video_id'  => $video->video_id,
                    'dashed'    => $video->slug
                );
                $template->Replace($template_data);
                $template->Send ($subscriber->email);
            }

        } 
        
        
        // Display message
        $message = 'Video has been approved and is now available';
        $message_type = 'success';
    }

}




// Determine which type (status) of video to display
$status = (!empty ($_GET['status'])) ? $_GET['status'] : 6;
switch ($status) {

    case 9:
        $query_string['status'] = 9;
        $header = 'Pending Videos';
        $page_title = 'Pending Videos';
        break;
    case 7:
        $query_string['status'] = 7;
        $header = 'Banned Videos';
        $page_title = 'Banned Videos';
        break;
    default:
        $status = 6;
        $header = 'Approved Videos';
        $page_title = 'Approved Videos';
        break;

}
$query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE status = $status";



// Handle Search Member Form
if (isset ($_POST['search_submitted'])&& !empty ($_POST['search'])) {

    $like = $db->Escape (trim ($_POST['search']));
    $query_string['search'] = $like;
    $query .= " AND title LIKE '%$like%'";
    $sub_header = "Search Results for: <em>$like</em>";

} else if (!empty ($_GET['search'])) {

    $like = $db->Escape (trim ($_GET['search']));
    $query_string['search'] = $like;
    $query .= " AND title LIKE '%$like%'";
    $sub_header = "Search Results for: <em>$like</em>";

}



// Retrieve total count
$result_count = $db->Query ($query);
$total = $db->Count ($result_count);

// Initialize pagination
$url .= (!empty ($query_string)) ? '?' . http_build_query($query_string) : '';
$pagination = new Pagination ($url, $total, $records_per_page, false);
$start_record = $pagination->GetStartRecord();
$_SESSION['list_page'] = $pagination->GetURL();

// Retrieve limited results
$query .= " LIMIT $start_record, $records_per_page";
$result = $db->Query ($query);


// Output Page
include (THEME_PATH . '/admin.layout.tpl');

?>