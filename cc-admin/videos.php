<?php

// Init application
include_once(dirname(dirname(__FILE__)) . '/cc-core/system/admin.bootstrap.php');

// Verify if user is logged in
$authService->enforceTimeout(true);

// Verify user can access admin panel
$userService = new \UserService();
Functions::redirectIf($userService->checkPermissions('admin_panel', $adminUser), HOST . '/account/');

// Establish page variables, objects, arrays, etc
$videoMapper = new VideoMapper();
$videoService = new VideoService();
$records_per_page = 9;
$url = ADMIN . '/videos.php';
$query_string = array();
$categories = array();
$message = null;
$sub_header = null;
$webmEncodingEnabled = (Settings::get('webm_encoding_enabled') == '1') ? true : false;
$theoraEncodingEnabled = (Settings::get('theora_encoding_enabled') == '1') ? true : false;
$admin_js[] = ADMIN . '/extras/fancybox/jquery.fancybox-1.3.4.js';
$admin_js[] = ADMIN . '/js/fancybox.js';

// Retrieve Category names
$categoryService = new CategoryService();
$categories = $categoryService->getCategories();

// Handle "Delete" video if requested
if (!empty($_GET['delete']) && is_numeric($_GET['delete'])) {

    // Validate video id
    $video = $videoMapper->getVideoById($_GET['delete']);
    if ($video) {
        $videoService->delete($video);
        $message = 'Video has been deleted';
        $message_type = 'alert-success';
    }
}

// Handle "Feature" video if requested
else if (!empty($_GET['feature']) && is_numeric($_GET['feature'])) {

    // Validate video id
    $video = $videoMapper->getVideoByCustom(array('video_id' => $_GET['feature'], 'featured' => 0, 'status' => VideoMapper::APPROVED));
    if ($video) {
        $video->featured = true;
        $videoMapper->save($video);
        $message = 'Video has been featured';
        $message_type = 'alert-success';
    }
}

// Handle "Un-Feature" video if requested
else if (!empty($_GET['unfeature']) && is_numeric($_GET['unfeature'])) {

    // Validate video id
    $video = $videoMapper->getVideoByCustom(array('video_id' => $_GET['unfeature'], 'featured' => 1, 'status' => VideoMapper::APPROVED));
    if ($video) {
        $video->featured = false;
        $videoMapper->save($video);
        $message = 'Video has been unfeatured';
        $message_type = 'alert-success';
    }
}

// Handle "Approve" video if requested
else if (!empty($_GET['approve']) && is_numeric($_GET['approve'])) {

    // Validate video id
    $video = $videoMapper->getVideoByCustom(array('video_id' => $_GET['approve'], 'status' => VideoMapper::PENDING_APPROVAL));
    if ($video) {
        $videoService->approve($video, 'approve');
        $message = 'Video has been approved and is now available';
        $message_type = 'alert-success';
    }
}

// Handle "Unban" video if requested
else if (!empty($_GET['unban']) && is_numeric($_GET['unban'])) {

    // Validate video id
    $video = $videoMapper->getVideoByCustom(array('video_id' => $_GET['unban'], 'status' => VideoMapper::BANNED));
    if ($video) {
        $videoService->approve($video, 'approve');
        $message = 'Video has been unbanned';
        $message_type = 'alert-success';
    }
}

// Handle "Ban" video if requested
else if (!empty($_GET['ban']) && is_numeric ($_GET['ban'])) {

    // Validate video id
    $video = $videoMapper->getVideoByCustom(array('video_id' => $_GET['ban']));
    if ($video && $video->status != VideoMapper::BANNED) {
        $video->status = VideoMapper::BANNED;
        $videoMapper->save($video);
        $flagService = new FlagService();
        $flagService->flagDecision($video, true);
        $message = 'Video has been banned';
        $message_type = 'alert-success';
    }
}

// Determine which type (status) of video to display
$query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE";
$status = (!empty($_GET['status'])) ? $_GET['status'] : VideoMapper::APPROVED;
switch ($status) {
    case 'pending':
        $query .= " status IN ('processing', '" . VideoMapper::PENDING_APPROVAL . "', '" . VideoMapper::PENDING_CONVERSION . "')";
        $query_string['status'] = 'pending';
        $header = 'Pending Videos';
        $page_title = 'Pending Videos';
        $statusText = 'Pending';
        break;
    case 'banned':
        $query .= " status = '" . VideoMapper::BANNED . "'";
        $query_string['status'] = 'banned';
        $header = 'Banned Videos';
        $page_title = 'Banned Videos';
        $statusText = 'Banned';
        break;
    case 'featured':
        $query .= " status = '" . VideoMapper::APPROVED . "' AND featured = 1";
        $query_string['status'] = 'featured';
        $header = 'Featured Videos';
        $page_title = 'Featured Videos';
        $statusText = 'Featured';
        break;
    default:
        $query .= " status = '" . VideoMapper::APPROVED . "'";
        $status = 'approved';
        $header = 'Approved Videos';
        $page_title = 'Approved Videos';
        $statusText = 'Approved';
        break;
}

// Handle Search Member Form
if (isset ($_POST['search_submitted'])&& !empty($_POST['search'])) {
    $like = trim($_POST['search']);
    $query_string['search'] = $like;
    $query .= " AND title LIKE :like";
    $sub_header = "Search Results for: <em>$like</em>";
    $queryParams = array(':like' => '%' . $like . '%');
} else if (!empty($_GET['search'])) {
    $like = trim($_GET['search']);
    $query_string['search'] = $like;
    $query .= " AND title LIKE :like";
    $sub_header = "Search Results for: <em>$like</em>";
    $queryParams = array(':like' => '%' . $like . '%');
} else {
    $queryParams = array();
}

