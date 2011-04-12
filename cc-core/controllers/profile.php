<?php

### Created on March 11, 2009
### Created by Miguel A. Hurtado
### This script displays the channel page


// Include required files
include ('../config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Video');
App::LoadClass ('Rating');
App::LoadClass ('Subscription');
App::LoadClass ('Flag');
App::LoadClass ('Picture');
App::LoadClass ('Post');
View::InitView();


// Establish page variables, objects, arrays, etc
View::$vars->logged_in = User::LoginCheck();
if (View::$vars->logged_in) $user = new User (View::$vars->logged_in);
View::$vars->page_title = 'Techie Videos - ';
$success = NULL;
$errors = NULL;
$sub_id = NULL;
$post_count = 5;


// Verify Member was supplied
if (isset ($_GET['username'])) {
    $data = array ('username' => $_GET['username'], 'account_status' => 'Active');
    $id = User::Exist ($data);
} else {
    App::Throw404();
}


// Verify Member exists
if ($id) {
    View::$vars->member = new User ($id);
    View::$vars->page_title .= View::$vars->member->username .  "'s Profile";
} else {
    App::Throw404();
}



### Check if user is subscribed
if (View::$vars->logged_in) {
    $data = array ('user_id' => $user->user_id, 'member' => View::$vars->member->user_id);
    $sub_id = Subscription::Exist ($data);
    View::$vars->subscribed = $sub_id ? true : false;
} else {
    View::$vars->subscribed = false;
}



### Count subscription
$query = "SELECT COUNT(sub_id) FROM subscriptions WHERE member = " . View::$vars->member->user_id;
$result = $db->Query ($query);
View::$vars->sub_count = $db->FetchRow ($result);



### Retrieve video list
$query = "SELECT video_id FROM videos WHERE user_id = " . View::$vars->member->user_id . " AND status = 6 LIMIT 3";
View::$vars->result_videos = $db->Query ($query);



### Update Member view count
$data = array ('views' => View::$vars->member->views+1);
View::$vars->member->Update ($data);



### Retrieve latest status updates
$query = "SELECT post_id FROM posts WHERE user_id = " . View::$vars->member->user_id . "  ORDER BY post_id DESC LIMIT 0, $post_count";
View::$vars->result_posts = $db->Query ($query);





/*************************
Handle Action if requested
*************************/

//if (isset ($_GET['action'])) {
//
//	switch ($_GET['action']) {
//
//		case 'subscribe':
//
//			if (!$logged_in) {
//				$Errors = '<div id="errors-found">You must be logged in to subscribe to channels.</div>';
//				break;
//			}
//
//			if ($subscribed) {
//				$Errors = '<div id="errors-found">You are already subscribed to this channel.</div>';
//				break;
//			}
//
//			if ($user->user_id == View::$vars->member->user_id) {
//				$Errors = '<div id="errors-found">You can\'t subscribe to your own channel.</div>';
//				break;
//			}
//
//			$data = array ('user_id' => $user->user_id, 'channel' => View::$vars->member->user_id);
//			Subscription::Create ($data, $db);
//			$subscribed = TRUE;
//			$Success = '<div id="success">You have successfully subscribed to this channel!</div>';
//			$sub_count[0]++;
//			break;
//
//		case 'unsubscribe':
//
//			if ($logged_in && $subscribed) {
//				Subscription::Delete ($sub_id, $db);
//				$subscribed = FALSE;
//				$Success = '<div id="success">You have successfully unsubscribed from this channel!</div>';
//				$sub_count[0]--;
//			}
//			break;
//
//		case 'flag':
//
//			if (!$logged_in) {
//				$Errors = '<div id="errors-found">You must be logged in to report abuse on this channel.</div>';
//				break;
//			}
//
//			if ($user->user_id == View::$vars->member->user_id) {
//				$Errors = '<div id="errors-found">You can\'t report your own channel.</div>';
//				break;
//			}
//
//			$data = array ('user_id' => $user->user_id, 'id' => View::$vars->member->user_id, 'flag_type' => 'channel');
//			$flag_id = Flag::Exist ($data, $db);
//			if ($flag_id) {
//				$Errors = '<div id="errors-found">You already reported this channel. We may still be working on this issue, or deemed the content appropriate.</div>';
//				break;
//			}
//
//			Flag::Create ($data, $db);
//			$Success = '<div id="success">Thank you for reporting this. We will look into this matter immediately.</div>';
//			break;
//
//	}
//
//}

// Output Page
View::AddSidebarBlock ('recent_posts.tpl');
View::Render ('profile.tpl');

?>