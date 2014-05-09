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
     * @var string
     */
    public $email;
    
    /**
     * @var string
     */
    public $name;
    
    /**
     * @var string
     */
    public $website;
    
    /**
     * @var string
     */
    public $ip;
    
    /**
     * @var string
     */
    public $userAgent;
    
    /**
     * @var boolean
     */
    public $released;
}