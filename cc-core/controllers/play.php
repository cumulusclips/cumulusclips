<?php

// Establish page variables, objects, arrays, etc
View::initView('play');
Plugin::triggerEvent('play.start');

// Verify if user is logged in
$userService = new UserService();
View::$vars->loggedInUser = $userService->loginCheck();

$userMapper = new UserMapper();
$videoMapper = new VideoMapper();
$videoService = new VideoService();
View::$vars->tags = null;
View::$vars->private = null;
View::$vars->vp8Options = json_decode(Settings::Get('vp8Options'));

// Validate requested video
if (!empty($_GET['vid']) && $video = $videoMapper->getVideoByCustom(array('video_id' => $_GET['vid'], 'status' => 'approved'))) {

    View::$vars->video = $video;
    View::$vars->comments_url = HOST . '/videos/' . View::$vars->video->videoId . '/comments';

    // Prevent direct access to video to all users except owner
    if (View::$vars->video->private == '1' && View::$vars->loggedInUser->userId != View::$vars->video->userId) {
        App::Throw404();
    }

} else if (!empty($_GET['private']) && $video = $videoMapper->getVideoByCustom(array('status' => 'approved', 'private_url' => $_GET['private']))) {
    View::$vars->video = $video;
    View::$vars->private = true;
    View::$vars->comments_url = HOST . '/private/comments/' . View::$vars->video->privateUrl;
} else if (!empty($_GET['get_private'])) {
    exit($videoService->generatePrivate());
} else {
    App::Throw404();
}

// Load video data for page rendering
View::$vars->member = $userMapper->getUserById(View::$vars->video->userId);
View::$vars->video->views++;
$videoMapper->save(View::$vars->video);
View::$vars->rating = RatingService::getRating(View::$vars->video->videoId);
View::$vars->meta->title = View::$vars->video->title;
View::$vars->meta->keywords = implode (', ',View::$vars->video->tags);
View::$vars->meta->description = View::$vars->video->description;
Plugin::triggerEvent('play.load_video');

// Retrieve user data if logged in
if (View::$vars->loggedInUser) {
    $subscriptionService = new SubscriptionService();
    View::$vars->subscribe_text = ($subscriptionService->checkSubscription(View::$vars->loggedInUser->userId, View::$vars->video->userId)) ? 'unsubscribe' : 'subscribe';
} else {
    View::$vars->subscribe_text = 'subscribe';
}

// Retrieve count of all videos
$query = "SELECT COUNT(video_id) as total FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND private = '0'";
$total = $db->fetchRow($query);

### Retrieve related videos
if ($total['total'] > 20) {
    // Use FULLTEXT query
    $search_terms = View::$vars->video->title . ' ' . implode (' ', View::$vars->video->tags);
    $query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE MATCH(title, tags, description) AGAINST (:searchTerms) AND status = 'approved' AND private = '0' AND video_id != :videoId LIMIT 9";
    View::$vars->result_related = $db->fetchAll($query, array(':searchTerms' => $search_terms, ':videoId' => View::$vars->video->video_id));
} else {
    // Use LIKE query
    $replacements = array(':videoId' => View::$vars->video->videoId);
    $tags = View::$vars->video->tags;
    foreach ($tags as $key => $tag) {
        $sub_queries[] = "video_id IN (SELECT video_id FROM " . DB_PREFIX . "videos WHERE title LIKE :tag$key OR description LIKE :tag$key OR tags LIKE :tag$key)";
        $replacements[':tag' . $key] = '%' . $tag . '%';
    }

    $sub_queries = implode(' OR ', $sub_queries);
    $query = 'SELECT video_id FROM ' . DB_PREFIX . 'videos WHERE (' . $sub_queries . ') AND status = "approved" AND private = "0" AND video_id != :videoId LIMIT 9';
    View::$vars->result_related = $db->fetchAll($query, $replacements);
}
Plugin::triggerEvent('play.load_suggestions');

### Retrieve comment count
$query = 'SELECT COUNT(comment_id) as count FROM ' . DB_PREFIX . 'comments WHERE video_id = :videoId AND status = "approved"';
Plugin::triggerEvent('play.comment_count');
$result_comment_count = $db->fetchRow($query, array(':videoId' => View::$vars->video->videoId));
View::$vars->comment_count = $result_comment_count['count'];

### Retrieve comments
$query = 'SELECT comment_id FROM ' . DB_PREFIX . 'comments WHERE video_id = :videoId AND status = "approved" ORDER BY comment_id DESC LIMIT 0, 5';
Plugin::triggerEvent('play.load_comments');
View::$vars->comment_list = $db->fetchAll($query, array(':videoId' => View::$vars->video->videoId));

// Output Page
Plugin::triggerEvent('play.before_render');
View::render('play.tpl');