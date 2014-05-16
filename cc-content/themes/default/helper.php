<?php

function getCommentLoadMoreOffset(array $commentList)
{
    if (count($commentList) > 0) {
        $lastComment = array_pop($commentList);
        return $lastComment->commentId;
    } else {
        return false;
    }
}

function getCommentTree($currentCommentTree, Comment $comment)
{
    if ($comment->parentId == 0) {
        return $comment->commentId;
    } else {
        return $currentCommentTree;
    }  
}

function getCommentIndentClass($currentCommentTree, Comment $comment)
{
    if ($comment->parentId == 0) {
        return '';
    } else if ($comment->parentId == $currentCommentTree) {
        return 'commentIndent';
    } else {
        return 'commentIndentDouble';
    }
}

function getCommentAuthorText(Comment $comment)
{
    if ($comment->userId == 0) {
        return $comment->name;
    } else {
        $userMapper = new UserMapper();
        $author = $userMapper->getUserById($comment->userId);
        return '<a href="' . getUserProfileLink($author) . '">' . $author->username . '</a>';
    }
}

function getUserProfileLink(User $user)
{
    return HOST . '/members/' . $user->username;
}