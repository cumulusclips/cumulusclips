<?php

Plugin::triggerEvent('videos_edit.start');

// Verify if user registrations are enabled
$config = Registry::get('config');
if (!$config->enableUserUploads) App::throw404();

// Verify if user is logged in
$userService = new UserService();
$this->view->vars->loggedInUser = $userService->loginCheck();
Functions::RedirectIf($this->view->vars->loggedInUser, HOST . '/login/');

// Establish page variables, objects, arrays, etc
$videoMapper = new VideoMapper();
$videoService = new VideoService();
$this->view->vars->privateUrl = $videoService->generatePrivate();
$this->view->vars->errors = array();
$this->view->vars->message = null;

// Verify a video was provided
if (!empty($_GET['vid']) && $_GET['vid'] > 0) {

    // Retrieve video information
    $video = $videoMapper->getVideoByCustom(array(
        'user_id' => $this->view->vars->loggedInUser->userId,
        'video_id' => $_GET['vid']
    ));
    
    // Verify video is valid
    if (!$video) {
        header('Location: ' . HOST . '/account/videos/');
        exit();
    }
} else {
    header('Location: ' . HOST . '/account/videos/');
    exit();
}

// Handle form if submitted
if (isset($_POST['submitted'])) {

    // Validate title
    if (!empty($_POST['title']) && !ctype_space($_POST['title'])) {
        $video->title = trim($_POST['title']);
    } else {
        $this->view->vars->errors['title'] = Language::getText('error_title');
    }

    // Validate description
    if (!empty($_POST['description']) && !ctype_space($_POST['description'])) {
        $video->description = trim($_POST['description']);
    } else {
        $this->view->vars->errors['description'] = Language::getText('error_description');
    }

    // Validate tags
    if (!empty($_POST['tags']) && !ctype_space($_POST['tags'])) {
        $video->tags = preg_split('/,\s*/', trim($_POST['tags']));
    } else {
        $this->view->vars->errors['tags'] = Language::getText('error_tags');
    }

    // Validate cat_id
    if (!empty($_POST['cat_id']) && is_numeric($_POST['cat_id'])) {
        $video->categoryId = $_POST['cat_id'];
    } else {
        $this->view->vars->errors['cat_id'] = Language::getText('error_category');
    }

    // Validate disable embed
    if (!empty($_POST['disable_embed']) && $_POST['disable_embed'] == '1') {
        $video->disableEmbed = true;
    } else {
        $video->disableEmbed = false;
    }

    // Validate gated
    if (!empty($_POST['gated']) && $_POST['gated'] == '1') {
        $video->gated = true;
    } else {
        $video->gated = false;
    }

    // Validate private
    if (!empty($_POST['private']) && $_POST['private'] == '1') {
        try {
            // Validate private URL
            if (empty($_POST['private_url'])) throw new Exception();
            if (strlen($_POST['private_url']) != 7) throw new Exception();
            $privateVideoCheck = $videoMapper->getVideoByCustom(array('private_url' => $_POST['private_url']));
            if ($privateVideoCheck && $privateVideoCheck->videoId != $video->videoId) {
                throw new Exception();
            }

            // Set private URL
            $video->private = true;
            $video->privateUrl = trim($_POST['private_url']);
        } catch (Exception $e) {
            $this->view->vars->errors['private_url'] = Language::getText('error_private_url');
        }
    } else {
        $video->private = false;
        $video->privateUrl = null;
    }

    // Validate close comments
    if (!empty($_POST['closeComments']) && $_POST['closeComments'] == '1') {
        $video->commentsClosed = true;
    } else {
        $video->commentsClosed = false;
    }

    // Update video if no errors were made
    if (empty ($this->view->vars->errors)) {
        $videoMapper->save($video);
        $this->view->vars->message = Language::getText('success_video_updated');
        $this->view->vars->message_type = 'success';
    } else {
        $this->view->vars->message = Language::getText('errors_below');
        $this->view->vars->message .= '<br /><br /> - ' . implode('<br /> - ', $this->view->vars->errors);
        $this->view->vars->message_type = 'errors';
    }
}

// Retrieve Categories	
$categoryService = new CategoryService();
$this->view->vars->categoryList = $categoryService->getCategories();

$this->view->vars->video = $video;
Plugin::triggerEvent('videos_edit.end');