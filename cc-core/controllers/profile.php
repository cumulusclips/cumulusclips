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
App::LoadClass ('Post');
View::InitView();


// Establish page variables, objects, arrays, etc
View::LoadPage ('profile');
Plugin::Trigger ('profile.start');
View::$vars->logged_in = User::LoginCheck();
if (View::$vars->logged_in) $user = new User (View::$vars->logged_in);
$success = NULL;
$errors = NULL;
$sub_id = NULL;
$post_count = 5;


// Verify Member was supplied
if (isset ($_GET['username'])) {
    $data = array ('username' => $_GET['username'], 'account_status' => 'Active');
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
    $data = array ('user_id' => $user->user_id, 'member' => View::$vars->member->user_id);
    View::$vars->subscribe_text = Subscription::Exist ($data) ? 'unsubscribe' : 'subscribe';
} else {
    View::$vars->subscribe_text = 'subscribe';
}



### Count subscription
$query = "SELECT COUNT(sub_id) FROM subscriptions WHERE member = " . View::$vars->member->user_id;
$result = $db->Query ($query);
View::$vars->sub_count = $db->FetchRow ($result);



### Retrieve video list
$query = "SELECT video_id FROM videos WHERE user_id = " . View::$vars->member->user_id . " AND status = 6 LIMIT 3";
Plugin::Trigger ('profile.load_recent_videos');
View::$vars->result_videos = $db->Query ($query);



### Update Member view count
$data = array ('views' => View::$vars->member->views+1);
View::$vars->member->Update ($data);



### Retrieve latest status updates
$query = "SELECT post_id FROM posts WHERE user_id = " . View::$vars->member->user_id . "  ORDER BY post_id DESC LIMIT 0, $post_count";
Plugin::Trigger ('profile.load_posts');
$result_posts = $db->Query ($query);
View::$vars->post_list = array();
while ($row = $db->FetchObj ($result_posts)) {
    View::$vars->post_list[] = $row->post_id;
}


// Output Page
View::AddMeta ('baseURL', HOST);
View::AddSidebarBlock ('recent_posts.tpl');
Plugin::Trigger ('profile.before_render');
View::Render ('profile.tpl');

?>