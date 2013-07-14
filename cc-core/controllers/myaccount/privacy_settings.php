<?php

// Include required files
include_once (dirname (dirname (dirname (__FILE__))) . '/config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Privacy');


// Establish page variables, objects, arrays, etc
View::InitView ('privacy_settings');
Plugin::Trigger ('privacy_settings.start');
Functions::RedirectIf (View::$vars->logged_in = User::LoginCheck(), HOST . '/login/');
View::$vars->user = new User (View::$vars->logged_in);
View::$vars->privacy = Privacy::LoadByUser (View::$vars->user->user_id);
View::$vars->data = array();
View::$vars->errors = array();
View::$vars->message = null;





/**************************
 * Handle Form if submitted
 *************************/

if (isset ($_POST['submitted'])) {

    // Validate Video Comments
    if (isset ($_POST['video_comment']) && in_array ($_POST['video_comment'], array ('0','1'))) {
        View::$vars->data['video_comment'] = $_POST['video_comment'];
    } else {
        View::$vars->errors['video_comment'] = TRUE;
    }


    // Validate Private Message
    if (isset ($_POST['new_message']) && in_array ($_POST['new_message'], array ('0','1'))) {
        View::$vars->data['new_message'] = $_POST['new_message'];
    } else {
        View::$vars->errors['new_message'] = TRUE;
    }


    // Validate New member Videos
    if (isset ($_POST['new_video']) && in_array ($_POST['new_video'], array ('0','1'))) {
        View::$vars->data['new_video'] = $_POST['new_video'];
    } else {
        View::$vars->errors['new_video'] = TRUE;
    }

    // Validate Video Ready
    if (isset ($_POST['videoReady']) && in_array ($_POST['videoReady'], array ('0','1'))) {
        View::$vars->data['videoReady'] = $_POST['videoReady'];
    } else {
        View::$vars->errors['videoReady'] = TRUE;
    }


    if (empty (View::$vars->errors)) {
        View::$vars->privacy->Update (View::$vars->data);
        View::$vars->message = Language::GetText('success_privacy_updated');
        View::$vars->message_type = 'success';
        Plugin::Trigger ('privacy_settings.update_privacy');
    } else {
        View::$vars->message = Language::GetText('error_general');
        View::$vars->message_type = 'errors';
    }

}


// Output page
Plugin::Trigger ('privacy_settings.before_render');
View::Render ('myaccount/privacy_settings.tpl');

?>