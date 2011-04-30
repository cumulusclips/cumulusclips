<?php

### Created on April 2, 2009
### Created by Miguel A. Hurtado
### This script allows the user to edit their privacy settings


// Include required files
include ('../../config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Privacy');
View::InitView();


// Establish page variables, objects, arrays, etc
View::LoadPage ('privacy_settings');
View::$vars->logged_in = User::LoginCheck (HOST . '/login/');
View::$vars->data = array();
View::$vars->user = new User (View::$vars->logged_in);
View::$vars->privacy = new Privacy (View::$vars->user->user_id);
View::$vars->errors = NULL;
View::$vars->success = NULL;





/**************************
 * Handle Form if submitted
 *************************/

if (isset ($_POST['submitted'])) {

    // Validate Video Comments
    if (isset ($_POST['video_comment']) && ($_POST['video_comment'] == 'yes' || $_POST['video_comment'] == 'no')) {
        View::$vars->data['video_comment'] = $_POST['video_comment'];
    } else {
        View::$vars->errors = TRUE;
    }


    // Validate Private Message
    if (isset ($_POST['new_message']) && ($_POST['new_message'] == 'yes' || $_POST['new_message'] == 'no')) {
        View::$vars->data['new_message'] = $_POST['new_message'];
    } else {
        View::$vars->errors = TRUE;
    }


    // Validate Newsletter
    if (isset ($_POST['newsletter']) && ($_POST['newsletter'] == 'yes' || $_POST['newsletter'] == 'no')) {
        View::$vars->data['newsletter'] = $_POST['newsletter'];
    } else {
        View::$vars->errors = TRUE;
    }


    // Validate New Channel Videos
    if (isset ($_POST['new_video']) && ($_POST['new_video'] == 'yes' || $_POST['new_video'] == 'no')) {
        View::$vars->data['new_video'] = $_POST['new_video'];
    } else {
        View::$vars->errors = TRUE;
    }


    if (!View::$vars->errors) {
        View::$vars->privacy->Update (View::$vars->data);
        View::$vars->success = Language::GetText('success_privacy_updated');
    } else {
        View::$vars->errors = Language::GetText('error_general');
    }

}

// Output page
View::SetLayout ('portal.layout.tpl');
View::Render ('myaccount/privacy_settings.tpl');

?>