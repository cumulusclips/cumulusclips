<?php

class Video
{
    /**
     * @var int
     */
    public $videoId;

    /**
     * @var string
     */
    public $filename;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $description;

    /**
     * @var array
     */
    public $tags;

    /**
     * @var int
     */
    public $categoryId;

    /**
     * @var int
     */
    public $userId;

    /**
     * @var string
     */
    public $username;

    /**
     * @var string
     */
    public $dateCreated;

    /**
     * @var string
     */
    public $duration;

    /**
     * @var string
     */
    public $status;

    /**
     * @var int
     */
    public $views;

    /**
     * @var string
     */
    public $originalExtension;

    /**
     * @var boolean
     */
    public $featured;

    /**
     * @var boolean
     */
    public $gated;

    /**
     * @var boolean
     */
    public $released;

    /**
     * @var int System PID for the video's transcoding process
     */
    public $jobId;

    /**
     * @var boolean
     */
    public $disableEmbed;

    /**
     * @var boolean
     */
    public $private;

    /**
     * @var string
     */
    public $privateUrl;

    /**
     * @var boolean
     */
    public $commentsClosed;
}