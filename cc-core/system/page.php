<?php

// Establish page variables, objects, arrays, etc
View::InitView();
Plugin::triggerEvent('page.start');

// Verify if user is logged in
$userService = new UserService();
View::$vars->loggedInUser = $userService->loginCheck();

$page_id = null;

$pageMapper = new PageMapper();
if (!empty($_GET['preview']) && is_numeric($_GET['preview'])) {
    // Parse preview request
    $page_id = $pageMapper->getPageById($_GET['preview']);
} else {
    // Parse the URI request
    $page = $pageMapper->getPageByCustom(array('slug' => trim($router->getRequestUri(), '/'), 'status' => 'published'));
}

// Validate requested page
if ($page) {
    $page_name = 'page_' . $page->slug;
    View::$vars->page = $page;
    View::$options->page = $page_name;
    View::$vars->meta = Language::GetMeta ($page_name);
    if (empty(View::$vars->meta->title)) View::$vars->meta->title = $page->title;
} else {
    App::Throw404();
}

// Output Page
Plugin::triggerEvent('page.before_render');
View::Render ('page.tpl');