<?php

Plugin::triggerEvent('mobile_watch_later.start');
Functions::redirectIf((boolean) Settings::get('mobile_site'), HOST . '/');

// Verify if user is logged in
$userService = new UserService();
$this->view->vars->loggedInUser = $userService->loginCheck();
Functions::redirectIf($this->view->vars->loggedInUser, MOBILE_HOST . '/');

// Retrieve playlist videos
$playlistService = new PlaylistService();
$this->view->vars->playlist = $playlistService->getUserSpecialPlaylist($this->view->vars->loggedInUser, 'watch_later');
$this->view->vars->videoList = $playlistService->getPlaylistVideos($this->view->vars->playlist);

Plugin::triggerEvent('mobile_watch_later.end');