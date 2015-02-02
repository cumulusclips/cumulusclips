<?php

Plugin::triggerEvent('mobile_index.start');

// Verify if user is logged in
$userService = new UserService();
$this->view->vars->loggedInUser = $userService->loginCheck();
$this->view->vars->message = null;
$this->view->vars->messageType = null;
$username = null;
$password = null;

// Establish page variables, objects, arrays, etc
$videoMapper = new VideoMapper();
$config = Registry::get('config');
$db = Registry::get('db');
$this->view->vars->meta->title = Language::getText('mobile_heading', array('sitename' => $config->sitename));

// Retrieve Featured Video
$this->view->vars->featuredVideos = $videoMapper->getMultipleVideosByCustom(array(
    'status' => 'approved',
    'featured' => '1',
    'private' => '0',
    'gated' => '0'
));

// Retrieve Recent Videos
$query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND private = '0' AND gated = '0' ORDER BY video_id DESC LIMIT 3";
$recentResults = $db->fetchAll($query);
$this->view->vars->recentVideos = $videoMapper->getVideosFromList(
    Functions::arrayColumn($recentResults, 'video_id')
);

// Proccess logout
if (isset($_GET['logout']) && $this->view->vars->loggedInUser) {
    $userService->logout(); 
    $this->view->vars->loggedInUser = false;
    $this->view->vars->message = Language::getText('success_logout');
    $this->view->vars->messageType = 'success';
}

// Handle login form if submitted
if (isset($_POST['submitted'])) {

    // validate username
    if (!empty($_POST['username'])) {
        $username = trim($_POST['username']);
    }

    // validate password
    if (!empty($_POST['password'])) {
        $password = $_POST['password'];
    }
	
    // login if no errors were found
    if ($username && $password) {

        if ($userService->login($username, $password)) {
            $this->view->vars->loggedInUser = $userService->loginCheck();
            $this->view->vars->message = Language::getText('account_header') . ' - ' . $this->view->vars->loggedInUser->username;
            $this->view->vars->messageType = 'success';
        } else {
            $this->view->vars->message = Language::getText('error_invalid_login');
            $this->view->vars->messageType = 'errors';
        }

    } else {
        $this->view->vars->message = Language::getText('error_general');
        $this->view->vars->messageType = 'errors';
    }
}


Plugin::triggerEvent('mobile_index.end');