<?php

// Init view
View::initView('edit_video');
Plugin::triggerEvent('edit_video.start');

// Verify if user is logged in
$userService = new UserService();
View::$vars->loggedInUser = $userService->loginCheck();
Functions::RedirectIf(View::$vars->loggedInUser, HOST . '/login/');

// Establish page variables, objects, arrays, etc
$videoMapper = new VideoMapper();
$videoService = new VideoService();
View::$vars->privateUrl = $videoService->generatePrivate();
View::$vars->errors = array();
View::$vars->message = null;

// Verify a video was provided
if (!empty($_GET['vid']) && $_GET['vid'] > 0) {

    // Retrieve video information
    $video = $videoMapper->getVideoByCustom(array(
        'user_id' => View::$vars->loggedInUser->userId,
        'video_id' => $_GET['vid']
    ));
    
    // Verify video is valid
    if (!$video) {
        header('Location: ' . HOST . '/myaccount/myvideos/');
        exit();
    }
} else {
    header('Location: ' . HOST . '/myaccount/myvideos/');
    exit();
}

// Handle form if submitted
if (isset($_POST['submitted'])) {

    // Validate title
    if (!empty($_POST['title']) && !ctype_space($_POST['title'])) {
        $video->title = trim($_POST['title']);
    } else {
        View::$vars->errors['title'] = Language::getText('error_title');
    }

    // Validate description
    if (!empty($_POST['description']) && !ctype_space($_POST['description'])) {
        $video->description = trim($_POST['description']);
    } else {
        View::$vars->errors['description'] = Language::getText('error_description');
    }

    // Validate tags
    if (!empty($_POST['tags']) && !ctype_space($_POST['tags'])) {
        $video->tags = preg_split('/,\s*/', trim($_POST['tags']));
    } else {
        View::$vars->errors['tags'] = Language::getText('error_tags');
    }

    // Validate cat_id
    if (!empty($_POST['cat_id']) && is_numeric($_POST['cat_id'])) {
        $video->categoryId = $_POST['cat_id'];
    } else {
        View::$vars->errors['cat_id'] = Language::getText('error_category');
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
            View::$vars->errors['private_url'] = Language::getText('error_private_url');
        }
    } else {
        $video->private = false;
        $video->privateUrl = null;
    }

    // Update video if no errors were made
    if (empty (View::$vars->errors)) {
        $videoMapper->save($video);
        View::$vars->message = Language::getText('success_video_updated');
        View::$vars->message_type = 'success';
        Plugin::triggerEvent('edit_video.edit');
    } else {
        View::$vars->message = Language::getText('errors_below');
        View::$vars->message .= '<br /><br /> - ' . implode('<br /> - ', View::$vars->errors);
        View::$vars->message_type = 'errors';
    }
}

// Retrieve Categories	
$categoryService = new CategoryService();
View::$vars->categoryList = $categoryService->getCategories();

// Output page
View::$vars->video = $video;
Plugin::triggerEvent('edit_video.before_render');
View::Render ('myaccount/edit_video.tpl');