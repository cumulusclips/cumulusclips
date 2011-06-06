<?php

### Created on February 28, 2009
### Created by Miguel A. Hurtado
### This script displays the site homepage


// Include required files
include ('../cc-core/config/admin.bootstrap.php');
App::LoadClass ('User');


// Establish page variables, objects, arrays, etc
Plugin::Trigger ('admin.member_edit.start');
//$logged_in = User::LoginCheck(HOST . '/login/');
//$admin = new User ($logged_in);
$content = 'member_edit.tpl';
$page_title = 'Edit Member';
$data = array();
$Errors = array();
$message = null;



// Build return to list link
if (!empty ($_SESSION['list_page'])) {
    $list_page = $_SESSION['list_page'];
} else {
    $list_page = ADMIN . '/member.php';
}



### Verify a member was provided
if (isset ($_GET['id']) && is_numeric ($_GET['id']) && $_GET['id'] != 0) {

    ### Retrieve member information
    $user = new User ($_GET['id']);
    if (!$user->found) {
        header ('Location: ' . ADMIN . '/members.php');
        exit();
    }

} else {
    header ('Location: ' . ADMIN . '/members.php');
    exit();
}





/***********************
Handle form if submitted
***********************/

if (isset ($_POST['submitted'])) {

    // Validate First Name
    if (!empty ($user->first_name) && $_POST['first_name'] == '') {
        $data['first_name'] = '';
    } elseif (!empty ($_POST['first_name']) && !ctype_space ($_POST['first_name'])) {
        $data['first_name'] = htmlspecialchars ($_POST['first_name']);
    }



    // Validate Last Name
    if (!empty ($user->last_name) && $_POST['last_name'] == '') {
        $data['last_name'] = '';
    } elseif (!empty ($_POST['last_name']) && !ctype_space ($_POST['last_name'])) {
        $data['last_name'] = htmlspecialchars ($_POST['last_name']);
    }



    // Validate Email
    if (!empty ($_POST['email']) && !ctype_space ($_POST['email']) && preg_match ('/^[a-z0-9][a-z0-9_\.\-]+@[a-z0-9][a-z0-9\.\-]+\.[a-z0-9]{2,4}$/i',$_POST['email'])) {
        $email = array ('email' => $_POST['email']);
        $id = User::Exist ($email);
        if (!$id || $id == $user->user_id) {
            $data['email'] = $_POST['email'];
        } else {
            $Errors['email'] = Language::GetText('error_email_unavailable');
        }

    } else {
        $Errors['email'] = Language::GetText('error_email');
    }



    // Validate Website
    if (!empty ($user->website) && $_POST['website'] == '') {
        $data['website'] = '';
    } elseif (!empty ($_POST['website']) && !ctype_space ($_POST['website'])) {
        $data['website'] = htmlspecialchars ($_POST['website']);
    }



    // Validate About Me
    if (!empty ($user->about_me) && $_POST['about_me'] == '') {
        $data['about_me'] = '';
    } elseif (!empty ($_POST['about_me']) && !ctype_space ($_POST['about_me'])) {
        $data['about_me'] = htmlspecialchars ($_POST['about_me']);
    }



    // Validate status
    if (!empty ($_POST['status']) && !ctype_space ($_POST['status'])) {
        $data['status'] = htmlspecialchars (trim ($_POST['status']));
    } else {
        $Errors['status'] = 'Invalid status';
    }



    // Update User if no errors were found
    if (empty ($Errors)) {

        // Perform addional actions based on status change
        if ($data['status'] != $user->status) {

            switch ($data['status']) {

                // Handle "Approve" action
                case 'active':
                    $user->UpdateContentStatus ('active');
                    $user->Approve (true);
                    break;


                // Handle "Ban" action
                case 'banned':
                    $user->UpdateContentStatus ('banned');
                    Flag::FlagDecision ($user->user_id, 'user', true);
                    break;


                // Handle "Pending" or "New" action
                case 'new':
                case 'pending':
                    $user->UpdateContentStatus ($data['status']);
                    break;

            }

        }

        $message = Language::GetText('success_profile_updated');
        $message_type = 'success';
        $user->Update ($data);
        Plugin::Trigger ('admin.member_edit.update_member');
    } else {
        $message = Language::GetText('errors_below');
        $message .= '<br /><br /> - ' . implode ('<br /> - ', $Errors);
        $message_type = 'error';
    }

}


// Output Page
include (THEME_PATH . '/admin.layout.tpl');

?>