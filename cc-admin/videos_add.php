<?php

// Init application
include_once(dirname(dirname(__FILE__)) . '/cc-core/system/admin.bootstrap.php');

// Verify user can access admin panel
$userService = new \UserService();
Functions::RedirectIf($userService->checkPermissions('admin_panel', $adminUser), HOST . '/account/');
App::enableUploadsCheck();

$ffmpegPath = Settings::get('ffmpeg');

// Establish page variables, objects, arrays, etc
$videoMapper = new VideoMapper();
$videoService = new VideoService();
$fileService = new \FileService();
$attachmentMapper = new \AttachmentMapper();
$fileMapper = new \FileMapper();
$video = new Video();
$page_title = 'Add Video';
$categories = array();
$data = array();
$errors = array();
$message = null;
$message_type = null;
$admin_js[] = ADMIN . '/extras/fileupload/fileupload.jquery-ui.widget.js';
$admin_js[] = ADMIN . '/extras/fileupload/fileupload.iframe-transport.js';
$admin_js[] = ADMIN . '/extras/fileupload/fileupload.plugin.js';
$admin_js[] = ADMIN . '/js/fileupload.js';
$private_url = $videoService->generatePrivate();
$prepopulate = null;
$tab = null;
$newAttachmentFileIds = array();
$newFiles = array();