// Retrieve total count
$query .= " ORDER BY video_id DESC";
$db->fetchAll($query, $queryParams);
$total = $db->rowCount();

// Initialize pagination
$url .= (!empty($query_string)) ? '?' . http_build_query($query_string) : '';
$pagination = new Pagination($url, $total, $records_per_page, false);
$start_record = $pagination->getStartRecord();
$_SESSION['list_page'] = $pagination->getURL();

// Retrieve limited results
$query .= " LIMIT $start_record, $records_per_page";
$videoResults = $db->fetchAll($query, $queryParams);
$videoList = $videoMapper->getVideosFromList(
    Functions::arrayColumn($videoResults, 'video_id')
);

// Output Header
$pageName = 'videos';
include('header.php');

?>

<link rel="stylesheet" type="text/css" href="<?=ADMIN?>/extras/fancybox/jquery.fancybox-1.3.4.css" />
<meta name="h264Url" content="<?=$config->h264Url?>" />
<meta name="thumbUrl" content="<?=$config->thumbUrl?>" />
<?php if ($webmEncodingEnabled): ?>
    <meta name="webmUrl" content="<?=$config->webmUrl?>" />
<?php endif; ?>
<?php if ($theoraEncodingEnabled): ?>
    <meta name="theoraUrl" content="<?=$config->theoraUrl?>" />
<?php endif; ?>

<h1><?=$header?></h1>
<?php if ($sub_header): ?>
<h3><?=$sub_header?></h3>
<?php endif; ?>


<?php if ($message): ?>
<div class="alert <?=$message_type?>"><?=$message?></div>
<?php endif; ?>

<div class="filters">
    <div class="jump">
        Jump To:

        <div class="dropdown">
          <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">
            <?=$statusText?>
            <span class="caret"></span>
          </button>
          <ul class="dropdown-menu">
            <li><a tabindex="-1" href="<?=ADMIN?>/videos.php?status=approved">Approved</a></li>
            <li><a tabindex="-1" href="<?=ADMIN?>/videos.php?status=featured">Featured</a></li>
            <li><a tabindex="-1" href="<?=ADMIN?>/videos.php?status=pending">Pending</a></li>
            <li><a tabindex="-1" href="<?=ADMIN?>/videos.php?status=banned">Banned</a></li>
          </ul>
        </div>
    </div>

    <div class="search">
        <form method="POST" action="<?=ADMIN?>/videos.php?status=<?=$status?>">
            <input type="hidden" name="search_submitted" value="true" />
            <input class="form-control" type="text" name="search" value="" />
            <input type="submit" name="submit" class="button" value="Search" />
        </form>
    </div>
</div>

<?php if ($total > 0): ?>

    <table class="table table-striped">
        <thead>
            <tr>
                <th class="video-title">Title</th>
                <th class="category">Category</th>
                <th>Member</th>
                <th>Upload Date</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($videoList as $video): ?>

            <tr>
                <td class="video-title">
                    <a href="<?=ADMIN?>/videos_edit.php?id=<?=$video->videoId?>" class="h3"><?=$video->title?></a><br />
                    <div class="record-actions">

                        <?php if (!in_array($video->status, array('processing', VideoMapper::PENDING_CONVERSION))): ?>
                            <a href="" class="watch" data-filename="<?=$video->filename?>">Watch</a>
                        <?php endif; ?>

                        <a href="<?=ADMIN?>/videos_edit.php?id=<?=$video->videoId?>">Edit</a>

                        <?php if ($status == 'approved'): ?>
                            <?php if ($video->featured == 1): ?>
                                <a href="<?=$pagination->getURL('unfeature='.$video->videoId)?>">Un-Feature</a>
                            <?php else: ?>
                                <a href="<?=$pagination->getURL('feature='.$video->videoId)?>">Feature</a>
                            <?php endif ?>
                        <?php endif; ?>

                        <?php if ($status == 'featured'): ?>
                            <a href="<?=$pagination->getURL('unfeature='.$video->videoId)?>">Un-Feature</a>
                        <?php endif; ?>

                        <?php if (in_array ($status, array ('approved','featured'))): ?>
                            <a href="<?=$videoService->getUrl($video)?>/" target="_ccsite">Go to Video</a>
                        <?php endif; ?>

                        <?php if ($video->status == VideoMapper::PENDING_APPROVAL): ?>
                            <a class="approve" href="<?=$pagination->getURL('approve='.$video->videoId)?>">Approve</a>
                        <?php elseif (in_array ($status, array ('approved','featured'))): ?>
                            <a class="delete" href="<?=$pagination->getURL('ban='.$video->videoId)?>">Ban</a>
                        <?php elseif ($status == 'banned'): ?>
                            <a href="<?=$pagination->getURL('unban='.$video->videoId)?>">Unban</a>
                        <?php endif; ?>

                        <a class="delete confirm" href="<?=$pagination->getURL('delete='.$video->videoId)?>" data-confirm="You're about to delete this video. This cannot be undone. Do you want to proceed?">Delete</a>
                    </div>
                </td>
                <td class="category"><?=$categories[$video->categoryId]->name?></td>
                <td><?=$video->username?></td>
                <td><?=date('m/d/Y', strtotime($video->dateCreated))?></td>
            </tr>

        <?php endforeach; ?>
        </tbody>
    </table>

<?php else: ?>
    <p>No videos found</p>
<?php endif; ?>

<video width="600" height="337" controls="controls" poster="">
    <source src="" type="video/mp4" />
    <?php if ($webmEncodingEnabled): ?>
        <source src="" type="video/webm" />
    <?php endif; ?>
    <?php if ($theoraEncodingEnabled): ?>
        <source src="" type="video/ogg" />
    <?php endif; ?>
</video>

<?php include('footer.php'); ?>