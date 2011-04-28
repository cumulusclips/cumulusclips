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
App::LoadClass ('Flag');
App::LoadClass ('Favorite');
App::LoadClass ('Comment');
App::LoadClass ('Privacy');
App::LoadClass ('EmailTemplate');
View::InitView();


// Establish page variables, objects, arrays, etc
View::LoadPage ('play');
View::$vars->logged_in = User::LoginCheck();
View::$vars->tags = NULL;



// Validate requested video
$data = array ('video_id' => $_GET['vid'], 'status' => 6);
if (!isset ($_GET['vid']) || !is_numeric ($_GET['vid']) || !Video::Exist ($data)) {
    App::Throw404();
}



// Assign data to variables
View::$vars->video = new Video ($_GET['vid']);
View::$vars->member = new User (View::$vars->video->user_id);
View::$vars->video->Update (array ('views' => View::$vars->video->views+1));
View::$vars->rating = new Rating (View::$vars->video->video_id);
View::$vars->meta->title = View::$vars->video->title;
View::$vars->meta->keywords = implode (', ',View::$vars->video->tags);
View::$vars->meta->description = View::$vars->video->description;



// Retrieve user data if logged in
if (View::$vars->logged_in) {
    View::$vars->user = new User (View::$vars->logged_in);
    $data = array ('member' => View::$vars->video->user_id, 'user_id' => View::$vars->user->user_id);
    View::$vars->subscribe_text = (Subscription::Exist ($data)) ? 'unsubscribe' : 'subscribe';
} else {
    View::$vars->subscribe_text = 'subscribe';
}



### Create Tags Links
foreach (View::$vars->video->tags as $value) {
    View::$vars->tags .= '<a href="' . HOST . '/search/?keyword=' . $value . '" title="' . $value . '">' . $value . '</a>&nbsp;&nbsp; ';
}



### Retrieve related videos
$search_terms = $db->Escape (View::$vars->video->title) . ' ' . $db->Escape (implode (' ', View::$vars->video->tags));
$query = "SELECT video_id FROM videos WHERE MATCH(title, tags, description) AGAINST ('$search_terms') AND status = 6 LIMIT 9";
View::$vars->result_related = $db->Query ($query);



### Retrieve comment count
$query = "SELECT COUNT(comment_id) FROM comments WHERE video_id = " . View::$vars->video->video_id . " AND status = 'approved'";
$result_comment_count = $db->Query ($query);
$comment_count = $db->FetchRow ($result_comment_count);
View::$vars->comment_count = $comment_count[0];



### Retrieve comments
$query = "SELECT comment_id FROM comments WHERE video_id = " . View::$vars->video->video_id . " AND status = 'approved' ORDER BY comment_id DESC LIMIT 0, 5";
$result_comments = $db->Query ($query);
View::$vars->comment_count = $db->Count ($result_comments);
View::$vars->comment_list = array();
while ($row = $db->FetchObj ($result_comments)) {
    View::$vars->comment_list[] = $row->comment_id;
}



// Output Page
View::AddMeta('baseURL', HOST);
View::AddJs('flowplayer.plugin.js');
View::AddJs('play.js');
View::SetLayout('full.layout.tpl');
View::Render ('play.tpl');

?>