// Retrieve Category names
$categoryService = new CategoryService();
$categories = $categoryService->getCategories();

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
                    $errors['attachment'] = 'Invalid attachment';
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
                        || !\App::isValidUpload($attachment['temp'], $adminUser, 'library')
                    ) {
                        $errors['attachment'] = 'Invalid attachment file upload';
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
                        || $file->userId !== $adminUser->userId
                    ) {
                        $errors['attachment'] = 'Invalid attachment';
                        break;
                    }

                    // Verify attachment isn't already attached
                    if (in_array($attachment['file'], $newAttachmentFileIds)) {
                        $errors['attachment'] = 'File is already attached';
                        break;
                    }

                    // Create attachment entry
                    $newAttachmentFileIds[] = $attachment['file'];

                } else {
                    $errors['attachment'] = 'Invalid attachment';
                    break;
                }
            }
        }

        // Validate file upload
        if (
            !empty($_POST['upload']['original-name'])
            && !empty($_POST['upload']['original-size'])
            && !empty($_POST['upload']['temp'])
            && \App::isValidUpload($_POST['upload']['temp'], $adminUser, 'video')
        ) {
            $prepopulate = array(
                'path' => $_POST['upload']['temp'],
                'name' => trim($_POST['upload']['original-name']),
                'size' => trim($_POST['upload']['original-size'])
            );
        } else {
            $errors['video'] = 'Invalid video upload';
        }

        // Validate title
        if (!empty($_POST['title']) && !ctype_space($_POST['title'])) {
            $video->title = trim($_POST['title']);
        } else {
            $errors['title'] = 'Invalid title';
        }

        // Validate description
        if (!empty($_POST['description']) && !ctype_space($_POST['description'])) {
            $video->description = trim($_POST['description']);
        } else {
            $errors['description'] = 'Invalid description';
        }

       // Validate tags
        if (!empty($_POST['tags']) && !ctype_space($_POST['tags'])) {
            $video->tags = preg_split('/,\s*/', trim($_POST['tags']));
        } else {
            $errors['tags'] = 'Invalid tags';
        }

        // Validate cat_id
        if (!empty($_POST['cat_id']) && is_numeric($_POST['cat_id'])) {
            $video->categoryId = $_POST['cat_id'];
        } else {
            $errors['cat_id'] = 'Invalid category';
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
                $errors['private_url'] = 'Invalid private URL';
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
        if (empty($errors)) {
            // Create video in system
            $video->userId = $adminUser->userId;
            //$video->filename = $videoService->generateFilename();
            $video->filename = Functions::getFilename($_POST['upload']['original-name']);
            $video->originalExtension = Functions::getExtension($_POST['upload']['temp']);
            //$video->status = VideoMapper::PENDING_CONVERSION;
            $videoId = $videoMapper->save($video);


            try {

                // Create files for uploaded attachments
                foreach ($newFiles as $newFile) {

                    $file = new \File();
                    //$file->filename = $fileService->generateFilename();
                    $file->filename = Functions::getFilename($_POST['upload']['original-name']);
                    $file->name = $newFile['name'];
                    $file->type = \FileMapper::TYPE_ATTACHMENT;
                    $file->userId = $adminUser->userId;
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
                    $_POST['upload']['temp'],
                    UPLOAD_PATH . '/temp/' . $video->filename . '.' . $video->originalExtension
                );

                //Checking file is MP4 file
                $command = "file ".UPLOAD_PATH . '/temp/' . $video->filename . '.mp4'." 2>&1 | grep MP4";
                exec($command,$fileIsMp4);

                if (!empty($fileIsMp4)){

                    //if we are dont needed conversion

                    // Move file to files directory
                    Filesystem::rename( UPLOAD_PATH . '/temp/' . $video->filename . '.' . $video->originalExtension,
                        UPLOAD_PATH . '/h264/' . $video->filename . '.mp4');

                    // Retrieve duration of raw video file.
                    $durationCommand = "$ffmpegPath -i ".UPLOAD_PATH . '/h264/' . $video->filename . '.mp4'." 2>&1 | grep Duration:";
                    exec($durationCommand, $durationResults);

                    $durationResultsCleaned = preg_replace('/^\s*Duration:\s*/', '', $durationResults[0]);
                    preg_match ('/^[0-9]{2}:[0-9]{2}:[0-9]{2}/', $durationResultsCleaned, $duration);

                    $sec = Functions::durationToSeconds($duration[0]);
                    $video->duration = Functions::formatDuration($duration[0]);
                    // Calculate thumbnail position
                    $thumbPosition = round ($sec / 10);

                    // Create video thumbnail image
                    $thumbCommand = "$ffmpegPath -i ".UPLOAD_PATH.'/h264/'.$video->filename .'.mp4'." -ss $thumbPosition -vf \"scale=min(640\,iw):trunc(ow/a/2)*2\" -t 1 -r 1 -f mjpeg ". UPLOAD_PATH.'/thumbs/'.$video->filename .'.jpg';
                    exec($thumbCommand);    // Execute Thumb Creation Command

                    // Activate & Release
                    $video->status = VideoMapper::APPROVED;
                    $video->released = true;
                    $videoService->approve($video, 'activate');
                    // Output message
                    $prepopulate = null;
                    $message = 'Video has been added.';
                    $message_type = 'alert-success';
                    $video = null;
                    $newAttachmentFileIds = array();
                    $newFiles = array();


                }
                else {

                    $video->status = VideoMapper::PENDING_CONVERSION;
                    // Begin transcoding
                    $commandOutput = $config->debugConversion ? CONVERSION_LOG : '/dev/null';
                    $command = 'nohup ' . Settings::get('php') . ' ' . DOC_ROOT . '/cc-core/system/encode.php --video="' . $videoId . '" >> ' . $commandOutput . ' 2>&1 &';
                    exec($command);

                    //------------------------------------------------------

                    // Output message
                    $prepopulate = null;
                    $message = 'Video has been created.';
                    $message_type = 'alert-success';
                    $video = null;
                    $newAttachmentFileIds = array();
                    $newFiles = array();
                }

            } catch (Exception $exception) {
                $message = $exception->getMessage();
                $message_type = 'alert-danger';
            }

        } else {
            $message = 'The following errors were found. Please correct them and try again.';
            $message .= '<br /><br /> - ' . implode('<br /> - ', $errors);
            $message_type = 'alert-danger';
        }

    } else {
        $message = 'Expired or invalid session';
        $message_type = 'alert-danger';
    }
}

// Retrieve user's attachments
$userAttachments = $fileMapper->getMultipleByCustom(array(
    'user_id' => $adminUser->userId,
    'type' => \FileMapper::TYPE_ATTACHMENT
));

// Generate new form nonce
$formNonce = md5(uniqid(rand(), true));
$_SESSION['formNonce'] = $formNonce;
$_SESSION['formTime'] = time();

// Output Header
$pageName = 'videos-add';
include('header.php');

?>

<h1>Add Video</h1>

<div class="alert <?=$message_type?>"><?=$message?></div>

