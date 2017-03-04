<?php

// Init application
include_once(dirname(dirname(__FILE__)) . '/cc-core/system/admin.bootstrap.php');

// Verify user can access admin panel
$userService = new \UserService();
Functions::RedirectIf($userService->checkPermissions('admin_panel', $adminUser), HOST . '/account/');

// Establish page variables, objects, arrays, etc
$videoMapper = new \VideoMapper();
$videoService = new \VideoService();
$fileService = new \FileService();
$attachmentMapper = new \AttachmentMapper();
$fileMapper = new \FileMapper();
$page_title = 'Edit Video';
$pageName = 'videos-edit';
$message = null;
$message_type = null;
$admin_js[] = ADMIN . '/extras/fileupload/fileupload.jquery-ui.widget.js';
$admin_js[] = ADMIN . '/extras/fileupload/fileupload.iframe-transport.js';
$admin_js[] = ADMIN . '/extras/fileupload/fileupload.plugin.js';
$admin_js[] = ADMIN . '/js/fileupload.js';
$private_url = $videoService->generatePrivate();
$categories = array();
$data = array();
$errors = array();
$message = null;
$tab = null;
$newAttachmentFileIds = array();
$newFiles = array();

// Retrieve Category names
$categoryService = new CategoryService();
$categories = $categoryService->getCategories();

// Build return to list link
if (!empty ($_SESSION['list_page'])) {
    $list_page = $_SESSION['list_page'];
} else {
    $list_page = ADMIN . '/videos.php';
}

### Verify a video was provided
if (!empty($_GET['id']) && is_numeric ($_GET['id']) && $_GET['id'] > 0) {

    ### Retrieve video information
    $video = $videoMapper->getVideoById($_GET['id']);
    if (!$video || !in_array($video->status, array('approved', 'processing', VideoMapper::PENDING_CONVERSION, VideoMapper::PENDING_APPROVAL, 'banned'))) {
        header ('Location: ' . ADMIN . '/videos.php');
        exit();
    }
    if ($video->private) $private_url = $video->privateUrl;
} else {
    header ('Location: ' . ADMIN . '/videos.php');
    exit();
}

// Retrieve video's attachments
$videoAttachments = $fileService->getVideoAttachments($video);
$attachmentFileIds = \Functions::arrayColumn($videoAttachments, 'fileId');

/***********************
Handle form if submitted
***********************/

