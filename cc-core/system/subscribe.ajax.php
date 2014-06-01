<?php

Plugin::triggerEvent('subscribe.ajax.start');

// Verify if user is logged in
$userService = new UserService();
$loggedInUser = $userService->loginCheck();
Plugin::Trigger ('subscribe.ajax.login_check');

// Establish page variables, objects, arrays, etc
$this->view->disableView = true;
$userMapper = new UserMapper();
$subscriptionService = new SubscriptionService();

// Validate passed values
if (
    empty($_POST['type'])
    || !in_array($_POST['type'], array('subscribe', 'unsubscribe'))
    || empty($_POST['user'])
    || !is_numeric($_POST['user'])
) {
    App::Throw404();
}

// Validate user
$member = $userMapper->getUserByCustom(array('user_id' => $_POST['user'], 'status' => 'active'));
if (!$member) App::Throw404();

// Handle subscribe/unsubscribe
switch ($_POST['type']) {

    // Handle subscribe user to a member
    case 'subscribe':

        // Verify user is logged in
        if (!$loggedInUser) {
            echo json_encode(array('result' => 0, 'message' => (string) Language::GetText('error_subscribe_login')));
            exit();
        }

        // Check if user is subscribing to himself
        if ($loggedInUser->userId == $member->userId) {
            echo json_encode(array('result' => 0, 'message' => (string) Language::GetText('error_subscribe_own')));
            exit();
        }

        // Create subscription if not yet subscribed
        if (!$subscriptionService->checkSubscription($loggedInUser->userId, $member->userId)) {
            $subscriptionService->subscribe($loggedInUser->userId, $member->userId);
            Plugin::Trigger ('subscribe.ajax.subscribe');
            echo json_encode(array('result' => 1, 'message' => (string) Language::GetText('success_subscribed', array('username' => $member->username)), 'other' => (string) Language::GetText('unsubscribe')));
            exit();
        } else {
            echo json_encode(array('result' => 0, 'message' => (string) Language::GetText('error_subscribe_duplicate')));
            exit();
        }

    // Handle unsubscribe user from a member
    case 'unsubscribe':

        // Verify user is logged in
        if (!$loggedInUser) {
            echo json_encode(array('result' => 0, 'message' => (string) Language::GetText('error_subscribe_login')));
            exit();
        }

        // Unsubscribe user if subscribed
        if ($subscriptionService->checkSubscription($loggedInUser->userId, $member->userId)) {
            $subscriptionService->unsubscribe($loggedInUser->userId, $member->userId);
            Plugin::Trigger('subscribe.ajax.unsubscribe');
            echo json_encode(array('result' => 1, 'message' => (string) Language::GetText('success_unsubscribed', array('username' => $member->username)), 'other' => (string) Language::GetText('subscribe')));
            exit();
        } else {
            echo json_encode(array('result' => 0, 'message' => (string) Language::GetText('error_subscribe_noexist')));
            exit();
        }
}