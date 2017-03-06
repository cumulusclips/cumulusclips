<?php

Plugin::triggerEvent('search.start');

// Verify if user is logged in
$this->authService->enforceTimeout();
$this->view->vars->loggedInUser = $this->authService->getAuthUser();

// Establish page variables, objects, arrays, etc
$keyword = null;
$this->view->vars->cleaned = null;
$url = HOST . '/search';
$query_string = array();
$records_per_page = 9;
$db = Registry::get('db');

// Verify a keyword was given
if (isset ($_POST['submitted_search'])) {
    $this->view->vars->cleaned = htmlspecialchars($_POST['keyword']);
    $keyword = $_POST['keyword'];
} elseif (isset ($_GET['keyword'])) {
    $this->view->vars->cleaned = htmlspecialchars($_GET['keyword']);
    $keyword = $_GET['keyword'];
}

$query_string['keyword'] = $this->view->vars->cleaned;
$this->view->vars->meta->title = Functions::Replace($this->view->vars->meta->title, array('keyword' => $this->view->vars->cleaned));

// Retrieve count of all videos
$query = "SELECT COUNT(video_id) as total FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND private = '0'";
$resultTotal = $db->fetchRow($query);

// Retrieve total count
if ($resultTotal['total'] > 20 && strlen($keyword) > 3) {
    // Use FULLTEXT query
    $query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND private = '0' AND MATCH(title, tags, description) AGAINST(:keyword)";
    $db->fetchAll($query, array(':keyword' => $keyword));
    $count = $db->rowCount();
} else {
    // Use LIKE query
    $keyword = '%' . $keyword . '%';
    $query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND private = '0' AND (title LIKE :keyword OR description LIKE :keyword OR tags LIKE :keyword)";
    $db->fetchAll($query, array(':keyword' => $keyword));
    $count = $db->rowCount();
}

// Initialize pagination
$url .= (!empty ($query_string)) ? '?' . http_build_query($query_string) : '';
$this->view->vars->pagination = new Pagination ($url, $count, $records_per_page);
$start_record = $this->view->vars->pagination->GetStartRecord();

// Retrieve limited results
$query .= " LIMIT $start_record, $records_per_page";
$searchResult = $db->fetchAll($query, array(':keyword' => $keyword));
$videoMapper = new VideoMapper();
$this->view->vars->search_videos = $videoMapper->getVideosFromList(
    Functions::arrayColumn($searchResult, 'video_id')
);

Plugin::triggerEvent('search.end');