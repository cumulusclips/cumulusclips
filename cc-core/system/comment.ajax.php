<?php

### Created on March 15, 2009
### Created by Miguel A. Hurtado
### This script performs all the user actions for a video via AJAX


// Include required files
include ('../config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Video');
App::LoadClass ('Comment');
App::LoadClass ('Privacy');
App::LoadClass ('EmailTemplate');


// Establish page variables, objects, arrays, etc
$logged_in = User::LoginCheck();
if ($logged_in) $user = new User ($logged_in);
$Errors = array();
$data = array();



// Verify a video was selected
if (isset ($_POST['video_id']) && is_numeric ($_POST['video_id'])) {
    $video = new Video ($_POST['video_id']);
} else {
    App::Throw404();
}



// Check if video is valid
if (!$video->found || $video->status != 6) {
    App::Throw404();
}





/***********************
Handle page if submitted
***********************/

if (isset ($_POST['submitted'])) {

    // Verify user is logged in
    if ($logged_in) {
        $data['user_id'] = $user->user_id;
    } else {

        $data['user_id'] = 0;
        $data['ip'] = $_SERVER['REMOTE_ADDR'];

        // Validate name
        if (!empty ($_POST['name']) && !ctype_space ($_POST['name'])) {
            $data['name'] = htmlspecialchars ( trim ($_POST['name']));
        } else {
            $Errors['name'] = Language::GetText('error_name');
        }


        // Validate email address
        $email_pattern = '/^[a-z0-9][a-z0-9_\.\-]+@[a-z0-9][a-z0-9\.\-]+\.[a-z0-9]{2,4}$/i';
        if (!empty ($_POST['email']) && !ctype_space ($_POST['email']) && preg_match ($email_pattern, $_POST['email'])) {
            $data['email'] = htmlspecialchars ( trim ($_POST['email']));
        } else {
            $Errors['email'] = Language::GetText('error_email');
        }


        // Validate website
        $website_pattern = '/^(https?:\/\/)?[a-z0-9][a-z0-9\.\-]+\.[a-z0-9]{2,4}$/i';
        if (!empty ($_POST['website']) && !ctype_space ($_POST['website']) && preg_match ($website_pattern, $_POST['website'])) {
            $data['website'] = htmlspecialchars( trim ($_POST['website']));
        }

    }

    // Validate comments
    if (!empty ($_POST['comments']) && !ctype_space ($_POST['comments'])) {
        $data['comments'] = htmlspecialchars ( trim ($_POST['comments']));
    } else {
        $Errors['comments'] = Language::GetText('error_comment');
    }


    // Save comment if no errors were found
    if (empty ($Errors)) {

        $data['video_id'] = $video->video_id;
        $data['status'] = 'approved';
        $comment_id = Comment::Create ($data);
        $comment = new Comment ($comment_id);

        // Send video owner notifition if opted-in
        $privacy = Privacy::LoadByUser ($video->user_id);
        if ($privacy->OptCheck ('video_comment')) {
            $template = new EmailTemplate ('/video_comment.htm');
            $template_user = new User ($video->user_id);
            $template_data = array (
                'host'   => HOST,
                'email'  => $template_user->email,
                'title'  => $video->title
            );
            $template->Replace ($template_data);
            $template->Send ($template_user->email);
        }


        // Retrieve formatted new comment block
        View::InitView();
        ob_start();
        View::RepeatingBlock('comment.tpl', array ($comment->comment_id));
        $comment_block = ob_get_contents();
        ob_end_clean();

        echo json_encode (array ('result' => 1, 'msg' => (string)Language::GetText('success_comment_posted'), 'other' => $comment_block));
        exit();

    } else {
        $error_msg = Language::GetText('errors_below');
        $error_msg .= '<br /><br /> - ' . implode ('<br /> - ', $Errors);
        echo json_encode (array ('result' => 0, 'msg' => $error_msg));
        exit();
    }

}   // END verify if page was submitted	

?>