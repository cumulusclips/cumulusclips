<?php

// Include required files
include_once (dirname (dirname (dirname (__FILE__))) . '/config/bootstrap.php');
App::LoadClass ('User');


// Establish page variables, objects, arrays, etc
View::InitView ('update_profile');
Plugin::Trigger ('update_profile.start');
Functions::RedirectIf (View::$vars->logged_in = User::LoginCheck(), HOST . '/login/');
View::$vars->user = new User (View::$vars->logged_in);
View::$vars->Errors = array();
View::$vars->message = null;
View::$vars->timestamp = time();
$_SESSION['upload_key'] = md5 (md5 (View::$vars->timestamp) . SECRET_KEY);
$duplicate = NULL;





/**************************
 * Handle Form if submitted
 *************************/

if (isset ($_POST['submitted'])) {

    // Validate First Name
    if (!empty (View::$vars->user->first_name) && $_POST['first_name'] == '') {
        View::$vars->data['first_name'] = '';
    } elseif (!empty ($_POST['first_name']) && !ctype_space ($_POST['first_name'])) {
        View::$vars->data['first_name'] = htmlspecialchars ($_POST['first_name']);
    }


    // Validate Last Name
    if (!empty (View::$vars->user->last_name) && $_POST['last_name'] == '') {
        View::$vars->data['last_name'] = '';
    } elseif (!empty ($_POST['last_name']) && !ctype_space ($_POST['last_name'])) {
        View::$vars->data['last_name'] = htmlspecialchars ($_POST['last_name']);
    }


    // Validate Email
    if (!empty ($_POST['email']) && !ctype_space ($_POST['email']) && preg_match ('/^[a-z0-9][a-z0-9_\.\-]+@[a-z0-9][a-z0-9\.\-]+\.[a-z0-9]{2,4}$/i',$_POST['email'])) {
        $email = array ('email' => $_POST['email']);
        $id = User::Exist ($email);
        if (!$id || $id == View::$vars->user->user_id) {
            View::$vars->data['email'] = $_POST['email'];
        } else {
            View::$vars->Errors['email'] = Language::GetText('error_email_unavailable');
        }

    } else {
        View::$vars->Errors['email'] = Language::GetText('error_email');
    }


    // Validate Website
    if (!empty ($_POST['website']) && !ctype_space ($_POST['website'])) {
        $website = $_POST['website'];
        if (preg_match ('/^(https?:\/\/)?[a-z0-9][a-z0-9\.-]+\.[a-z0-9]{2,4}.*$/i', $website, $matches)) {
            $website = (empty ($matches[1]) ? 'http://' : '') . $website;
            View::$vars->data['website'] = htmlspecialchars (trim ($website));
        } else {
            View::$vars->errors['website'] = Language::GetText('error_website_invalid');
        }
    } else {
        View::$vars->data['website'] = '';
    }


    // Validate About Me
    if (!empty (View::$vars->user->about_me) && $_POST['about_me'] == '') {
        View::$vars->data['about_me'] = '';
    } elseif (!empty ($_POST['about_me']) && !ctype_space ($_POST['about_me'])) {
        View::$vars->data['about_me'] = htmlspecialchars ($_POST['about_me']);
    }



    // Update User if no errors were found
    if (empty (View::$vars->Errors)) {
        View::$vars->message = Language::GetText('success_profile_updated');
        View::$vars->message_type = 'success';
        View::$vars->user->Update (View::$vars->data);
        Plugin::Trigger ('update_profile.update_profile');
    } else {
        View::$vars->message = Language::GetText('errors_below');
        View::$vars->message .= '<br /><br /> - ' . implode ('<br /> - ', View::$vars->Errors);
        View::$vars->message_type = 'error';
    }



} // END Handle Profile form





/**************************
Handle Reset Avatar Action
**************************/

if (!empty ($_GET['action']) && $_GET['action'] == 'reset' && !empty (View::$vars->user->avatar)) {
    try {
        Filesystem::Open();
        Filesystem::Delete (UPLOAD_PATH . '/avatars/' . View::$vars->user->avatar);
        Filesystem::Close();
    } catch (Exception $e) {
        App::Alert('Error during Avatar Reset', $e->getMessage());
    }
    View::$vars->user->Update (array ('avatar' => ''));
    View::$vars->message = Language::GetText('success_avatar_reset');
    View::$vars->message_type = 'success';
    Plugin::Trigger ('update_profile.avatar_reset');
}


// Output page
Plugin::Trigger ('update_profile.before_render');
View::Render ('myaccount/update_profile.tpl');

?>