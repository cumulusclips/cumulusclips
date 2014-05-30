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

function getLastCommentId(array $commentList)
{
    if (count($commentList) > 0) {
        $lastComment = array_pop($commentList);
        return $lastComment->commentId;
    } else {
        return false;
    }
}

function getCommentAuthorText(Comment $comment, User $author = null)
{
    if ($comment->userId == 0) {
        return $comment->name;
    } else {
        return '<a href="' . getUserProfileLink($author) . '">' . $author->username . '</a>';
    }
}

function getUserProfileLink(User $user)
{
    return HOST . '/members/' . $user->username;
}