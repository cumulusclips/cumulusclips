<?php

// Verify if user is logged in
$userService = new UserService();
$this->view->vars->loggedInUser = $userService->loginCheck();

// Verify Member was supplied
$userMapper = new UserMapper();
if (!empty($_GET['username'])) {
    $user = $userMapper->getUserByCustom(array('username' => $_GET['username'], 'status' => 'Active'));
} else {
    App::Throw404();
}

// Verify Member exists
if ($user) {
    $this->view->vars->member = $user;
    $this->view->vars->meta->title = Functions::Replace($this->view->vars->meta->title, array('member' => $this->view->vars->member->username));
} else {
    App::Throw404();
}

// Check if user is subscribed
if ($this->view->vars->loggedInUser) {
    $subscriptionService = new SubscriptionService();
    $this->view->vars->subscribe_text = $subscriptionService->checkSubscription($this->view->vars->loggedInUser->userId, $this->view->vars->member->userId) ? 'unsubscribe' : 'subscribe';
} else {
    $this->view->vars->subscribe_text = 'subscribe';
}

// Count subscription
$db = Registry::get('db');
$query = "SELECT COUNT(subscription_id) as count FROM " . DB_PREFIX . "subscriptions WHERE member = " . $this->view->vars->member->userId;
$countResult = $db->fetchRow($query);
$this->view->vars->sub_count = $countResult['count'];

// Retrieve member's video list
$videoMapper = new VideoMapper();
$query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE user_id = " . $this->view->vars->member->userId . " AND status = 'approved' AND private = '0' LIMIT 6";
$memberVideosResults = $db->fetchAll($query);
$this->view->vars->result_videos = $videoMapper->getVideosFromList(
    Functions::arrayColumn($memberVideosResults, 'video_id')
);

// Update Member's profile view count
$this->view->vars->member->views++;
$userMapper->save($this->view->vars->member);