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

function getPlaylistThumbnail(Playlist $playlist)
{
    $config = Registry::get('config');
    $videoMapper = new VideoMapper();
    $video = $videoMapper->getVideoById($playlist->entries[0]->videoId);
    return $config->thumb_url . '/' . $video->filename . '.jpg';
}