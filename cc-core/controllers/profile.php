<?php

// Include required files
include_once (dirname (dirname (__FILE__)) . '/config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Video');
App::LoadClass ('Rating');
App::LoadClass ('Subscription');
App::LoadClass ('Flag');
App::LoadClass ('Post');


// Establish page variables, objects, arrays, etc
View::InitView ('profile');
Plugin::Trigger ('profile.start');
View::$vars->logged_in = User::LoginCheck();
if (View::$vars->logged_in) View::$vars->user = new User (View::$vars->logged_in);
$success = NULL;
$errors = NULL;
$sub_id = NULL;


// Verify Member was supplied
if (isset ($_GET['username'])) {
    $data = array ('username' => $_GET['username'], 'status' => 'Active');
    $user_id = User::Exist ($data);
} else {
    App::Throw404();
}


// Verify Member exists
if ($user_id) {
    View::$vars->member = new User ($user_id);
    View::$vars->meta->title = Functions::Replace (View::$vars->meta->title, array ('member' => View::$vars->member->username));
    Plugin::Trigger ('profile.load_member');
} else {
    App::Throw404();
}



### Check if user is subscribed
if (View::$vars->logged_in) {
    $data = array ('user_id' => View::$vars->user->user_id, 'member' => View::$vars->member->user_id);
    View::$vars->subscribe_text = Subscription::Exist ($data) ? 'unsubscribe' : 'subscribe';
} else {
    View::$vars->subscribe_text = 'subscribe';
}



### Count subscription
$query = "SELECT COUNT(sub_id) FROM " . DB_PREFIX . "subscriptions WHERE member = " . View::$vars->member->user_id;
$result = $db->Query ($query);
View::$vars->sub_count = $db->FetchRow ($result);



### Retrieve video list
$query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE user_id = " . View::$vars->member->user_id . " AND status = 'approved' AND private = '0' LIMIT 6";
Plugin::Trigger ('profile.load_recent_videos');
$result = $db->Query ($query);
View::$vars->result_videos = array();
while ($video = $db->FetchObj ($result)) {
    View::$vars->result_videos[] = $video->video_id;
}


### Update Member view count
$data = array ('views' => View::$vars->member->views+1);
View::$vars->member->Update ($data);



### Retrieve latest status updates
$query = "SELECT post_id FROM " . DB_PREFIX . "posts WHERE user_id = " . View::$vars->member->user_id . "  ORDER BY post_id DESC LIMIT 10";
Plugin::Trigger ('profile.load_posts');
$result_posts = $db->Query ($query);
View::$vars->post_list = array();
while ($row = $db->FetchObj ($result_posts)) {
    View::$vars->post_list[] = $row->post_id;
}



### Retrieve latest comments by user
$query = "SELECT comment_id FROM " . DB_PREFIX . "comments WHERE user_id = " . View::$vars->member->user_id . "  ORDER BY comment_id DESC LIMIT 10";
Plugin::Trigger ('profile.load_comments');
$result_comments = $db->Query ($query);
View::$vars->comment_list = array();
while ($row = $db->FetchObj ($result_comments)) {
    View::$vars->comment_list[] = $row->comment_id;
}


// Output Page
Plugin::Trigger ('profile.before_render');
View::Render ('profile.tpl');

?>