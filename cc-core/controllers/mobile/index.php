<?php

Plugin::triggerEvent('mobile_index.start');
Functions::redirectIf((boolean) Settings::get('mobile_site'), HOST . '/');

// Verify if user is logged in
$this->authService->enforceTimeout();
$this->view->vars->loggedInUser = $this->authService->getAuthUser();

// Establish page variables, objects, arrays, etc
$userService = new UserService();
$this->view->vars->message = null;
$this->view->vars->messageType = null;
$videoMapper = new VideoMapper();
$config = Registry::get('config');
$db = Registry::get('db');
$this->view->vars->meta->title = Language::getText('mobile_heading', array('sitename' => $config->sitename));

// Retrieve Featured Video
$this->view->vars->featuredVideos = $videoMapper->getMultipleVideosByCustom(array(
    'status' => 'approved',
    'featured' => '1',
    'private' => '0'
));

// Retrieve Recent Videos
$query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND private = '0' ORDER BY video_id DESC LIMIT 3";
$recentResults = $db->fetchAll($query);
$this->view->vars->recentVideos = $videoMapper->getVideosFromList(
    Functions::arrayColumn($recentResults, 'video_id')
);

// Display welcome message
if (isset($_GET['welcome']) && $this->view->vars->loggedInUser) {
    $this->view->vars->message = Language::getText('account_header') . ' - ' . $this->view->vars->loggedInUser->username;
    $this->view->vars->messageType = 'success';
}

// Proccess logout
if (isset($_GET['logout']) && $this->view->vars->loggedInUser) {
    $userService->logout();
    $this->view->vars->loggedInUser = false;
    $this->view->vars->message = Language::getText('success_logout');
    $this->view->vars->messageType = 'success';
}

Plugin::triggerEvent('mobile_index.end');