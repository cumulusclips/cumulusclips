<?php

// Verify if user is logged in
$userService = new UserService();
$view->vars->loggedInUser = $userService->loginCheck();

// Verify Member was supplied
$userMapper = new UserMapper();
if (!empty($_GET['username'])) {
    $user = $userMapper->getUserByCustom(array('username' => $_GET['username'], 'status' => 'Active'));
} else {
    App::Throw404();
}

// Verify Member exists
if ($user) {
    $view->vars->member = $user;
    $view->vars->meta->title = Functions::Replace($view->vars->meta->title, array('member' => $view->vars->member->username));
} else {
    App::Throw404();
}

// Check if user is subscribed
if ($view->vars->loggedInUser) {
    $subscriptionService = new SubscriptionService();
    $view->vars->subscribe_text = $subscriptionService->checkSubscription($view->vars->loggedInUser->userId, $view->vars->member->userId) ? 'unsubscribe' : 'subscribe';
} else {
    $view->vars->subscribe_text = 'subscribe';
}

// Count subscription
$query = "SELECT COUNT(subscription_id) as count FROM " . DB_PREFIX . "subscriptions WHERE member = " . $view->vars->member->userId;
$countResult = $db->fetchRow($query);
$view->vars->sub_count = $countResult['count'];

// Retrieve member's video list
$videoMapper = new VideoMapper();
$query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE user_id = " . $view->vars->member->userId . " AND status = 'approved' AND private = '0' LIMIT 6";
$memberVideosResults = $db->fetchAll($query);
$view->vars->result_videos = $videoMapper->getVideosFromList(
    Functions::arrayColumn($memberVideosResults, 'video_id')
);

// Update Member's profile view count
$view->vars->member->views++;
$userMapper->save($view->vars->member);