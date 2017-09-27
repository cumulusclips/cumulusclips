<?php

Plugin::triggerEvent('upload_info.start');

// @deprecated Deprecated in 2.5.0, removed in 2.6.0. Use upload_info.end instead
Plugin::triggerEvent('upload.start');

// Verify if user registrations are enabled
$config = Registry::get('config');
if (!$config->enableUserUploads) App::throw404();

// Verify if user is logged in
$this->authService->enforceAuth();
$this->view->vars->loggedInUser = $this->authService->getAuthUser();

// Establish page variables, objects, arrays, etc
App::EnableUploadsCheck();
$this->view->vars->categories = null;
$this->view->vars->data = array();
$this->view->vars->errors = array();
$this->view->vars->message = null;
$videoService = new VideoService();
$videoMapper = new VideoMapper();
$fileMapper = new \FileMapper();
$fileService = new \FileService();
$attachmentMapper = new \AttachmentMapper();
$video = new Video();
$this->view->vars->privateUrl = $videoService->generatePrivate();
$newAttachmentFileIds = array();
$newFiles = array();

// Send user video upload page if upload has not been completed first
if (!isset($_SESSION['upload'])) {
    header('Location: ' . HOST . '/account/upload/video/');
    exit();
}

// Retrieve user's attachments
$this->view->vars->userAttachments = $fileMapper->getMultipleByCustom(array(
    'user_id' => $this->view->vars->loggedInUser->userId,
    'type' => \FileMapper::TYPE_ATTACHMENT
));

// Retrieve Categories
$categoryService = new CategoryService();
$this->view->vars->categoryList = $categoryService->getCategories();

