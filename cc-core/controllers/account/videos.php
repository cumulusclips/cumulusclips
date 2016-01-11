<?php

Plugin::triggerEvent('myvideos.start');

// Verify if user registrations are enabled
$config = Registry::get('config');
if (!$config->enableUserUploads) App::throw404();

// Verify if user is logged in
$userService = new UserService();
$this->view->vars->loggedInUser = $userService->loginCheck();
Functions::RedirectIf($this->view->vars->loggedInUser, HOST . '/login/');

// Establish page variables, objects, arrays, etc
$records_per_page = 9;
$url = HOST . '/account/videos';
$this->view->vars->message = null;
$videoMapper = new VideoMapper();
$db = Registry::get('db');

// Delete video if requested
if (!empty($_GET['vid'])) {
    $video = $videoMapper->getVideoByCustom(array(
        'user_id' => $this->view->vars->loggedInUser->userId,
        'video_id' => $_GET['vid']
    ));
    if ($video) {
        $videoService = new VideoService();
        $videoService->delete($video);
        $this->view->vars->message = Language::GetText('success_video_deleted');
        $this->view->vars->message_type = 'success';
    }
}

// Retrieve total count
$query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE user_id = ? AND status IN (?, ?, ?, ?) ORDER BY date_created DESC";
$bindParams = array(
    $this->view->vars->loggedInUser->userId,
    VideoMapper::APPROVED,
    VideoMapper::PROCESSING,
    VideoMapper::PENDING_CONVERSION,
    VideoMapper::PENDING_APPROVAL
);
$db->fetchAll($query, $bindParams);
$total = $db->rowCount();

// Initialize pagination
$this->view->vars->pagination = new Pagination($url, $total, $records_per_page);
$start_record = $this->view->vars->pagination->GetStartRecord();

// Retrieve limited results
$query .= " LIMIT $start_record, $records_per_page";
$resultVideos = $db->fetchAll($query, $bindParams);
$this->view->vars->userVideos = $videoMapper->getVideosFromList(
    Functions::arrayColumn($resultVideos, 'video_id')
);

Plugin::triggerEvent('myvideos.end');