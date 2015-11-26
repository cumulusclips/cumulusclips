<?php

$this->view->options->disableView = true;
$keyword = null;
$suggestLimit = 9;

// Verify a keyword was given
if (!empty($_GET['term'])) {
    $keyword = $_GET['term'];
} else {
    App::Throw404();
}

// Retrieve count of all videos
$db = Registry::get('db');
$query = "SELECT COUNT(video_id) as total FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND private = '0'";
$resultTotal = $db->fetchRow($query);

// Retrieve total count
if ($resultTotal['total'] > 20 && strlen($keyword) > 3) {
    // Use FULLTEXT query
    $query = "SELECT title FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND private = '0' AND MATCH(title, tags, description) AGAINST(:keyword)";
    $db->fetchAll($query, array(':keyword' => $keyword));
} else {
    // Use LIKE query
    $keyword = '%' . $keyword . '%';
    $query = "SELECT title FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND private = '0' AND (title LIKE :keyword OR description LIKE :keyword OR tags LIKE :keyword)";
    $db->fetchAll($query, array(':keyword' => $keyword));
}

// Retrieve limited results
$query .= " LIMIT $suggestLimit";
$searchResult = $db->fetchAll($query, array(':keyword' => $keyword));
echo json_encode(Functions::arrayColumn($searchResult, 'title'));