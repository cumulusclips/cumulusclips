<?php

// Init application
include_once(dirname(dirname(__FILE__)) . '/cc-core/config/admin.bootstrap.php');

// Verify if user is logged in
$userService = new UserService();
$adminUser = $userService->loginCheck();
Functions::RedirectIf($adminUser, HOST . '/login/');
Functions::RedirectIf($userService->checkPermissions('admin_panel', $adminUser), HOST . '/myaccount/');

// Establish page variables, objects, arrays, etc
$videoMapper = new VideoMapper();
$videoService = new VideoService();
$records_per_page = 9;
$url = ADMIN . '/videos.php';
$query_string = array();
$categories = array();
$message = null;
$sub_header = null;
$vp8Options = json_decode(Settings::Get('vp8Options'));
$admin_js[] = ADMIN . '/extras/fancybox/jquery.fancybox-1.3.4.js';
$admin_js[] = ADMIN . '/js/fancybox.js';


// Retrieve Category names
$categoryService = new CategoryService();
$categories = $categoryService->getCategories();


### Handle "Delete" video if requested
if (!empty ($_GET['delete']) && is_numeric ($_GET['delete'])) {

    // Validate video id
    $video = $videoMapper->getVideoById($_GET['delete']);
    if ($video) {
        $videoService->delete($video);
        $message = 'Video has been deleted';
        $message_type = 'success';
    }
}


### Handle "Feature" video if requested
else if (!empty ($_GET['feature']) && is_numeric ($_GET['feature'])) {

    // Validate video id
    $video = $videoMapper->getVideoByCustom(array('video_id' => $_GET['feature'], 'featured' => 0, 'status' => 'approved'));
    if ($video) {
        $video->featured = true;
        $videoMapper->save($video);
        $message = 'Video has been featured';
        $message_type = 'success';
    }
}


### Handle "Un-Feature" video if requested
else if (!empty ($_GET['unfeature']) && is_numeric ($_GET['unfeature'])) {

    // Validate video id
    $video = $videoMapper->getVideoByCustom(array('video_id' => $_GET['unfeature'], 'featured' => 1, 'status' => 'approved'));
    if ($video) {
        $video->featured = false;
        $videoMapper->save($video);
        $message = 'Video has been unfeatured';
        $message_type = 'success';
    }
}


### Handle "Approve" video if requested
else if (!empty ($_GET['approve']) && is_numeric ($_GET['approve'])) {

    // Validate video id
    $video = $videoMapper->getVideoByCustom(array('video_id' => $_GET['approve'], 'status' => 'pendingApproval'));
    if ($video) {
        $videoService->approve($video, 'approve');
        $message = 'Video has been approved and is now available';
        $message_type = 'success';
    }
}


### Handle "Unban" video if requested
else if (!empty ($_GET['unban']) && is_numeric ($_GET['unban'])) {

    // Validate video id
    $video = $videoMapper->getVideoByCustom(array('video_id' => $_GET['unban'], 'status' => 'banned'));
    if ($video) {
        $videoService->approve($video, 'approve');
        $message = 'Video has been unbanned';
        $message_type = 'success';
    }
}


### Handle "Ban" video if requested
else if (!empty ($_GET['ban']) && is_numeric ($_GET['ban'])) {

    // Validate video id
    $video = $videoMapper->getVideoByCustom(array('video_id' => $_GET['ban']));
    if ($video && $video->status != 'banned') {
        $video->status = 'banned';
        $videoMapper->save($video);
        $flagService = new FlagService();
        $flagService->flagDecision($video, true);
        $message = 'Video has been banned';
        $message_type = 'success';
    }
}




### Determine which type (status) of video to display
$query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE";
$status = (!empty ($_GET['status'])) ? $_GET['status'] : 'approved';
switch ($status) {
    case 'pending':
        $query .= " status IN ('processing', 'pendingApproval', 'pendingConversion')";
        $query_string['status'] = 'pending';
        $header = 'Pending Videos';
        $page_title = 'Pending Videos';
        break;
    case 'banned':
        $query .= " status = 'banned'";
        $query_string['status'] = 'banned';
        $header = 'Banned Videos';
        $page_title = 'Banned Videos';
        break;
    case 'featured':
        $query .= " status = 'approved' AND featured = 1";
        $query_string['status'] = 'featured';
        $header = 'Featured Videos';
        $page_title = 'Featured Videos';
        break;
    default:
        $query .= " status = 'approved'";
        $status = 'approved';
        $header = 'Approved Videos';
        $page_title = 'Approved Videos';
        break;
}

