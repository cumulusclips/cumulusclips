<?php

// Include required files
include_once (dirname (dirname (dirname (__FILE__))) . '/config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Video');


// Establish page variables, objects, arrays, etc
View::InitView ('upload');
Plugin::Trigger ('upload.start');
Functions::RedirectIf (View::$vars->logged_in = User::LoginCheck(), HOST . '/login/');
App::EnableUploadsCheck();
View::$vars->user = new User (View::$vars->logged_in);
View::$vars->categories = NULL;
View::$vars->data = array();
View::$vars->errors = array();
View::$vars->message = null;
View::$vars->private_url = Video::GeneratePrivate();
unset ($_SESSION['upload']);



### Retrieve categories for drop down
$query = "SELECT cat_id, cat_name FROM " . DB_PREFIX . "categories";
View::$vars->result_cat = $db->Query ($query);





/******************************
Handle upload form if submitted
******************************/

if (isset ($_POST['submitted'])) {

    // Validate Title
    if (!empty ($_POST['title']) && !ctype_space ($_POST['title'])) {
        View::$vars->data['title'] = htmlspecialchars (trim ($_POST['title']));
    } else {
        View::$vars->errors['title'] = Language::GetText('error_title');
    }


    // Validate Description
    if (!empty ($_POST['description']) && !ctype_space ($_POST['description'])) {
        View::$vars->data['description'] = htmlspecialchars (trim ($_POST['description']));
    } else {
        View::$vars->errors['description'] = Language::GetText('error_description');
    }


    // Validate Tags
    if (!empty ($_POST['tags']) && !ctype_space ($_POST['tags'])) {
        View::$vars->data['tags'] = htmlspecialchars (trim ($_POST['tags']));
    } else {
        View::$vars->errors['tags'] = Language::GetText('error_tags');
    }


    // Validate Category
    if (!empty ($_POST['cat_id']) && !ctype_space ($_POST['cat_id'])) {
        View::$vars->data['cat_id'] = $_POST['cat_id'];
    } else {
        View::$vars->errors['cat_id'] = Language::GetText('error_category');
    }


    // Validate disable embed
    if (!empty ($_POST['disable_embed']) && $_POST['disable_embed'] == '1') {
        View::$vars->data['disable_embed'] = '1';
    } else {
        View::$vars->data['disable_embed'] = '0';
    }


    // Validate gated
    if (!empty ($_POST['gated']) && $_POST['gated'] == '1') {
        View::$vars->data['gated'] = '1';
    } else {
        View::$vars->data['gated'] = '0';
    }


    // Validate private
    if (!empty ($_POST['private']) && $_POST['private'] == '1') {
        View::$vars->data['private'] = '1';
        if (!empty ($_POST['private_url']) && strlen ($_POST['private_url']) == 7 && !Video::Exist (array ('private_url' => $_POST['private_url']))) {
            View::$vars->data['private_url'] = htmlspecialchars (trim ($_POST['private_url']));
            View::$vars->private_url = View::$vars->data['private_url'];

        } else {
            View::$vars->errors['private_url'] = Language::GetText('error_private_url');
        }
    } else {
        View::$vars->data['private'] = '0';
    }




    // Validate Video Upload last (only if other fields were valid)
    if (empty (View::$vars->errors)) {
        View::$vars->data['user_id'] = View::$vars->user->user_id;
        View::$vars->data['filename'] = Video::CreateFilename();
        View::$vars->data['status'] = 'new';
        Plugin::Trigger ('upload.before_create_video');
        $_SESSION['upload'] = Video::Create (View::$vars->data);
        Plugin::Trigger ('upload.create_video');
        header ('Location: ' . HOST . '/myaccount/upload/video/');
        exit();
    } else {
        View::$vars->message = Language::GetText('errors_below');
        View::$vars->message .= '<br /><br /> - ' . implode ('<br /> - ', View::$vars->errors);
        View::$vars->message_type = 'errors';
    }

}


// Output page
Plugin::Trigger ('upload.before_render');
View::Render ('myaccount/upload.tpl');

?>