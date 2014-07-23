<?php

function getCommentThread($currentCommentThread, Comment $comment)
{
    if ($comment->parentId == 0) {
        return $comment->commentId;
    } else {
        return $currentCommentThread;
    }
}

function getCommentIndentClass($currentCommentThread, Comment $comment)
{
    if ($comment->parentId == 0) {
        return '';
    } else if ($comment->parentId == $currentCommentThread) {
        return 'commentIndent';
    } else {
        return 'commentIndentDouble';
    }
}

function getUserProfileLink(User $user)
{
    return HOST . '/members/' . $user->username;
}

function getPlaylistThumbnails(Playlist $playlist)
{
    $config = Registry::get('config');
    $videoIds = Functions::arrayColumn(array_slice($playlist->entries, 0, 3), 'videoId');
    $videoMapper = new VideoMapper();
    $thumbnailList = array();
    foreach ($videoMapper->getVideosFromList($videoIds) as $video) {
        $thumbnailList[] = $config->thumb_url . '/' . $video->filename . '.jpg';
    }
    return $thumbnailList;
}