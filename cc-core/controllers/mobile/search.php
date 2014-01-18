<?php

// Init view
$view->initView('mobile_search', true);
Plugin::triggerEvent('mobile_search.start');

// Verify if user is logged in
$userService = new UserService();
$view->vars->loggedInUser = $userService->loginCheck();

// Establish page variables, objects, arrays, etc
$videoMapper = new VideoMapper();

// Handle form if submitted
if (!empty ($_POST['keyword']) && !ctype_space ($_POST['keyword'])) {

    // Retrieve search result count
    $view->vars->keyword = $keyword = trim($_POST['keyword']);
    $query = "SELECT COUNT(video_id) FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND private = '0' AND gated = '0' AND MATCH(title, tags, description) AGAINST(:keyword)";
    $db->fetchRow($query, array(':keyword' => $keyword));
    $view->vars->count = $db->rowCount();

    // Retrieve search result video list
    $query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND private = '0' AND gated = '0' AND MATCH(title, tags, description) AGAINST(:keyword) LIMIT 20";
    $videoResults = $db->fetchAll($query, array(':keyword' => $keyword));
    $view->vars->searchVideos = $videoMapper->getVideosFromList(
        Functions::arrayColumn($videoResults, 'video_id')
    );
}

// Output Page
Plugin::Trigger ('mobile_search.before_render');
$view->Render ('search.tpl');