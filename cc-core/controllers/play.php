<?php

// Establish page variables, objects, arrays, etc
$view->initView('play');
Plugin::triggerEvent('play.start');

// Verify if user is logged in
$userService = new UserService();
$view->vars->loggedInUser = $userService->loginCheck();

$userMapper = new UserMapper();
$videoMapper = new VideoMapper();
$videoService = new VideoService();
$view->vars->tags = null;
$view->vars->private = null;
$view->vars->vp8Options = json_decode(Settings::Get('vp8Options'));

// Validate requested video
if (!empty($_GET['vid']) && $video = $videoMapper->getVideoByCustom(array('video_id' => $_GET['vid'], 'status' => 'approved'))) {

    $view->vars->video = $video;
    $view->vars->comments_url = HOST . '/videos/' . $view->vars->video->videoId . '/comments';

    // Prevent direct access to video to all users except owner
    if ($view->vars->video->private == '1' && $view->vars->loggedInUser->userId != $view->vars->video->userId) {
        App::Throw404();
    }

} else if (!empty($_GET['private']) && $video = $videoMapper->getVideoByCustom(array('status' => 'approved', 'private_url' => $_GET['private']))) {
    $view->vars->video = $video;
    $view->vars->private = true;
    $view->vars->comments_url = HOST . '/private/comments/' . $view->vars->video->privateUrl;
} else if (!empty($_GET['get_private'])) {
    exit($videoService->generatePrivate());
} else {
    App::Throw404();
}

// Load video data for page rendering
$view->vars->member = $userMapper->getUserById($view->vars->video->userId);
$view->vars->video->views++;
$videoMapper->save($view->vars->video);
$view->vars->rating = RatingService::getRating($view->vars->video->videoId);
$view->vars->meta->title = $view->vars->video->title;
$view->vars->meta->keywords = implode (', ',$view->vars->video->tags);
$view->vars->meta->description = $view->vars->video->description;
Plugin::triggerEvent('play.load_video');

// Retrieve user data if logged in
if ($view->vars->loggedInUser) {
    $subscriptionService = new SubscriptionService();
    $view->vars->subscribe_text = ($subscriptionService->checkSubscription($view->vars->loggedInUser->userId, $view->vars->video->userId)) ? 'unsubscribe' : 'subscribe';
} else {
    $view->vars->subscribe_text = 'subscribe';
}

// Retrieve count of all videos
$query = "SELECT COUNT(video_id) as total FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND private = '0'";
$total = $db->fetchRow($query);

### Retrieve related videos
if ($total['total'] > 20) {
    // Use FULLTEXT query
    $search_terms = $view->vars->video->title . ' ' . implode (' ', $view->vars->video->tags);
    $query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE MATCH(title, tags, description) AGAINST (:searchTerms) AND status = 'approved' AND private = '0' AND video_id != :videoId LIMIT 9";
    $view->vars->result_related = $db->fetchAll($query, array(':searchTerms' => $search_terms, ':videoId' => $view->vars->video->video_id));
} else {
    // Use LIKE query
    $replacements = array(':videoId' => $view->vars->video->videoId);
    $tags = $view->vars->video->tags;
    foreach ($tags as $key => $tag) {
        $sub_queries[] = "video_id IN (SELECT video_id FROM " . DB_PREFIX . "videos WHERE title LIKE :tag$key OR description LIKE :tag$key OR tags LIKE :tag$key)";
        $replacements[':tag' . $key] = '%' . $tag . '%';
    }

    $sub_queries = implode(' OR ', $sub_queries);
    $query = 'SELECT video_id FROM ' . DB_PREFIX . 'videos WHERE (' . $sub_queries . ') AND status = "approved" AND private = "0" AND video_id != :videoId LIMIT 9';
    $view->vars->result_related = $db->fetchAll($query, $replacements);
}
Plugin::triggerEvent('play.load_suggestions');

### Retrieve comment count
$query = 'SELECT COUNT(comment_id) as count FROM ' . DB_PREFIX . 'comments WHERE video_id = :videoId AND status = "approved"';
Plugin::triggerEvent('play.comment_count');
$result_comment_count = $db->fetchRow($query, array(':videoId' => $view->vars->video->videoId));
$view->vars->comment_count = $result_comment_count['count'];

### Retrieve comments
$query = 'SELECT comment_id FROM ' . DB_PREFIX . 'comments WHERE video_id = :videoId AND status = "approved" ORDER BY comment_id DESC LIMIT 0, 5';
Plugin::triggerEvent('play.load_comments');
$view->vars->comment_list = $db->fetchAll($query, array(':videoId' => $view->vars->video->videoId));

// Output Page
Plugin::triggerEvent('play.before_render');
$view->render('play.tpl');