// Handle Search Member Form
if (isset ($_POST['search_submitted'])&& !empty ($_POST['search'])) {
    $like = trim($_POST['search']);
    $query_string['search'] = $like;
    $query .= " AND title LIKE :like";
    $sub_header = "Search Results for: <em>$like</em>";
    $queryParams = array(':like' => '%' . $like . '%');
} else if (!empty ($_GET['search'])) {
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
$url .= (!empty ($query_string)) ? '?' . http_build_query($query_string) : '';
$pagination = new Pagination ($url, $total, $records_per_page, false);
$start_record = $pagination->GetStartRecord();
$_SESSION['list_page'] = $pagination->GetURL();

// Retrieve limited results
$query .= " LIMIT $start_record, $records_per_page";
$videoResults = $db->fetchAll($query, $queryParams);
$videoList = $videoMapper->getVideosFromList(
    Functions::flattenArray($videoResults, 'video_id')
);


// Output Header
include ('header.php');

?>

<link rel="stylesheet" type="text/css" href="<?=ADMIN?>/extras/fancybox/jquery.fancybox-1.3.4.css" />
<meta name="h264Url" content="<?=$config->h264Url?>" />
<meta name="theoraUrl" content="<?=$config->theoraUrl?>" />
<meta name="thumbUrl" content="<?=$config->thumb_url?>" />
<?php if ($vp8Options->enabled): ?>
    <meta name="vp8Url" content="<?=$config->vp8Url?>" />
<?php endif; ?>

<div id="videos">

    <h1><?=$header?></h1>
    <?php if ($sub_header): ?>
    <h3><?=$sub_header?></h3>
    <?php endif; ?>


    <?php if ($message): ?>
    <div class="message <?=$message_type?>"><?=$message?></div>
    <?php endif; ?>


    <div id="browse-header">
        <div class="jump">
            Jump To:
            <select name="status" data-jump="<?=ADMIN?>/videos.php">
                <option <?=(isset($status) && $status == 'approved') ? 'selected="selected"' : ''?>value="approved">Approved</option>
                <option <?=(isset($status) && $status == 'featured') ? 'selected="selected"' : ''?>value="featured">Featured</option>
                <option <?=(isset($status) && $status == 'pending') ? 'selected="selected"' : ''?>value="pending">Pending</option>
                <option <?=(isset($status) && $status == 'banned') ? 'selected="selected"' : ''?>value="banned">Banned</option>
            </select>
        </div>

        <div class="search">
            <form method="POST" action="<?=ADMIN?>/videos.php?status=<?=$status?>">
                <input type="hidden" name="search_submitted" value="true" />
                <input type="text" name="search" value="" />&nbsp;
                <input type="submit" name="submit" class="button" value="Search" />
            </form>
        </div>
    </div>

    <?php if ($total > 0): ?>

        <div class="block list">
            <table>
                <thead>
                    <tr>
                        <td class="video-title large">Title</td>
                        <td class="category large">Category</td>
                        <td class="large">Member</td>
                        <td class="large">Upload Date</td>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($videoList as $video): ?>

                    <?php $odd = empty ($odd) ? true : false; ?>

                    <tr class="<?=$odd ? 'odd' : ''?>">
                        <td class="video-title">
                            <a href="<?=ADMIN?>/videos_edit.php?id=<?=$video->videoId?>" class="large"><?=$video->title?></a><br />
                            <div class="record-actions">
                                
                                <?php if (!in_array($video->status, array('processing', 'pendingConversion'))): ?>
                                    <a href="" class="watch" data-filename="<?=$video->filename?>">Watch</a>
                                <?php endif; ?>
                                    
                                <a href="<?=ADMIN?>/videos_edit.php?id=<?=$video->videoId?>">Edit</a>

                                <?php if ($status == 'approved'): ?>
                                    <?php if ($video->featured == 1): ?>
                                        <a href="<?=$pagination->GetURL('unfeature='.$video->videoId)?>">Un-Feature</a>
                                    <?php else: ?>
                                        <a href="<?=$pagination->GetURL('feature='.$video->videoId)?>">Feature</a>
                                    <?php endif ?>
                                <?php endif; ?>
                                    
                                <?php if ($status == 'featured'): ?>
                                    <a href="<?=$pagination->GetURL('unfeature='.$video->videoId)?>">Un-Feature</a>
                                <?php endif; ?>

                                <?php if (in_array ($status, array ('approved','featured'))): ?>
                                    <a href="<?=$videoService->getUrl($video)?>/" target="_ccsite">Go to Video</a>
                                <?php endif; ?>

                                <?php if ($video->status == 'pendingApproval'): ?>
                                    <a class="approve" href="<?=$pagination->GetURL('approve='.$video->videoId)?>">Approve</a>
                                <?php elseif (in_array ($status, array ('approved','featured'))): ?>
                                    <a class="delete" href="<?=$pagination->GetURL('ban='.$video->videoId)?>">Ban</a>
                                <?php elseif ($status == 'banned'): ?>
                                    <a href="<?=$pagination->GetURL('unban='.$video->videoId)?>">Unban</a>
                                <?php endif; ?>

                                <a class="delete confirm" href="<?=$pagination->GetURL('delete='.$video->videoId)?>" data-confirm="You're about to delete this video. This cannot be undone. Do you want to proceed?">Delete</a>
                            </div>
                        </td>
                        <td class="category"><?=$categories[$video->categoryId]?></td>
                        <td><?=$video->username?></td>
                        <td><?=Functions::DateFormat('m/d/Y',$video->dateCreated)?></td>
                    </tr>

                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?=$pagination->paginate()?>

    <?php else: ?>
        <div class="block"><strong>No videos found</strong></div>
    <?php endif; ?>
        
    <video width="600" height="337" controls="controls" poster="">
        <source src="" type="video/mp4" />
        <source src="" type="video/ogg" />
        <?php if ($vp8Options->enabled): ?>
            <source src="" type="video/webm" />
        <?php endif; ?>
    </video>

</div>
    
<?php include ('footer.php'); ?>