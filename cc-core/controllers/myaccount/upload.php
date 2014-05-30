<?php

Plugin::triggerEvent('upload.start');

// Verify if user is logged in
$userService = new UserService();
$view->vars->loggedInUser = $userService->loginCheck();
Functions::RedirectIf($view->vars->loggedInUser, HOST . '/login/');

// Establish page variables, objects, arrays, etc
App::EnableUploadsCheck();
$view->vars->categories = null;
$view->vars->data = array();
$view->vars->errors = array();
$view->vars->message = null;
$videoService = new VideoService();
$videoMapper = new VideoMapper();
$video = new Video();
$view->vars->privateUrl = $videoService->generatePrivate();
unset($_SESSION['upload']);

// Retrieve Categories	
$categoryService = new CategoryService();
$view->vars->categoryList = $categoryService->getCategories();

// Handle upload form if submitted
if (isset ($_POST['submitted'])) {

    // Validate Title
    if (!empty($_POST['title']) && !ctype_space($_POST['title'])) {
        $video->title = trim($_POST['title']);
    } else {
        $view->vars->errors['title'] = Language::getText('error_title');
    }

    // Validate Description
    if (!empty($_POST['description']) && !ctype_space($_POST['description'])) {
        $video->description = trim($_POST['description']);
    } else {
        $view->vars->errors['description'] = Language::getText('error_description');
    }

    // Validate Tags
    if (!empty($_POST['tags']) && !ctype_space($_POST['tags'])) {
        $video->tags = preg_split('/,\s*/', trim($_POST['tags']));
    } else {
        $view->vars->errors['tags'] = Language::getText('error_tags');
    }

    // Validate Category
    if (!empty($_POST['cat_id']) && is_numeric($_POST['cat_id'])) {
        $video->categoryId = $_POST['cat_id'];
    } else {
        $view->vars->errors['cat_id'] = Language::getText('error_category');
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
            if ($videoMapper->getVideoByCustom(array('private_url' => $_POST['private_url']))) throw new Exception();

            // Set private URL
            $video->private = true;
            $video->privateUrl = trim($_POST['private_url']);
        } catch (Exception $e) {
            $view->vars->errors['private_url'] = Language::getText('error_private_url');
        }
    } else {
        $video->private = false;
    }

    // Validate Video Upload last (only if other fields were valid)
    if (empty($view->vars->errors)) {
        $video->userId = $view->vars->loggedInUser->userId;
        $video->filename = $videoService->generateFilename();
        $video->status = 'new';
        $_SESSION['upload'] = $videoMapper->save($video);
        Plugin::triggerEvent('upload.create_video');
        header('Location: ' . HOST . '/myaccount/upload/video/');
        exit();
    } else {
        $view->vars->message = Language::getText('errors_below');
        $view->vars->message .= '<br /><br /> - ' . implode ('<br /> - ', $view->vars->errors);
        $view->vars->message_type = 'errors';
    }
}

$view->vars->video = $video;
Plugin::triggerEvent('upload.before_render');