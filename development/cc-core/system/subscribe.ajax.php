<?php

// Include required files
include_once (dirname (dirname (__FILE__)) . '/config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Subscription');
Plugin::Trigger ('subscribe.ajax.start');


// Establish page variables, objects, arrays, etc
$logged_in = User::LoginCheck();
if ($logged_in) $user = new User ($logged_in);
Plugin::Trigger ('subscribe.ajax.login_check');


// Verify passed values
if (empty ($_POST['type']) || !in_array ($_POST['type'], array ('subscribe', 'unsubscribe'))) App::Throw404();
if (empty ($_POST['user']) || !is_numeric ($_POST['user'])) App::Throw404();


// Validate user
if (!User::Exist (array ('user_id' => $_POST['user'], 'status' => 'active'))) App::Throw404();
$member = new User ($_POST['user']);




### Handle subscribe/unsubscribe
switch ($_POST['type']) {

    ### Handle subscribe user to a member
    case 'subscribe':

        // Verify user is logged in
        if (!$logged_in) {
            echo json_encode (array ('result' => 0, 'msg' => (string) Language::GetText('error_subscribe_login')));
            exit();
        }

        // Check if user is subscribing to himself
        if ($user->user_id == $member->user_id) {
            echo json_encode (array ('result' => 0, 'msg' => (string) Language::GetText('error_subscribe_own')));
            exit();
        }

        // Create subscription record if none exists
        $data = array ('member' => $member->user_id, 'user_id' => $user->user_id);
        if (!Subscription::Exist ($data)) {
            $subscribed = Subscription::Create ($data);
            Plugin::Trigger ('subscribe.ajax.subscribe');
            echo json_encode (array ('result' => 1, 'msg' => (string) Language::GetText('success_subscribed', array ('username' => $member->username)),'other' => (string) Language::GetText ('unsubscribe')));
            exit();
        } else {
            echo json_encode (array ('result' => 0, 'msg' => (string) Language::GetText('error_subscribe_duplicate')));
            exit();
        }




    ### Handle unsubscribe user from a member
    case 'unsubscribe':

        // Verify user is logged in
        if (!$logged_in) {
            echo json_encode (array ('result' => 0, 'msg' => (string) Language::GetText('error_subscribe_login')));
            exit();
        }

        // Delete subscription if one exists
        $subscription_id = Subscription::Exist (array ('user_id' => $user->user_id, 'member' => $member->user_id));
        if ($subscription_id) {
            Subscription::Delete ($subscription_id);
            Plugin::Trigger ('subscribe.ajax.unsubscribe');
            echo json_encode (array ('result' => 1, 'msg' => (string) Language::GetText('success_unsubscribed', array ('username' => $member->username)),'other' => (string) Language::GetText ('subscribe')));
            exit();
        } else {
            echo json_encode (array ('result' => 0, 'msg' => (string) Language::GetText('error_subscribe_noexist')));
            exit();
        }


}   // END action switch

?>