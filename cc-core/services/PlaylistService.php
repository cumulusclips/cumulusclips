<?php

class PlaylistService extends ServiceAbstract
{
    public function getUrl(Playlist $playlist)
    {
        if (!empty($playlist->entries)) {
            $videoMapper = new VideoMapper();
            $videoService = new VideoService();
            $firstVideo = $videoMapper->getVideoById($playlist->entries[0]->videoId);
            return $videoService->getUrl($firstVideo) . '/?playlist=' . $playlist->playlistId;
        } else {
            return false;
        }
    }

    public function getPlaylistVideos(Playlist $playlist)
    {
        $videoIds = Functions::arrayColumn($playlist->entries, 'videoId');
        $videoMapper = new VideoMapper();
        return $videoMapper->getVideosFromList($videoIds);
    }

    public function getUserSpecialPlaylist(User $user, $type)
    {
        // Validator playlist type
        if (!in_array($type, array(
            \PlaylistMapper::TYPE_WATCH_LATER,
            \PlaylistMapper::TYPE_FAVORITES
        ))) {
            throw new Exception('Unknown special playlist type.');
        }

        $playlistMapper = $this->_getMapper();
        return $playlistMapper->getPlaylistByCustom(array('user_id' => $user->userId, 'type' => $type));
    }

    /**
     * Adds video to playlist
     *
     * @param \Video $video Video to be added
     * @param \Playlist $playlist Playlist being added to
     * @return \Playlist Returns updated playlist
     * @throws \Exception Thrown if video is already in playlist
     */
    public function addVideoToPlaylist(Video $video, Playlist $playlist)
    {
        // Verify video is not already in playlist
        if ($this->checkListing($video, $playlist)) {
            throw new Exception('Video already listed in playlist');
        }

        $playlistMapper = $this->_getMapper();
        $playlistEntryMapper = new PlaylistEntryMapper();

        // Create new entry
        $playlistEntry = new PlaylistEntry();
        $playlistEntry->playlistId = $playlist->playlistId;
        $playlistEntry->videoId = $video->videoId;
        $playlistEntryMapper->save($playlistEntry);

        // Retrieve updated playlist
        return $playlistMapper->getPlaylistById($playlist->playlistId);
    }

    public function checkListing(Video $video, Playlist $playlist)
    {
        $playlistEntryMapper = new PlaylistEntryMapper();
        return (boolean) $playlistEntryMapper->getPlaylistEntryByCustom(array(
            'playlist_id' => $playlist->playlistId,
            'video_id' => $video->videoId
        ));
    }

    public function getPlaylistName(Playlist $playlist)
    {
        switch ($playlist->type) {
            case \PlaylistMapper::TYPE_FAVORITES:
                return Language::GetText('favorites');
            case \PlaylistMapper::TYPE_WATCH_LATER:
                return Language::GetText('watch_later');
            default:
                return $playlist->name;
        }
    }

    public function delete(Playlist $playlist)
    {
        // Delete all playlist entries
        $playlistEntryMapper = new PlaylistEntryMapper();
        foreach ($playlist->entries as $playlistEntry) {
            $playlistEntryMapper->delete($playlistEntry->playlistEntryId);
        }

        // Delete playlist
        $playlistMapper = $this->_getMapper();
        $playlistMapper->delete($playlist->playlistId);
    }

    /**
     * Removes video from playlist
     *
     * @param \Video $video Video to be removed
     * @param \Playlist $playlist Playlist being removed from
     * @return \Playlist Returns updated playlist
     * @throws \Exception Thrown if video is not in playlist
     */
    public function deleteVideo(Video $video, Playlist $playlist)
    {
        // Check if video is in playlist
        if (!$this->checkListing($video, $playlist)) {
            throw new Exception('Video not found in playlist');
        }

        $playlistEntryMapper = new PlaylistEntryMapper();
        $playlistMapper = $this->_getMapper();

        // Remove entry
        $playlistEntry = $playlistEntryMapper->getPlaylistEntryByCustom(array(
            'playlist_id' => $playlist->playlistId,
            'video_id' => $video->videoId
        ));
        $playlistEntryMapper->delete($playlistEntry->playlistEntryId);

        // Retrieve updated playlist
        return $playlistMapper->getPlaylistById($playlist->playlistId);
    }

    /**
     * Retrieve instance of Playlist mapper
     * @return PlaylistMapper Mapper is returned
     */
    protected function _getMapper()
    {
        return new PlaylistMapper();
    }
}