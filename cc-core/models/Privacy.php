<?php

class Privacy
{
    const NEW_VIDEO = 'new_video';
    const VIDEO_COMMENT = 'video_comment';
    const NEW_MESSAGE = 'new_message';
    const VIDEO_READY = 'video_ready';

    /**
     * @var int
     */
    public $privacyId;
    
    /**
     * @var int
     */
    public $userId;
    
    /**
     * @var boolean
     */
    public $videoComment;
    
    /**
     * @var boolean
     */
    public $newMessage;
    
    /**
     * @var boolean
     */
    public $newVideo;
    
    /**
     * @var boolean
     */
    public $videoReady;
}