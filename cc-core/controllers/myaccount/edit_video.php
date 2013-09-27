<?php

// Establish page variables, objects, arrays, etc
View::InitView ('edit_video');
Plugin::Trigger ('edit_video.start');
Functions::RedirectIf (View::$vars->logged_in = UserService::LoginCheck(), HOST . '/login/');
View::$vars->user = new User (View::$vars->logged_in);
View::$vars->private_url = Video::GeneratePrivate();
View::$vars->errors = array();
View::$vars->message = null;



### Verify a video was provided
if (isset ($_GET['vid']) && is_numeric ($_GET['vid']) && $_GET['vid'] != 0) {

    ### Retrieve video information
    View::$vars->data = array ('user_id' => View::$vars->user->user_id, 'video_id' => $_GET['vid']);
    $id = Video::Exist(View::$vars->data);
    if ($id) {
        View::$vars->video = new Video ($id);
        if (View::$vars->video->private == '1') View::$vars->private_url = View::$vars->video->private_url;
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
        View::$vars->errors['title'] = Language::GetText('error_title');
    }


    // Validate description
    if (!empty ($_POST['description']) && !ctype_space ($_POST['description'])) {
        View::$vars->data['description'] = htmlspecialchars (trim ($_POST['description']));
    } else {
        View::$vars->errors['description'] = Language::GetText('error_description');
    }


    // Validate tags
    if (!empty ($_POST['tags']) && !ctype_space ($_POST['tags'])) {
        View::$vars->data['tags'] = htmlspecialchars (trim ($_POST['tags']));
    } else {
        View::$vars->errors['tags'] = Language::GetText('error_tags');
    }


    // Validate cat_id
    if (!empty ($_POST['cat_id']) && is_numeric ($_POST['cat_id'])) {
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

        try {

            // Validate private URL
            if (empty ($_POST['private_url'])) throw new Exception ('error');
            if (strlen ($_POST['private_url']) != 7) throw new Exception ('error');
            $vid = Video::Exist (array ('private_url' => $_POST['private_url']));
            if ($vid && $vid != View::$vars->video->video_id) throw new Exception ('error');

            // Set private URL
            View::$vars->data['private_url'] = htmlspecialchars (trim ($_POST['private_url']));
            View::$vars->private_url = View::$vars->data['private_url'];
            
        } catch (Exception $e) {
            View::$vars->errors['private_url'] = Language::GetText('error_private_url');
        }

    } else {
        View::$vars->data['private'] = '0';
        View::$vars->data['private_url'] = '';
        View::$vars->private_url = Video::GeneratePrivate();
    }


    // Update video if no errors were made
    if (empty (View::$vars->errors)) {
        View::$vars->video->Update (View::$vars->data);
        View::$vars->message = Language::GetText('success_video_updated');
        if (View::$vars->video->private == '1') View::$vars->private_url = View::$vars->video->private_url;
        View::$vars->message_type = 'success';
        Plugin::Trigger ('edit_video.edit');
    } else {
        View::$vars->message = Language::GetText('errors_below');
        View::$vars->message .= '<br /><br /> - ' . implode ('<br /> - ', View::$vars->errors);
        View::$vars->message_type = 'errors';
    }

}



### Populate categories dropdown
$query = "SELECT cat_id, cat_name FROM " . DB_PREFIX . "categories";
View::$vars->result_cat = $db->Query ($query);



// Output page
Plugin::Trigger ('edit_video.before_render');
View::Render ('myaccount/edit_video.tpl');