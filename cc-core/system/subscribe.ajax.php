<?php

### Created on March 15, 2009
### Created by Miguel A. Hurtado
### This script performs all the user actions for a video via AJAX


// Include required files
include ('../config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Subscription');
Plugin::Trigger ('subscribe.ajax.start');


// Establish page variables, objects, arrays, etc
$logged_in = User::LoginCheck();
if ($logged_in) $user = new User ($logged_in);
Plugin::Trigger ('subscribe.ajax.login_check');


// Verify passed values
if (empty ($_POST['type']) || !in_array ($_POST['type'], array ('subscribe', 'unsubscribe'))) App::Throw404();
if (empty ($_POST['member']) || !is_numeric ($_POST['member'])) App::Throw404();


// Validate user
$member = new User ($_POST['member']);
if (!$member->found || $member->status != 'Active') App::Throw404();




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
            echo json_encode (array ('result' => 1, 'msg' => (string) Language::GetText('success_subscribed', array ('username' => $member->username))));
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
            echo json_encode (array ('result' => 1, 'msg' => (string) Language::GetText('success_unsubscribed')));
            exit();
        } else {
            echo json_encode (array ('result' => 0, 'msg' => (string) Language::GetText('error_subscribe_noexist')));
            exit();
        }


}   // END action switch

?>