// Handle upload form if submitted
if (isset ($_POST['submitted'])) {

    // Validate form nonce token and submission speed
    if (
        !empty($_POST['nonce'])
        && !empty($_SESSION['formNonce'])
        && !empty($_SESSION['formTime'])
        && $_POST['nonce'] == $_SESSION['formNonce']
        && time() - $_SESSION['formTime'] >= 2
    ) {

        // Validate video attachments
        if ($config->allowVideoAttachments && isset($_POST['attachment']) && is_array($_POST['attachment'])) {

            foreach ($_POST['attachment'] as $attachment) {

                if (!is_array($attachment)) {
                    $this->view->vars->errors['attachment'] = Language::getText('error_attachment');
                    break;
                }

                // Determine if attachment is a new file upload or existing attachment
                if (!empty($attachment['temp'])) {

                    // New upload

                    // Validate file upload info
                    if (
                        empty($attachment['name'])
                        || empty($attachment['size'])
                        || !is_numeric($attachment['size'])
                        || !\App::isValidUpload($attachment['temp'], $this->view->vars->loggedInUser, 'library')
                    ) {
                        $this->view->vars->errors['attachment'] = Language::getText('error_attachment_file');
                        break;
                    }

                    // Create file
                    $newFiles[] = array(
                        'temp' => $attachment['temp'],
                        'name' => $attachment['name'],
                        'size' => $attachment['size']
                    );

                } elseif (!empty($attachment['file'])) {

                    // Attaching existing file

                    $file = $fileMapper->getById($attachment['file']);

                    // Verify file exists and belongs to user
                    if (
                        !$file
                        || $file->userId !== $this->view->vars->loggedInUser->userId
                    ) {
                        $this->view->vars->errors['attachment'] = Language::getText('error_attachment');
                        break;
                    }

                    // Verify attachment isn't already attached
                    if (in_array($attachment['file'], $newAttachmentFileIds)) {
                        $this->view->vars->errors['attachment'] = Language::getText('error_attachment_duplicate');
                        break;
                    }

                    // Create attachment entry
                    $newAttachmentFileIds[] = $attachment['file'];

                } else {
                    $this->view->vars->errors['attachment'] = Language::getText('error_attachment');
                    break;
                }
            }
        }

        // Validate Title
        if (!empty($_POST['title']) && !ctype_space($_POST['title'])) {
            $video->title = trim($_POST['title']);
        } else {
            $this->view->vars->errors['title'] = Language::getText('error_title');
        }

        // Validate Description
        if (!empty($_POST['description']) && !ctype_space($_POST['description'])) {
            $video->description = trim($_POST['description']);
        } else {
            $this->view->vars->errors['description'] = Language::getText('error_description');
        }

        // Validate Tags
        if (!empty($_POST['tags']) && !ctype_space($_POST['tags'])) {
            $video->tags = preg_split('/,\s*/', trim($_POST['tags']));
        } else {
            $this->view->vars->errors['tags'] = Language::getText('error_tags');
        }

        // Validate Category
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
                if ($videoMapper->getVideoByCustom(array('private_url' => $_POST['private_url']))) throw new Exception();

                // Set private URL
                $video->private = true;
                $video->privateUrl = trim($_POST['private_url']);
            } catch (Exception $e) {
                $this->view->vars->errors['private_url'] = Language::getText('error_private_url');
            }
        } else {
            $video->private = false;
        }

        // Validate close comments
        if (!empty($_POST['closeComments']) && $_POST['closeComments'] == '1') {
            $video->commentsClosed = true;
        } else {
            $video->commentsClosed = false;
        }

        // Verify no errors were found
        if (empty($this->view->vars->errors)) {

            // Create video in system
            $video->userId = $this->view->vars->loggedInUser->userId;
            $video->filename = $videoService->generateFilename();
            $video->originalExtension = Functions::getExtension($_SESSION['upload']->temp);
            $video->status = VideoMapper::PENDING_CONVERSION;
            $_SESSION['upload']->videoId = $videoId = $videoMapper->save($video);

            try {

                // Create files for uploaded attachments
                foreach ($newFiles as $newFile) {

                    $file = new \File();
                    $file->filename = $fileService->generateFilename();
                    $file->name = $newFile['name'];
                    $file->type = \FileMapper::TYPE_ATTACHMENT;
                    $file->userId = $this->view->vars->loggedInUser->userId;
                    $file->extension = Functions::getExtension($newFile['temp']);
                    $file->filesize = filesize($newFile['temp']);

                    // Move file to files directory
                    Filesystem::rename($newFile['temp'], UPLOAD_PATH . '/files/attachments/' . $file->filename . '.' . $file->extension);

                    // Create record
                    $newAttachmentFileIds[] = $fileMapper->save($file);
                }

                // Create attachments
                foreach ($newAttachmentFileIds as $fileId) {
                    $attachment = new \Attachment();
                    $attachment->videoId = $videoId;
                    $attachment->fileId = $fileId;
                    $attachmentMapper->save($attachment);
                }

                // Move temp video file to raw video location
                Filesystem::rename(
                    $_SESSION['upload']->temp,
                    UPLOAD_PATH . '/temp/' . $video->filename . '.' . $video->originalExtension
                );

                // Begin transcoding
                $commandOutput = $config->debugConversion ? CONVERSION_LOG : '/dev/null';
                $command = 'nohup ' . Settings::get('php') . ' ' . DOC_ROOT . '/cc-core/system/encode.php --video="' . $videoId . '" >> ' .  $commandOutput . ' 2>&1 &';
                exec($command);

                // Move to upload complete page
                header('Location: ' . HOST . '/account/upload/complete/');
                exit();

            } catch (Exception $exception) {
                App::alert('Error During Video Upload', $exception->getMessage());
                $this->view->vars->message = Language::getText('error_upload_system');
                $this->view->vars->message_type = 'errors';
            }

        } else {
            $this->view->vars->message = Language::getText('errors_below');
            $this->view->vars->message .= '<br /><br /> - ' . implode ('<br /> - ', $this->view->vars->errors);
            $this->view->vars->message_type = 'errors';
        }

    } else {
        $this->view->vars->message = Language::getText('invalid_session');
        $this->view->vars->message_type = 'errors';
    }
}

$this->view->vars->video = $video;
$this->view->vars->newFiles = $newFiles;
$this->view->vars->newAttachmentFileIds = $newAttachmentFileIds;

// Generate new form nonce
$this->view->vars->formNonce = md5(uniqid(rand(), true));
$_SESSION['formNonce'] = $this->view->vars->formNonce;
$_SESSION['formTime'] = time();

Plugin::triggerEvent('upload_info.end');

// @deprecated Deprecated in 2.5.0, removed in 2.6.0. Use upload_info.end instead
Plugin::triggerEvent('upload.end');
