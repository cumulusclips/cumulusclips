<?php

// Init application
include_once(dirname(dirname(__FILE__)) . '/cc-core/system/admin.bootstrap.php');

// Verify user can access admin panel
$userService = new \UserService();
Functions::RedirectIf($userService->checkPermissions('admin_panel', $adminUser), HOST . '/account/');

// Establish page variables, objects, arrays, etc
$pageMapper = new PageMapper();
$pageService = new PageService();




// Validate Slug
if (!empty ($_POST['action']) && in_array ($_POST['action'], array ('slug','title'))) {

    if ($_POST['action'] == 'slug') {
        $slug = Functions::CreateSlug (trim ($_POST['slug']));
    } else {
        $slug = Functions::CreateSlug (trim ($_POST['title']));
    }

} else {
    App::Throw404();
}


// Validate Page ID
if (empty($_POST['page_id'])) {
    $page_id = 0;
} else if (!empty ($_POST['page_id']) && is_numeric ($_POST['page_id']) && $pageMapper->getPageById($_POST['page_id'])) {
    $page_id = $_POST['page_id'];
} else {
    App::Throw404();
}


// If reserved
// If create & taken
// If update and & taken
$page = $pageMapper->getPageBySlug($slug);
if ($pageService->isReserved ($slug) || ($page && $page->pageId != $page_id)) {
    echo json_encode (array ('result' => 1, 'msg' => $pageService->getAvailableSlug ($slug)));
}




// Slug Available
else {
    // OK
    echo json_encode (array ('result' => 1, 'msg' => $slug));
}