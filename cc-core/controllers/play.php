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
View::$vars->logged_in = User::LoginCheck();
View::$vars->page_title = 'Techie Videos - ';
View::$vars->tags = NULL;
$keywords = NULL;
$continue = TRUE;
View::$vars->subscribed = NULL;
$data = array();
View::$vars->Success = NULL;
View::$vars->Errors = NULL;



// Verify a video was selected
if (isset ($_GET['vid']) && is_numeric ($_GET['vid'])) {
	View::$vars->video = new Video ($_GET['vid']);
} else {
    App::Throw404();
}



// Check if video is valid
if (!View::$vars->video->found || View::$vars->video->status != 6) {
    App::Throw404();
}



// Retrieve user data if logged in
if (View::$vars->logged_in) {
    View::$vars->user = new User (View::$vars->logged_in);
    $data = array ('member' => View::$vars->video->user_id, 'user_id' => View::$vars->user->user_id);
    $id = Subscription::Exist ($data);
    $subscribed = ($id)?$id:FALSE;
} else {
    $subscribed = FALSE;
}



// Assign data to variables
View::$vars->rating = new Rating (View::$vars->video->video_id);
$date_uploaded = View::$vars->video->date_created;
$video_file = View::$vars->video->filename . '.flv';
$video_url = HOST . '/videos/' . View::$vars->video->video_id . '/' . View::$vars->video->slug . '/';
$video_image = View::$vars->video->filename . '.jpg';
View::$vars->page_title .= View::$vars->video->title;
$url = HOST . '/videos/' . View::$vars->video->video_id . '/' . View::$vars->video->slug . '/';
$channel = new User (View::$vars->video->user_id);
$picture = $channel->avatar;



### Update video view count
$data = array ('views' => View::$vars->video->views+1);
View::$vars->video->Update ($data);



### Create Tags Links
$tags = '';
foreach (View::$vars->video->tags as $value) {
    $tags .= '<a href="' . HOST . '/search/?keyword=' . $value . '" title="' . $value . '">' . $value . '</a>&nbsp;&nbsp; ';
    $keywords .= $value . ', ';
}
$keywords = substr($keywords, 0, -2);
$match_keywords = str_replace (',', '', $keywords);



### Retrieve related videos
$search_terms = mysql_real_escape_string (View::$vars->video->title) . ' ' . mysql_real_escape_string ($match_keywords);
$query = "SELECT video_id FROM videos WHERE MATCH(title, tags, description) AGAINST ('$search_terms') AND status = 6 LIMIT 9";
View::$vars->result_related = $db->Query ($query);





/******************
Prepare page to run
******************/

### Retrieve video comments
$query = "SELECT COUNT(comment_id) FROM comments WHERE status = 1 AND video_id = " . View::$vars->video->video_id;
$result_count = $db->Query($query);
View::$vars->count = $db->FetchRow ($result_count);
$query = "SELECT comment_id FROM comments WHERE video_id = " . View::$vars->video->video_id . " AND status = 1 ORDER BY comment_id DESC LIMIT 0, 5";
View::$vars->result_comments = $db->Query ($query);


// Output Page
View::AddJs('flowplayer.plugin.js');
View::AddJs('play.js');
View::SetLayout('full.layout.tpl');
View::Render ('play.tpl');

?>