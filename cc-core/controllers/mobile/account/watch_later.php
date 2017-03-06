<?php

Plugin::triggerEvent('mobile_watch_later.start');
Functions::redirectIf((boolean) Settings::get('mobile_site'), HOST . '/');

// Verify if user is logged in
$this->authService->enforceAuth();
$this->authService->enforceTimeout(true);
$this->view->vars->loggedInUser = $this->authService->getAuthUser();

// Retrieve playlist videos
$playlistService = new PlaylistService();
$this->view->vars->playlist = $playlistService->getUserSpecialPlaylist($this->view->vars->loggedInUser, \PlaylistMapper::TYPE_WATCH_LATER);
$this->view->vars->videoList = $playlistService->getPlaylistVideos($this->view->vars->playlist);

Plugin::triggerEvent('mobile_watch_later.end');