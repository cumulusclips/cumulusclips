<?php

function getLastCommentId(array $commentList)
{
    if (count($commentList) > 0) {
        $lastComment = array_pop($commentList);
        return $lastComment->commentId;
    } else {
        return false;
    }
}

function getCommentAuthorText(Comment $comment)
{
    if ($comment->userId == 0) {
        return $comment->name;
    } else {
        return '<a href="' . getUserProfileLink($comment->author) . '">' . $comment->author->username . '</a>';
    }
}

function getUserProfileLink(User $user)
{
    return HOST . '/members/' . $user->username;
}