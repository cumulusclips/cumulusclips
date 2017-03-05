<?php

// Verify if user registrations are enabled
$config = Registry::get('config');
if (!$config->enableUserUploads) App::throw404();

Plugin::triggerEvent('mobile_my_videos.start');
Functions::redirectIf((boolean) Settings::get('mobile_site'), HOST . '/');

// Verify if user is logged in
$this->authService->enforceAuth();
$this->authService->enforceTimeout(true);
$this->view->vars->loggedInUser = $this->authService->getAuthUser();

// Establish page variables, objects, arrays, etc
$videoMapper = new VideoMapper();
$db = Registry::get('db');

// Retrieve video count
$query = "SELECT COUNT(video_id) FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND user_id = ?";
$db->fetchRow($query, array($this->view->vars->loggedInUser->userId));
$this->view->vars->count = $db->rowCount();

// Retrieve video list
$query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND user_id = ? ORDER BY video_id DESC LIMIT 20";
$this->view->vars->videos = array();
$videoResults = $db->fetchAll($query, array($this->view->vars->loggedInUser->userId));
$this->view->vars->videos = $videoMapper->getVideosFromList(
    Functions::arrayColumn($videoResults, 'video_id')
);

Plugin::triggerEvent('mobile_my_videos.end');