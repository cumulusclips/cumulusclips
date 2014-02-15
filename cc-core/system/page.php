<?php

// Establish page variables, objects, arrays, etc
$view->InitView();
Plugin::triggerEvent('page.start');

// Verify if user is logged in
$userService = new UserService();
$view->vars->loggedInUser = $userService->loginCheck();

$pageMapper = new PageMapper();
if (!empty($_GET['preview']) && is_numeric($_GET['preview'])) {
    // Parse preview request
    $page = $pageMapper->getPageById($_GET['preview']);
} else {
    // Parse the URI request
    $page = $pageMapper->getPageByCustom(array('slug' => trim($router->getRequestUri(), '/'), 'status' => 'published'));
}

// Validate requested page
if ($page) {
    $page_name = 'page_' . $page->slug;
    $view->vars->page = $page;
    $view->options->page = $page_name;
    $view->vars->meta = Language::GetMeta ($page_name);
    if (empty($view->vars->meta->title)) $view->vars->meta->title = $page->title;
} else {
    App::Throw404();
}

// Output Page
Plugin::triggerEvent('page.before_render');
$view->Render ('page.tpl');