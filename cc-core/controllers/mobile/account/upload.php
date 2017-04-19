<?php

// Verify if user registrations are enabled
$config = Registry::get('config');
if (!$config->enableUserUploads) App::throw404();

Plugin::triggerEvent('mobile_upload.start');
Functions::redirectIf((boolean) Settings::get('mobile_site'), HOST . '/');

// Verify if user is logged in
$this->authService->enforceAuth();
$this->view->vars->loggedInUser = $this->authService->getAuthUser();

// Establish page variables, objects, arrays, etc
$videoMapper = new VideoMapper();
$videoService = new VideoService();
$db = Registry::get('db');
$errors = array();
$this->view->vars->private_url = $videoService->generatePrivate();

// Retrieve Category names
$categoryService = new CategoryService();
$this->view->vars->categories = $categoryService->getCategories();

// Handle form if submitted
if (isset($_POST['submitted'])) {

    $this->view->options->disableView = true;
    $video = new Video();

    // Validate video upload
    if (!empty($_POST['filename']) && file_exists($_POST['filename'])) {
        $tempFile = $_POST['filename'];
    } else {
        $errors['video'] = 'Invalid video upload';
    }

    // Validate title
    if (!empty($_POST['title'])) {
        $video->title = trim($_POST['title']);
    } else {
        $errors['title'] = Language::getText('error_title');
    }

    // Validate description
    if (!empty($_POST['description'])) {
        $video->description = trim ($_POST['description']);
    } else {
        $errors['description'] = Language::getText('error_description');
    }

    // Validate tags
    if (!empty($_POST['tags'])) {
        $video->tags = preg_split('/,\s*/', trim($_POST['tags']));
    } else {
        $errors['tags'] = Language::getText('error_tags');
    }

    // Validate cat_id
    if (!empty($_POST['category_id']) && is_numeric($_POST['category_id'])) {
        $video->categoryId = $_POST['category_id'];
    } else {
        $errors['category_id'] = Language::getText('error_category');
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
        $video->private = true;
        if (!empty($_POST['private_url']) && strlen($_POST['private_url']) == 7 && !$videoMapper->getVideoByCustom(array('private_url' => $_POST['private_url']))) {
            $video->privateUrl = trim($_POST['private_url']);
            $private_url = $video->privateUrl;
        } else {
            $errors['private_url'] = Language::getText('error_private_url');
        }
    } else {
        $video->private = false;
    }

    // Validate close comments
    if (!empty($_POST['close_comments']) && $_POST['close_comments'] == '1') {
        $video->commentsClosed = true;
    } else {
        $video->commentsClosed = false;
    }

    // Update video if no errors were made
    if (empty($errors)) {

        // Create video in system
        $video->userId = $this->view->vars->loggedInUser->userId;
        $video->filename = $videoService->generateFilename();
        $video->originalExtension = Functions::getExtension($tempFile);
        $video->status = VideoMapper::PENDING_CONVERSION;
        $videoId = $videoMapper->save($video);

        try {

            // Move temp file to raw video location
            Filesystem::rename(
                $tempFile,
                UPLOAD_PATH . '/temp/' . $video->filename . '.' . $video->originalExtension
            );

            // Begin transcoding
            $commandOutput = $config->debugConversion ? CONVERSION_LOG : '/dev/null';
            $command = 'nohup ' . Settings::get('php') . ' ' . DOC_ROOT . '/cc-core/system/encode.php --video="' . $videoId . '" >> ' .  $commandOutput . ' 2>&1 &';
            exec($command);

            // Output message
            echo json_encode(array(
                'result' => true,
                'message' => Language::getText('success_video_upload'),
                'other' => array('videoId' => $videoId)
            ));

        } catch (Exception $exception) {
            echo json_encode(array(
                'result' => false,
                'message' => Language::getText('error_upload_system'),
                'errors' => array_keys($errors)
            ));
        }

    } else {
        $message = Language::getText('errors_below');
        $message .= '<br><br> - ' . implode('<br> - ', $errors);
        echo json_encode(array('result' => false, 'message' => $message, 'errors' => array_keys($errors)));
    }
}

Plugin::triggerEvent('mobile_upload.end');