<?php

// Include required files
include_once (dirname (dirname (__FILE__)) . '/cc-core/config/admin.bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Page');


// Establish page variables, objects, arrays, etc
Functions::RedirectIf ($logged_in = User::LoginCheck(), HOST . '/login/');
$admin = new User ($logged_in);
Functions::RedirectIf (User::CheckPermissions ('admin_panel', $admin), HOST . '/myaccount/');




// Validate Slug
if (!empty ($_POST['action']) && in_array ($_POST['action'], array ('slug','title'))) {

    if ($_POST['action'] == 'slug') {
        $slug = Functions::CreateSlug (trim ($_POST['slug']));
    } else if ($_POST['action'] == 'title') {
        $slug = Functions::CreateSlug (trim ($_POST['title']));
    } else {
        App::Throw404();
    }

} else {
    App::Throw404();
}


// Validate Page ID
if (isset ($_POST['page_id']) && $_POST['page_id'] == 0) {
    $page_id = 0;
} else if (!empty ($_POST['page_id']) && is_numeric ($_POST['page_id']) && Page::Exist (array ('page_id' => $_POST['page_id']))) {
    $page_id = $_POST['page_id'];
} else {
    App::Throw404();
}



$slug_page_id = Page::Exist (array ('slug' => $slug));






// If reserved
// If create & taken
// If update and & taken
if (Page::IsReserved ($slug) || ($slug_page_id && $slug_page_id !== $page_id)) {
    echo json_encode (array ('result' => 0, 'msg' => Page::GetAvailableSlug ($slug)));
}




// Slug Available
else {
    // OK
    echo json_encode (array ('result' => 1, 'msg' => $slug));
}

?>