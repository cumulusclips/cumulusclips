<?php

// Init view
View::InitView('myvideos');
Plugin::triggerEvent('myvideos.start');

// Verify if user is logged in
$userService = new UserService();
View::$vars->loggedInUser = $userService->loginCheck();
Functions::RedirectIf(View::$vars->loggedInUser, HOST . '/login/');

// Establish page variables, objects, arrays, etc
$records_per_page = 9;
$url = HOST . '/myaccount/myvideos';
View::$vars->message = null;
$videoMapper = new VideoMapper();

// Delete video if requested
if (!empty($_GET['vid'])) {
    $video = $videoMapper->getVideoByCustom(array(
        'user_id' => View::$vars->loggedInUser->userId,
        'video_id' => $_GET['vid']
    ));
    if ($video) {
        $videoService = new VideoService();
        $videoService->delete($video);
        View::$vars->message = Language::GetText('success_video_deleted');
        View::$vars->message_type = 'success';
        Plugin::triggerEvent('myvideos.delete_video');
    }
}

// Retrieve total count
$query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE user_id = " . View::$vars->loggedInUser->userId . " AND status IN ('approved', 'processing', 'pendingConversion', 'pendingApproval') ORDER BY date_created DESC";
$db->fetchAll($query);
$total = $db->rowCount();

// Initialize pagination
View::$vars->pagination = new Pagination($url, $total, $records_per_page);
$start_record = View::$vars->pagination->GetStartRecord();

// Retrieve limited results
$query .= " LIMIT $start_record, $records_per_page";
$resultVideos = $db->fetchAll($query);
View::$vars->userVideos = $videoMapper->getVideosFromList(
    Functions::flattenArray($resultVideos, 'video_id')
);

// Output page
Plugin::triggerEvent('myvideos.before_render');
View::Render('myaccount/myvideos.tpl');