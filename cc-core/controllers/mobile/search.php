<?php

Plugin::triggerEvent('mobile_search.start');
Functions::redirectIf((boolean) Settings::get('mobile_site'), HOST . '/');

// Verify if user is logged in
$this->authService->enforceTimeout();
$this->view->vars->loggedInUser = $this->authService->getAuthUser();

// Establish page variables, objects, arrays, etc
$videoMapper = new VideoMapper();
$db = Registry::get('db');
$this->view->vars->keyword = null;

// Handle form if submitted
if (!empty($_POST['keyword'])) {

    $keyword = $_POST['keyword'];
    $this->view->vars->keyword = $keyword;

    // Retrieve count of all videos
    $query = "SELECT COUNT(video_id) as total FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND private = '0'";
    $resultTotal = $db->fetchRow($query);
    $total = (int) $resultTotal['total'];

    // Retrieve total count
    if ($total > 20 && strlen($keyword) > 3) {
        // Use FULLTEXT query
        $queryCount = "SELECT COUNT(video_id) AS count FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND private = '0' AND MATCH(title, tags, description) AGAINST(:keyword)";
        $query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND private = '0' AND MATCH(title, tags, description) AGAINST(:keyword) LIMIT 20";
    } else {
        // Use LIKE query
        $keyword = '%' . $keyword . '%';
        $queryCount = "SELECT COUNT(video_id) AS count FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND private = '0' AND (title LIKE :keyword OR description LIKE :keyword OR tags LIKE :keyword)";
        $query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND private = '0' AND (title LIKE :keyword OR description LIKE :keyword OR tags LIKE :keyword) LIMIT 20";
    }

    // Retrieve search results
    $resultsCount = $db->fetchRow($queryCount, array(':keyword' => $keyword));
    $this->view->vars->count = (int) $resultsCount['count'];
    $searchResults = $db->fetchAll($query, array(':keyword' => $keyword));
    $this->view->vars->searchVideos = $videoMapper->getVideosFromList(
        Functions::arrayColumn($searchResults, 'video_id')
    );
}

Plugin::triggerEvent('mobile_search.end');