<?php

// Establish page variables, objects, arrays, etc
$view->InitView('profile');
Plugin::triggerEvent('profile.start');

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
    Plugin::Trigger('profile.load_member');
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
Plugin::triggerEvent('profile.load_recent_videos');
$memberVideosResults = $db->fetchAll($query);
$view->vars->result_videos = $videoMapper->getVideosFromList(
    Functions::arrayColumn($memberVideosResults, 'video_id')
);

// Update Member's profile view count
$view->vars->member->views++;
$userMapper->save($view->vars->member);

// Retrieve latest comments by user
$commentMapper = new CommentMapper();
$query = "SELECT comment_id FROM " . DB_PREFIX . "comments WHERE user_id = " . $view->vars->member->userId . "  ORDER BY comment_id DESC LIMIT 10";
Plugin::triggerEvent('profile.load_comments');
$memberCommentsResults = $db->fetchAll($query);
$view->vars->comment_list = $commentMapper->getCommentsFromList(
    Functions::arrayColumn($memberCommentsResults, 'comment_id')
);

// Output Page
Plugin::triggerEvent('profile.before_render');
$view->Render('profile.tpl');