<form action="<?php echo ADMIN; ?>/videos_add.php" method="post">

    <!-- Nav tabs -->
    <ul class="nav nav-tabs" role="tablist">
        <li class="<?=(empty($tab) || $tab == 'basic') ? 'active' : ''?>"><a href="#basic" data-toggle="tab">Basic Information</a></li>
        <?php if ($config->allowVideoAttachments): ?>
            <li class="<?=($tab == 'video-attachments') ? 'active' : ''?>"><a href="#video-attachments" data-toggle="tab">Attachments</a></li>
        <?php endif; ?>
        <li class="<?=($tab == 'advanced') ? 'active' : ''?>"><a href="#advanced" data-toggle="tab">Advanced Settings</a></li>
    </ul>

    <!-- BEING Tab panes -->
    <div class="tab-content">

        <!-- BEGIN Basic Info Tab Pane -->
        <div class="tab-pane <?=(empty($tab) || $tab == 'basic') ? 'active' : ''?>" id="basic">

            <h3>Basic Information</h3>

            <div class="form-group select-file <?=(isset ($errors['video'])) ? 'has-error' : ''?>">
                <label class="control-label">Video File:</label>
                <input
                    class="uploader"
                    type="file"
                    name="upload"
                    data-url="<?php echo BASE_URL; ?>/ajax/upload/"
                    data-text="Browse"
                    data-limit="<?php echo $config->videoSizeLimit; ?>"
                    data-extensions="<?php echo urlencode(json_encode($config->acceptedVideoFormats)); ?>"
                    data-prepopulate="<?php echo urlencode(json_encode($prepopulate)); ?>"
                    data-type="video"
                />
            </div>

            <div class="form-group <?=(isset($errors['title'])) ? 'has-error' : ''?>">
                <label class="control-label">Title:</label>
                <input class="form-control" type="text" name="title" value="<?=(!empty($video->title)) ? htmlspecialchars($video->title) : ''?>" />
            </div>

            <div class="form-group <?=(isset($errors['description'])) ? 'has-error' : ''?>">
                <label class="control-label">Description:</label>
                <textarea rows="7" cols="50" class="form-control" name="description"><?=(!empty($video->description)) ? htmlspecialchars($video->description) : ''?></textarea>
            </div>

            <div class="form-group <?=(isset($errors['tags'])) ? 'has-error' : ''?>">
                <label class="control-label">Tags:</label>
                <input class="form-control" type="text" name="tags" value="<?=(!empty($video->tags)) ? htmlspecialchars(implode(', ', $video->tags)) : ''?>" /> (Comma Delimited)
            </div>

            <div class="form-group <?=(isset($errors['cat_id'])) ? 'has-error' : ''?>">
                <label class="control-label">Category:</label>
                <select class="form-control" name="cat_id">
                <?php foreach ($categories as $category): ?>
                    <option value="<?=$category->categoryId?>" <?=(!empty($video->categoryId) && $video->categoryId == $category->categoryId) ? '' : 'selected="selected"'?>><?=$category->name?></option>
                <?php endforeach; ?>
                </select>
            </div>

        </div>
        <!-- END Basic Info Tab Pane -->


        <?php if ($config->allowVideoAttachments): ?>

            <!-- BEGIN Attachments Tab Pane -->
            <div class="tab-pane <?=($tab == 'video-attachments') ? 'active' : ''?>" id="video-attachments">

                <h3>Attachments</h3>

                <div class="attachments">

                    <?php $attachmentCount = 0; ?>
                    <?php foreach ($newFiles as $newFile): ?>

                        <div class="attachment">
                            <input type="hidden" name="attachment[<?php echo $attachmentCount; ?>][name]" value="<?php echo $newFile['name']; ?>" />
                            <input type="hidden" name="attachment[<?php echo $attachmentCount; ?>][size]" value="<?php echo $newFile['size']; ?>" />
                            <input type="hidden" name="attachment[<?php echo $attachmentCount; ?>][temp]" value="<?php echo $newFile['temp']; ?>" />

                            <div class="upload-progress">
                                <a class="remove" href=""><span class="glyphicon glyphicon-remove"></span></a>
                                <span class="title"><?php echo $newFile['name']; ?> (<?php echo \Functions::formatBytes($newFile['size'],0); ?>)</span>
                                <span class="pull-right glyphicon glyphicon-ok"></span>
                            </div>
                        </div>
                        <?php $attachmentCount++; ?>

                    <?php endforeach; ?>

                    <?php foreach ($newAttachmentFileIds as $fileId): ?>

                        <?php $file = \Functions::arrayColumnFilter($fileId, 'fileId', $userAttachments); ?>

                        <div class="attachment existing-file" id="existing-file-<?php echo $fileId; ?>">
                            <input type="hidden" name="attachment[<?php echo $attachmentCount; ?>][name]" value="<?php echo $file[0]->name; ?>" />
                            <input type="hidden" name="attachment[<?php echo $attachmentCount; ?>][size]" value="<?php echo $file[0]->filesize; ?>" />
                            <input type="hidden" name="attachment[<?php echo $attachmentCount; ?>][file]" value="<?php echo $file[0]->fileId; ?>" />

                            <div class="upload-progress">
                                <a class="remove" href=""><span class="glyphicon glyphicon-remove"></span></a>
                                <span class="title"><?php echo $file[0]->name; ?> (<?php echo \Functions::formatBytes($file[0]->filesize,0); ?>)</span>
                                <span class="pull-right glyphicon glyphicon-ok"></span>
                            </div>
                        </div>
                        <?php $attachmentCount++; ?>

                    <?php endforeach; ?>

                </div>

                <div class="add">
                    <p><a href="" class="new"><i class="fa fa-plus-circle"></i> Upload a new attachment</a></p>
                    <?php if (!empty($userAttachments)): ?>
                        <p><a href="" class="existing"><i class="fa fa-plus-circle"></i> Select from existing attachments</a></p>
                    <?php endif; ?>
                </div>

                <div class="attachment-form attachment-form-upload hidden">

                    <div class="header">
                        <h4>Upload New Attachment</h4>
                        &bull; <a href="" class="cancel">Cancel</a>
                    </div>

                    <input
                        class="uploader"
                        type="file"
                        name="attachment-upload"
                        data-url="<?php echo BASE_URL; ?>/ajax/upload/"
                        data-text="Browse"
                        data-limit="<?php echo $config->fileSizeLimit; ?>"
                        data-type="library"
                    />
                </div>

                <?php if (!empty($userAttachments)): ?>

                    <div class="attachment-form attachment-form-existing hidden">

                        <div class="header">
                            <h4>Select Existing Attachment</h4>
                            &bull; <a href="" class="cancel">Cancel</a>
                        </div>

                        <p>Choose a file from your existing attachments below:</p>

                        <ul>
                            <?php foreach ($userAttachments as $file): ?>
                                <li><a
                                    id="select-existing-file-<?php echo $file->fileId; ?>"
                                    class="<?php echo in_array($file->fileId, $newAttachmentFileIds) ? 'selected' : ''; ?>"
                                    href=""
                                    data-file="<?php echo $file->fileId; ?>"
                                    data-size="<?php echo $file->filesize; ?>"
                                    title="<?php echo $file->name; ?>"
                                ><?php echo $file->name; ?> <span><?php echo \Functions::formatBytes($file->filesize, 0); ?></span></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                <?php endif; ?>

            </div>
            <!-- END Attachments Tab Pane -->

        <?php endif; ?>


        <!-- BEGIN Advanced Settings Tab Pane -->
        <div class="tab-pane <?=($tab == 'advanced') ? 'active' : ''?>" id="advanced">
            <h3>Advanced Settings</h3>

            <div class="form-group">
                <input id="disable-embed" type="checkbox" name="disable_embed" value="1" <?=(!empty($video->disableEmbed)) ? 'checked="checked"' : ''?> />
                <label for="disable-embed">Disable Embed</label> <em>(Video cannot be embedded on third party sites)</em>
            </div>

            <div class="form-group">
                <input id="gated-video" type="checkbox" name="gated" value="1" <?=(!empty($video->gated)) ? 'checked="checked"' : ''?> />
                <label for="gated-video">Gated</label> <em>(Video can only be viewed by members who are logged in)</em>
            </div>

            <div class="form-group">
                <input id="private-video" data-block="private-url" class="showhide" type="checkbox" name="private" value="1" <?=(!empty($errors) && !empty($video->private)) ? 'checked="checked"' : ''?> />
                <label for="private-video">Private</label> <em>(Video can only be viewed by you or anyone with the private URL)</em>
            </div>

            <div id="private-url" class="form-group <?=(isset($errors['private_url'])) ? 'has-error' : ''?> <?=(!empty($video->private)) ? '' : 'hide'?>">
                <label class="control-label">Private URL:</label>
                <?=HOST?>/private/videos/<span><?=$private_url?></span>/
                <input type="hidden" name="private_url" value="<?=$private_url?>" />
                <a href="" class="small">Regenerate</a>
            </div>

            <div class="form-group">
                <input id="closeComments" type="checkbox" name="closeComments" value="1" <?=(!empty($video->commentsClosed)) ? 'checked="checked"' : ''?> />
                <label for="closeComments">Close Comments</label> <em>(Allow comments to be posted to video)</em>
            </div>
        </div>
        <!-- END Advanced Settings Tab Pane -->

    </div>
    <!-- END Tab Panes -->

    <div class="tab-content-footer">
        <input type="hidden" name="submitted" value="TRUE" />
        <input type="hidden" name="nonce" value="<?=$formNonce?>" />
        <input type="submit" class="button" value="Add Video" />
    </div>

</form>

<?php include('footer.php'); ?>