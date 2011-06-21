<?php

### Created on February 28, 2009
### Created by Miguel A. Hurtado
### This script displays the site homepage


// Include required files
include_once (dirname ( dirname ( __FILE__ ) ) . '/cc-core/config/admin.bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Page');


// Establish page variables, objects, arrays, etc
Plugin::Trigger ('admin.videos.start');
//$logged_in = User::LoginCheck(HOST . '/login/');
//$admin = new User ($logged_in);





/***********************
HANDLE FORM IF SUBMITTED
***********************/

if (isset ($_POST['submitted'])) {

    // Validate page title
    if (!empty ($_POST['title']) && !ctype_space ($_POST['title'])) {
        $title = trim ($_POST['title']);
        $slug = Functions::CreateSlug ($title);
    } else {
        echo json_encode(array ('result' => 0, 'msg' => 'Invalid page title'));
        exit();
    }
        
    
    // Validate page id
    if (isset ($_POST['page_id']) && is_numeric ($_POST['page_id'])) {
        $page_id = $_POST['page_id'];
    } else {
        echo json_encode(array ('result' => 0, 'msg' => 'Invalid page id'));
        exit();
    }
    
    $exist_id = Page::Exist (array ('title' => $title));
    if ($exist_id && $exist_id == $page_id) {
        echo json_encode(array ('result' => 1, 'msg' => $slug));
    } else {
    }
    
}

?>