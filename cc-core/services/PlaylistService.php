<?php

class PlaylistService extends ServiceAbstract
{
    
    public function getPlaylistVideos(Playlist $playlist)
    {
        $videoIds = Functions::arrayColumn($playlist->entries, 'videoId');
        $videoMapper = new VideoMapper();
        return $videoMapper->getVideosFromList($videoIds);
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
            case 'playlist':
                return $playlist->name;
            default:
                throw new Exception('Invalid playlist name');
        }
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