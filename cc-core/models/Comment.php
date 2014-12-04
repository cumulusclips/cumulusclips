<?php

class Comment
{
    /**
     * @var int 
     */
    public $commentId;
    
    /**
     * @var int
     */
    public $userId;
    
    /**
     * @var int
     */
    public $videoId;
    
    /**
     *@var int 
     */
    public $parentId;
    
    /**
     * @var string
     */
    public $comments;
    
    /**
     * @var string
     */
    public $dateCreated;
    
    /**
     * @var string
     */
    public $status;
    
    /**
     * @var boolean
     */
    public $released;
}