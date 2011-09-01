<?php

### Created on May 9, 2009
### Created by Miguel A. Hurtado
### This script allows users to edit their videos


// Include required files
include ('../../config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Video');


// Establish page variables, objects, arrays, etc
View::InitView ('edit_video');
Plugin::Trigger ('edit_video.start');
Functions::RedirectIf (View::$vars->logged_in = User::LoginCheck(), HOST . '/login/');
View::$vars->user = new User (View::$vars->logged_in);
View::$vars->Errors = array();
View::$vars->error_msg = null;
View::$vars->success = null;



### Verify a video was provided
if (isset ($_GET['vid']) && is_numeric ($_GET['vid']) && $_GET['vid'] != 0) {

    ### Retrieve video information
    View::$vars->data = array ('user_id' => View::$vars->user->user_id, 'video_id' => $_GET['vid']);
    $id = Video::Exist(View::$vars->data);
    if ($id) {
        View::$vars->video = new Video ($id);
    } else {
        header ('Location: ' . HOST . '/myaccount/myvideos/');
        exit();
    }

} else {
    header ('Location: ' . HOST . '/myaccount/myvideos/');
    exit();
}





/***********************
Handle form if submitted
***********************/

if (isset ($_POST['submitted'])) {


    // Validate title
    if (!empty ($_POST['title']) && !ctype_space ($_POST['title'])) {
        View::$vars->data['title'] = htmlspecialchars (trim ($_POST['title']));
    } else {
        View::$vars->Errors['title'] = Language::GetText('error_title');
    }


    // Validate description
    if (!empty ($_POST['description']) && !ctype_space ($_POST['description'])) {
        View::$vars->data['description'] = htmlspecialchars (trim ($_POST['description']));
    } else {
        View::$vars->Errors['description'] = Language::GetText('error_description');
    }


    // Validate tags
    if (!empty ($_POST['tags']) && !ctype_space ($_POST['tags'])) {
        View::$vars->data['tags'] = htmlspecialchars (trim ($_POST['tags']));
    } else {
        View::$vars->Errors['tags'] = Language::GetText('error_tags');
    }


    // Validate cat_id
    if (!empty ($_POST['cat_id']) && is_numeric ($_POST['cat_id'])) {
        View::$vars->data['cat_id'] = $_POST['cat_id'];
    } else {
        View::$vars->Errors['cat_id'] = Language::GetText('error_category');
    }


    // Update video if no errors were made
    if (empty (View::$vars->Errors)) {
        View::$vars->video->Update (View::$vars->data);
        View::$vars->success = Language::GetText('success_video_updated');
        Plugin::Trigger ('edit_video.edit');
    } else {
        View::$vars->error_msg = Language::GetText('errors_below');
        View::$vars->error_msg .= '<br /><br /> - ' . implode ('<br /> - ', View::$vars->Errors);
    }

}



### Populate categories dropdown
$query = "SELECT cat_id, cat_name FROM " . DB_PREFIX . "categories";
View::$vars->result_cat = $db->Query ($query);



// Output page
Plugin::Trigger ('edit_video.before_render');
View::Render ('myaccount/edit_video.tpl');

?>