if (isset ($_POST['submitted'])) {

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
                            $errors['attachment'] = 'Invalid attachment';
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
                                || !\App::isValidUpload($attachment['temp'], $adminUser, 'library')
                            ) {
                                $errors['attachment'] = 'Invalid attachment file upload';
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
                                || $file->userId !== $adminUser->userId
                            ) {
                                $errors['attachment'] = 'Invalid attachment';
                                break 2;
                            }

                            // Verify attachment isn't already attached
                            if (in_array($attachment['file'], $newAttachmentFileIds)) {
                                $errors['attachment'] = 'File is already attached';
                                break 2;
                            }

                            // Create attachment entry
                            $newAttachmentFileIds[] = $attachment['file'];

                        } else {
                            $errors['attachment'] = 'Invalid attachment';
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
        if (!empty ($_POST['title']) && !ctype_space ($_POST['title'])) {
            $video->title = trim($_POST['title']);
        } else {
            $errors['title'] = 'Invalid title';
        }


        // Validate description
        if (!empty ($_POST['description']) && !ctype_space ($_POST['description'])) {
            $video->description = trim ($_POST['description']);
        } else {
            $errors['description'] = 'Invalid description';
        }


        // Validate tags
        if (!empty ($_POST['tags']) && !ctype_space ($_POST['tags'])) {
            $video->tags = preg_split('/,\s*/', trim($_POST['tags']));
        } else {
            $errors['tags'] = 'Invalid tags';
        }


        // Validate cat_id
        if (!empty ($_POST['cat_id']) && is_numeric ($_POST['cat_id'])) {
            $video->categoryId = $_POST['cat_id'];
        } else {
            $errors['cat_id'] = 'Invalid category';
        }


        // Validate disable embed
        if (!empty ($_POST['disable_embed']) && $_POST['disable_embed'] == '1') {
            $video->disableEmbed = true;
        } else {
            $video->disableEmbed = false;
        }


        // Validate gated
        if (!empty ($_POST['gated']) && $_POST['gated'] == '1') {
            $video->gated = true;
        } else {
            $video->gated = false;
        }


        // Validate private
        if (!empty ($_POST['private']) && $_POST['private'] == '1') {
            $video->private = true;

            try {

                // Validate private URL
                if (empty ($_POST['private_url'])) throw new Exception ('error');
                if (strlen ($_POST['private_url']) != 7) throw new Exception ('error');
                $duplicateVideo = $videoMapper->getVideoByCustom(array('private_url' => $_POST['private_url']));
                if ($duplicateVideo && $duplicateVideo->videoId != $video->videoId) throw new Exception ('error');

                // Set private URL
                $video->privateUrl = trim($_POST['private_url']);
                $private_url = $video->privateUrl;

            } catch (Exception $e) {
                $errors['private_url'] = 'Invalid private URL';
            }

        } else {
            $video->private = false;
            $video->privateUrl = null;
        }

        // Validate close comments
        if (!empty ($_POST['closeComments']) && $_POST['closeComments'] == '1') {
            $video->commentsClosed = true;
        } else {
            $video->commentsClosed = false;
        }

        // Validate status
        if (!in_array($video->status, array('processing', VideoMapper::PENDING_CONVERSION))) {
            if (!empty ($_POST['status']) && !ctype_space ($_POST['status'])) {
                $video->status = $newStatus = trim ($_POST['status']);
            } else {
                $errors['status'] = 'Invalid status';
            }
        }

        // Update video if no errors were made
        if (empty ($errors)) {

            // Create files for uploaded attachments
            foreach ($newFiles as $key => $newFile) {

                $file = new \File();
                $file->filename = $fileService->generateFilename();
                $file->name = $newFile['name'];
                $file->type = \FileMapper::TYPE_ATTACHMENT;
                $file->userId = $adminUser->userId;
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

            // Perform addional actions based on status change
            if (isset($newStatus) && $newStatus != $video->status) {

                // Handle "Approve" action
                if ($newStatus == 'approved') {
                    $videoService->Approve('approve');
                }

                // Handle "Ban" action
                else if ($newStatus == 'banned') {
                    $flagService = new FlagService();
                    $flagService->flagDecision($video, true);
                }
            }

            $videoMapper->save($video);
            $message = 'Video has been updated.';
            $message_type = 'alert-success';
        } else {
            $message = 'The following errors were found. Please correct them and try again.';
            $message .= '<br /><br /> - ' . implode ('<br /> - ', $errors);
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
include ('header.php');

?>

<h1>Edit Video</h1>

<?php if ($message): ?>
<div class="alert <?=$message_type?>"><?=$message?></div>
<?php endif; ?>

<p><a href="<?=$list_page?>">Return to previous screen</a></p>

<form action="<?php echo ADMIN; ?>/videos_edit.php?id=<?php echo $video->videoId; ?>" method="post">

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

            <div class="form-group <?=(isset ($errors['status'])) ? 'has-error' : ''?>">
                <label>Status:</label>
                <?php if (!in_array($video->status, array('processing', VideoMapper::PENDING_CONVERSION))): ?>
                    <select name="status" class="form-control">
                        <option value="<?php echo VideoMapper::APPROVED; ?>"<?=(isset ($video->status) && $video->status == 'approved') || (!isset ($video->status) && $video->status == 'approved')?' selected="selected"':''?>>Approved</option>
                        <option value="<?php echo VideoMapper::PENDING_APPROVAL; ?>"<?=(isset ($video->status) && $video->status == VideoMapper::PENDING_APPROVAL) || (!isset ($video->status) && $video->status == VideoMapper::PENDING_APPROVAL)?' selected="selected"':''?>>Pending</option>
                        <option value="<?php echo VideoMapper::BANNED; ?>"<?=(isset ($video->status) && $video->status == 'banned') || (!isset ($video->status) && $video->status == 'banned')?' selected="selected"':''?>>Banned</option>
                    </select>
                <?php else: ?>
                    <?=($video->status == 'processing') ? 'Processing' : 'Pending Conversion'?>
                <?php endif; ?>

            </div>

            <div class="form-group <?=(isset ($errors['title'])) ? 'has-error' : ''?>">
                <label class="control-label">Title:</label>
                <input class="form-control" type="text" name="title" value="<?=htmlspecialchars($video->title)?>" />
            </div>

            <div class="form-group <?=(isset ($errors['description'])) ? 'has-error' : ''?>">
                <label class="control-label">Description:</label>
                <textarea rows="7" cols="50" class="form-control" name="description"><?=htmlspecialchars($video->description)?></textarea>
            </div>

            <div class="form-group <?=(isset ($errors['tags'])) ? 'has-error' : ''?>">
                <label class="control-label">Tags:</label>
                <input class="form-control" type="text" name="tags" value="<?=htmlspecialchars(implode (', ', $video->tags))?>" /> (Comma Delimited)
            </div>

            <div class="form-group <?=(isset ($errors['cat_id'])) ? 'has-error' : ''?>">
                <label>Category:</label>
                <select class="form-control" name="cat_id">
                <?php foreach ($categories as $category): ?>
                    <option value="<?=$category->categoryId?>"<?=($video->categoryId == $category->categoryId) ? ' selected="selected"' : ''?>><?=$category->name?></option>
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

                    <?php foreach ($attachmentFileIds as $fileId): ?>

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
                                    class="<?php echo in_array($file->fileId, $attachmentFileIds) ? 'selected' : ''; ?>"
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

            <div class="form-group ">
                <input id="disable-embed" type="checkbox" name="disable_embed" value="1" <?=$video->disableEmbed ? 'checked="checked"' : ''?> />
                <label for="disable-embed">Disable Embed</label> <em>(Video cannot be embedded on third party sites)</em>
            </div>

            <div class="form-group ">
                <input id="gated-video" type="checkbox" name="gated" value="1" <?=$video->gated ? 'checked="checked"' : ''?> />
                <label for="gated-video">Gated</label> <em>(Video can only be viewed by members who are logged in)</em>
            </div>

            <div class="form-group ">
                <input id="private-video" data-block="private-url" class="showhide" type="checkbox" name="private" value="1" <?=$video->private ? 'checked="checked"' : ''?> />
                <label for="private-video">Private</label> <em>(Video can only be viewed by you or anyone with the private URL)</em>
            </div>

            <div id="private-url" class="form-group <?=$video->private ? '' : 'hide'?>">
                <label <?=(isset ($errors['private_url'])) ? 'class="error"' : ''?>>Private URL:</label>
                <?=HOST?>/private/videos/<span><?=(!empty ($video->privateUrl)) ? $video->privateUrl : $private_url?></span>/
                <input type="hidden" name="private_url" value="<?=(!empty ($video->privateUrl)) ? $video->privateUrl : $private_url?>" />
                <a href="" class="small">Regenerate</a>
            </div>

            <div class="form-group ">
                <input id="closeComments" type="checkbox" name="closeComments" value="1" <?=$video->commentsClosed ? 'checked="checked"' : ''?> />
                <label for="closeComments">Close Comments</label> <em>(Allow comments to be posted to video)</em>
            </div>
        </div>
        <!-- END Advanced Settings Tab Pane -->

    </div>
    <!-- END Tab Panes -->

    <div class="tab-content-footer">
        <input type="hidden" name="submitted" value="TRUE" />
        <input type="hidden" name="nonce" value="<?=$formNonce?>" />
        <input type="submit" class="button" value="Update Video" />
    </div>

</form>

<?php include ('footer.php'); ?>