<?php

// Verify if user is logged in
$loggedInUser = $this->authService->getAuthUser();

// Establish page variables, objects, arrays, etc
$this->view->options->disableView = true;
$videoMapper = new VideoMapper();
$playlistMapper = new PlaylistMapper();
$playlistService = new PlaylistService();

// Verify a valid video was provided
if (empty($_POST['video_id']) || !is_numeric($_POST['video_id'])) App::throw404();
$video = $videoMapper->getVideoByCustom(array('video_id' => $_POST['video_id'], 'status' => 'approved'));
if (!$video) App::throw404();

// Verify user is logged in
if (!$loggedInUser) {
    $apiResponse = new \ApiResponse();
    $apiResponse->statusCode = \ApiResponse::HTTP_UNAUTHORIZED;
    $apiResponse->result = false;
    $apiResponse->message = (string) Language::getText('playlist_login', array('url' => HOST . '/login/'));
    \ApiResponse::sendResponse($apiResponse);
}

// Determine if playlist is being created or modified
if (empty($_POST['action']) || !in_array($_POST['action'], array('add', 'create', 'remove'))) App::throw404();
if ($_POST['action'] == 'add') {

    // Verify a valid playlist was provided
    if (empty($_POST['playlist_id']) || !is_numeric($_POST['playlist_id'])) App::Throw404();
    $playlist = $playlistMapper->getPlaylistByCustom(array('playlist_id' => $_POST['playlist_id'], 'user_id' => $loggedInUser->userId));
    if (!$playlist) App::throw404();

    // Add video to playlist if not already in list
    if (!$playlistService->checkListing($video, $playlist)) {
        $playlist = $playlistService->addVideoToPlaylist($video, $playlist);
        $playlistName = $playlistService->getPlaylistName($playlist);
        $message = (!empty($_POST['shortText'])) ? 'success_playlist_added_short' : 'success_playlist_added';

        // Send response
        $apiResponse = new \ApiResponse();
        $apiResponse->result = true;
        $apiResponse->statusCode = \ApiResponse::HTTP_CREATED;
        $apiResponse->message = (string) Language::getText($message, array('list_name' => $playlistName));
        $apiResponse->other = (object) array('count' => count($playlist->entries));
        \ApiResponse::sendResponse($apiResponse);

    } else {

        // Send response
        $apiResponse = new \ApiResponse();
        $apiResponse->result = false;
        $apiResponse->code = \ApiResponse::HTTP_CONFLICT;
        $apiResponse->message = (string) Language::getText('error_playlist_duplicate');
        \ApiResponse::sendResponse($apiResponse);
    }

} elseif ($_POST['action'] === 'remove') {

    // Verify a valid playlist was provided
    if (empty($_POST['playlist_id']) || !is_numeric($_POST['playlist_id'])) App::Throw404();
    $playlist = $playlistMapper->getPlaylistByCustom(array('playlist_id' => $_POST['playlist_id'], 'user_id' => $loggedInUser->userId));
    if (!$playlist) App::throw404();

    // Remove video from playlist if not already in list
    if ($playlistService->checkListing($video, $playlist)) {
        $playlist = $playlistService->deleteVideo($video, $playlist);
        $playlistName = $playlistService->getPlaylistName($playlist);
        $message = (!empty($_POST['shortText'])) ? 'success_playlist_video_removed_short' : 'success_playlist_video_removed';

        // Send response
        $apiResponse = new \ApiResponse();
        $apiResponse->result = true;
        $apiResponse->message = (string) Language::getText($message, array('list_name' => $playlistName));
        $apiResponse->other = (object) array('count' => count($playlist->entries));
        \ApiResponse::sendResponse($apiResponse);

    } else {

        // Send response
        $apiResponse = new \ApiResponse();
        $apiResponse->result = false;
        $apiResponse->statusCode = \ApiResponse::HTTP_NOT_FOUND;
        $apiResponse->message = (string) Language::getText('error_playlist_video_not_found');
        \ApiResponse::sendResponse($apiResponse);
    }

} else {

    $playlist = new Playlist();
    $playlist->userId = $loggedInUser->userId;

    // Validate playlist name
    if (!empty($_POST['playlist_name'])) {
        $playlist->name = trim($_POST['playlist_name']);
    } else {

        // Send response
        $apiResponse = new \ApiResponse();
        $apiResponse->result = false;
        $apiResponse->statusCode = \ApiResponse::HTTP_BAD_REQUEST;
        $apiResponse->message = (string) Language::getText('error_playlist_name');
        \ApiResponse::sendResponse($apiResponse);
    }

    // Validate playlist visibility
    if (!empty($_POST['playlist_visibility']) && $_POST['playlist_visibility'] == 'public') {
        $playlist->public = true;
    } else {
        $playlist->public = false;
    }

    $playlistId = $playlistMapper->save($playlist);
    $newPlaylist = $playlistMapper->getPlaylistById($playlistId);
    $playlistService->addVideoToPlaylist($video, $newPlaylist);

    // Send response
    $apiResponse = new \ApiResponse();
    $apiResponse->result = true;
    $apiResponse->statusCode = \ApiResponse::HTTP_CREATED;
    $apiResponse->message = (string) Language::getText('success_playlist_created');
    $apiResponse->other = (object) array('name' => $newPlaylist->name, 'count' => 1, 'playlistId' => $playlistId);
    \ApiResponse::sendResponse($apiResponse);
}