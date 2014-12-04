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
        if (!in_array($type, array('watch_later', 'favorites'))) throw new Exception('Unknown special playlist type.');
        $playlistMapper = $this->_getMapper();
        return $playlistMapper->getPlaylistByCustom(array('user_id' => $user->userId, 'type' => $type));
    }
    
    public function addVideoToPlaylist(Video $video, Playlist $playlist)
    {
        $playlistEntryMapper = new PlaylistEntryMapper();
        $playlistEntry = new PlaylistEntry();
        $playlistEntry->playlistId = $playlist->playlistId;
        $playlistEntry->videoId = $video->videoId;
        $playlistEntryMapper->save($playlistEntry);
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
            case 'favorites':
                return Language::GetText('favorites');
            case 'watch_later':
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
    
    public function deleteVideo(Video $video, Playlist $playlist)
    {
        if (!$this->checkListing($video, $playlist)) throw new Exception('Video not found in playlist');
        $playlistEntryMapper = new PlaylistEntryMapper();
        $playlistEntry = $playlistEntryMapper->getPlaylistEntryByCustom(array(
            'playlist_id' => $playlist->playlistId,
            'video_id' => $video->videoId
        ));      
        $playlistEntryMapper->delete($playlistEntry->playlistEntryId);
        $key = array_search($playlistEntry, $playlist->entries);
        unset($playlist->entries[$key]);
        return $playlist;
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