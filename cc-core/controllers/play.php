<?php

### Created on March 15, 2009
### Created by Miguel A. Hurtado
### This script plays a video


// Include required files
include ('../config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Video');
App::LoadClass ('Rating');
App::LoadClass ('Subscription');
App::LoadClass ('Comment');


// Establish page variables, objects, arrays, etc
View::InitView ('play');
Plugin::Trigger ('play.start');
View::$vars->logged_in = User::LoginCheck();
View::$vars->tags = NULL;



// Validate requested video
$data = array ('video_id' => $_GET['vid'], 'status' => 'approved');
if (!isset ($_GET['vid']) || !is_numeric ($_GET['vid']) || !Video::Exist ($data)) {
    App::Throw404();
}



// Load video data for page rendering
View::$vars->video = new Video ($_GET['vid']);
View::$vars->member = new User (View::$vars->video->user_id);
View::$vars->video->Update (array ('views' => View::$vars->video->views+1));
View::$vars->rating = Rating::GetRating (View::$vars->video->video_id);
View::$vars->meta->title = View::$vars->video->title;
View::$vars->meta->keywords = implode (', ',View::$vars->video->tags);
View::$vars->meta->description = View::$vars->video->description;



### Create Tags Links
foreach (View::$vars->video->tags as $value) {
    View::$vars->tags .= '<a href="' . HOST . '/search/?keyword=' . $value . '" title="' . $value . '">' . $value . '</a>&nbsp;&nbsp; ';
}
Plugin::Trigger ('play.load_video');



// Retrieve user data if logged in
if (View::$vars->logged_in) {
    View::$vars->user = new User (View::$vars->logged_in);
    $data = array ('member' => View::$vars->video->user_id, 'user_id' => View::$vars->user->user_id);
    View::$vars->subscribe_text = (Subscription::Exist ($data)) ? 'unsubscribe' : 'subscribe';
} else {
    View::$vars->subscribe_text = 'subscribe';
}



### Retrieve related videos
$search_terms = $db->Escape (View::$vars->video->title) . ' ' . $db->Escape (implode (' ', View::$vars->video->tags));
$query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE MATCH(title, tags, description) AGAINST ('$search_terms') AND status = 'approved' LIMIT 9";
Plugin::Trigger ('play.load_suggestions');
View::$vars->result_related = $db->Query ($query);



### Retrieve comment count
$query = "SELECT COUNT(comment_id) FROM " . DB_PREFIX . "comments WHERE video_id = " . View::$vars->video->video_id . " AND status = 'approved'";
Plugin::Trigger ('play.comment_count');
$result_comment_count = $db->Query ($query);
$comment_count = $db->FetchRow ($result_comment_count);
View::$vars->comment_count = $comment_count[0];



### Retrieve comments
$query = "SELECT comment_id FROM " . DB_PREFIX . "comments WHERE video_id = " . View::$vars->video->video_id . " AND status = 'approved' ORDER BY comment_id DESC LIMIT 0, 5";
Plugin::Trigger ('play.load_comments');
$result_comments = $db->Query ($query);
View::$vars->comment_list = array();
while ($row = $db->FetchObj ($result_comments)) {
    View::$vars->comment_list[] = $row->comment_id;
}


// Output Page
Plugin::Trigger ('play.before_render');
View::Render ('play.tpl');

?>