<?php

Plugin::triggerEvent('videos_edit.start');

// Verify if user registrations are enabled
$config = Registry::get('config');
if (!$config->enableUserUploads) App::throw404();

// Verify if user is logged in
$this->authService->enforceAuth();
$this->view->vars->loggedInUser = $this->authService->getAuthUser();

// Establish page variables, objects, arrays, etc
$videoService = new VideoService();
$videoMapper = new VideoMapper();
$fileMapper = new \FileMapper();
$fileService = new \FileService();
$attachmentMapper = new \AttachmentMapper();
$this->view->vars->privateUrl = $videoService->generatePrivate();
$this->view->vars->errors = array();
$this->view->vars->message = null;
$this->view->vars->message_type = null;
$newAttachmentFileIds = array();
$newFiles = array();

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

// Retrieve video's attachments
$videoAttachments = $fileService->getVideoAttachments($video);
$attachmentFileIds = \Functions::arrayColumn($videoAttachments, 'fileId');

// Handle form if submitted
if (isset($_POST['submitted'])) {

    // Validate form nonce token and submission speed
    if (
        !empty($_POST['nonce'])
        && !empty($_SESSION['formNonce'])
        && !empty($_SESSION['formTime'])
        && $_POST['nonce'] == $_SESSION['formNonce']
        && time() - $_SESSION['formTime'] >= 2
    ) {
        if ($config->allowVideoAttachments) {

            // Validate video attachments
            if (isset($_POST['attachment']) && is_array($_POST['attachment'])) {

                do {

                    foreach ($_POST['attachment'] as $attachment) {

                        if (!is_array($attachment)) {
                            $this->view->vars->errors['attachment'] = Language::getText('error_attachment');
                            break 2;
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
                                break 2;
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
                                break 2;
                            }

                            // Verify attachment isn't already attached
                            if (in_array($attachment['file'], $newAttachmentFileIds)) {
                                $this->view->vars->errors['attachment'] = Language::getText('error_attachment_duplicate');
                                break 2;
                            }

                            // Create attachment entry
                            $newAttachmentFileIds[] = $attachment['file'];

                        } else {
                            $this->view->vars->errors['attachment'] = Language::getText('error_attachment');
                            break 2;
                        }
                    }

                    // Set attachment files to display on form
                    $attachmentFileIds = $newAttachmentFileIds;

                } while (false);

            } else {
                // Set attachment files to display on form
                $attachmentFileIds = $newAttachmentFileIds;
            }
        }

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

            try {

                // Create files for uploaded attachments
                foreach ($newFiles as $key => $newFile) {

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
                    $newAttachmentFileIds[] = $attachmentFileIds[] = $fileMapper->save($file);
                    unset($newFiles[$key]);
                }

                // Determine which attachments are new and removed
                $existingAttachmentFileIds = \Functions::arrayColumn($videoAttachments, 'fileId');
                $removedAttachmentFileIds = array_diff($existingAttachmentFileIds, $newAttachmentFileIds);
                $addedAttachmentFileIds = array_diff($newAttachmentFileIds, $existingAttachmentFileIds);

                // Create new attachments
                foreach ($addedAttachmentFileIds as $fileId) {
                    $attachment = new \Attachment();
                    $attachment->videoId = $video->videoId;
                    $attachment->fileId = $fileId;
                    $attachmentMapper->save($attachment);
                }

                // Remove discarded attachments
                foreach ($removedAttachmentFileIds as $fileId) {
                    $attachment = $attachmentMapper->getByCustom(array('file_id' => $fileId));
                    $attachmentMapper->delete($attachment->attachmentId);
                }

                // Save video
                $videoMapper->save($video);

                $this->view->vars->message = Language::getText('success_video_updated');
                $this->view->vars->message_type = 'success';

            } catch (\Exception $exception) {
                App::alert('Error During Video Edit', $exception->getMessage());
                $this->view->vars->message = Language::getText('error_attachment_upload');
                $this->view->vars->message_type = 'errors';
            }

        } else {
            $this->view->vars->message = Language::getText('errors_below');
            $this->view->vars->message .= '<br /><br /> - ' . implode('<br /> - ', $this->view->vars->errors);
            $this->view->vars->message_type = 'errors';
        }

    } else {
        $this->view->vars->message = Language::getText('invalid_session');
        $this->view->vars->message_type = 'errors';
    }
}

// Retrieve Categories
$categoryService = new CategoryService();
$this->view->vars->categoryList = $categoryService->getCategories();

$this->view->vars->video = $video;
$this->view->vars->newFiles = $newFiles;
$this->view->vars->attachmentFileIds = $attachmentFileIds;

// Retrieve user's attachments
$this->view->vars->userAttachments = $fileMapper->getMultipleByCustom(array(
    'user_id' => $this->view->vars->loggedInUser->userId,
    'type' => \FileMapper::TYPE_ATTACHMENT
));

// Generate new form nonce
$this->view->vars->formNonce = md5(uniqid(rand(), true));
$_SESSION['formNonce'] = $this->view->vars->formNonce;
$_SESSION['formTime'] = time();

Plugin::triggerEvent('videos_edit.end');
