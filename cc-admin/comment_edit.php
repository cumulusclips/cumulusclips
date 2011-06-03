<?php

### Created on February 28, 2009
### Created by Miguel A. Hurtado
### This script displays the site homepage


// Include required files
include ('../cc-core/config/admin.bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Comment');


// Establish page variables, objects, arrays, etc
Plugin::Trigger ('admin.member_edit.start');
//$logged_in = User::LoginCheck(HOST . '/login/');
//$admin = new User ($logged_in);
$content = 'comment_edit.tpl';
$page_title = 'Edit Comment';
$data = array();
$Errors = array();
$message = null;



// Build return to list link
if (!empty ($_SESSION['list_page'])) {
    $list_page = $_SESSION['list_page'];
} else {
    $list_page = ADMIN . '/comments.php';
}



### Verify a record was provided
if (isset ($_GET['id']) && is_numeric ($_GET['id']) && $_GET['id'] != 0) {

    ### Retrieve record information
    $user = new Comment ($_GET['id']);
    if (!$user->found) {
        header ('Location: ' . ADMIN . '/comments.php');
        exit();
    }

} else {
    header ('Location: ' . ADMIN . '/comments.php');
    exit();
}





/***********************
Handle form if submitted
***********************/

if (isset ($_POST['submitted'])) {

    // Validate Name
    if (!empty ($_POST['name']) && !ctype_space ($_POST['name'])) {
        $data['name'] = htmlspecialchars ($_POST['name']);
    } else {
        $Errors['name'] = Language::GetText('error_name');
    }



    // Validate comments
    if (!empty ($_POST['comments']) && !ctype_space ($_POST['comments'])) {
        $data['comments'] = htmlspecialchars ( trim ($_POST['comments']));
    } else {
        $Errors['comments'] = Language::GetText('error_comment');
    }



    // Validate Email
    if (!empty ($_POST['email']) && !ctype_space ($_POST['email']) && preg_match ('/^[a-z0-9][a-z0-9_\.\-]+@[a-z0-9][a-z0-9\.\-]+\.[a-z0-9]{2,4}$/i',$_POST['email'])) {
        $data['email'] = $_POST['email'];
    } else {
        $Errors['email'] = Language::GetText('error_email');
    }



    // Validate Website
    if (!empty ($comment->website) && $_POST['website'] == '') {
        $data['website'] = '';
    } else if (!empty ($_POST['website']) && !ctype_space ($_POST['website'])) {
        $data['website'] = htmlspecialchars ($_POST['website']);
    }



    // Update record if no errors were found
    if (empty ($Errors)) {
        $message = 'Comment has been updated';
        $message_type = 'success';
        $comment->Update ($data);
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