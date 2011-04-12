<?php

### Created on March 20, 2009
### Created by Miguel A. Hurtado
### This script displays all the comments for a video or channel


// Include required files
include ($_SERVER['DOCUMENT_ROOT'] . '/config/bootstrap.php');
App::LoadClass ('Login');
App::LoadClass ('User');
App::LoadClass ('VideoComment');
App::LoadClass ('Pagination');
App::LoadClass ('Video');
App::LoadClass ('Picture');
App::LoadClass ('Flag');


// Establish page variables, objects, arrays, etc
$login = new Login ($db);
$logged_in = $login->LoginCheck();
$page_title = 'Techie Videos - View All Comments';
$limit = 10;
$Success = NULL;
$Errors = NULL;


// Retrieve user data if logged in
if ($logged_in) {
    $user = new User ($logged_in, $db);
}


// Verify a comment type and id was given
if (!isset ($_GET['type']) || !isset ($_GET['id'])) {
    $db->Close();
    header ("HTTP/1.0 404 Not Found");
    Login::Forward ('/notfound/');
    exit();
}

$id = $_GET['id'];
$url = '/comments/videos/' . $id;
$type = 'video';
$data = array ('video_id' => $id);
if (Video::Exist ($data, $db)) {
    
    $video = new Video ($id, $db);
    $query = "SELECT comment_id FROM video_comments WHERE video_id = $id ORDER BY comment_id DESC";

    // Retrieve total count
    $result_count = $db->Query ($query);
    $total = $db->Count ($result_count);

    // Initialize pagination
    $pagination = new Pagination ($url, $total);
    $start_record = $pagination->GetStartRecord();

    // Retrieve limited results
    $query .= " LIMIT $start_record, $limit";
    $result = $db->Query ($query);

} else {
    $db->Close();
    header ("HTTP/1.0 404 Not Found");
    Login::Forward ('/notfound/');
    exit();
}







/***************************
* Handle Flag if Submitted *
***************************/

if (isset ($_GET['flag']) && !empty ($_GET['flag'])) {

    // Verify user is logged in
    if ($logged_in) {

        // Verify comment exists
        $comment = new VideoComment ($_GET['flag'], $db);
        if ($comment->found) {

            // Verify comment doesn't belong to user
            if ($comment->user_id == $user->user_id) {
                $Errors = 'You can\'t report your own comments as inappropriate!';
            } else {

                // Create flag if it doesn't already exist
                $data = array ('user_id' => $user->user_id, 'id' => $_GET['flag'], 'flag_type' => 'video-comment');
                if (!Flag::Exist ($data, $db)) {
                    Flag::Create ($data, $db);
                    $Success = 'Thank you for reporting this. We will look into this immediately.';
                } else {
                    $Errors = 'You have already reported this as inappropriate content! Thank you for your assistance.';
                }

            }

        }

    } else {
        $Errors = 'You must be logged in to report inappropriate content!';
    }

}

$content_file = 'view_comments.tpl';
include (THEMES . '/layouts/two_column.layout.tpl');

?>