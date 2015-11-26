<?php

class CommentCard
{
    /**
     * @var Comment Instance of the comment represented by the card
     */
    public $comment;
    
    /**
     * @var User Instance of user who authored comment, null if author is unregistered
     */
    public $author;
    
    /**
     * @var Comment Instance of the comment the current comment is replying to
     */
    public $parentComment;
    
    /**
     *@var User Instance of user who authored parent comment, null if author is unregistered
     */
    public $parentAuthor;
    
    /**
     * @var string Name of file for the comment author's avatar, null if author is unregistered or avatar is not set
     */
    public $avatar;
}