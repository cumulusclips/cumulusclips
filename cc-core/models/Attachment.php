<?php

class Attachment extends Model
{
    /**
     * @var int ID of attachment entry
     */
    public $attachmentId;

    /**
     * @var int ID of attached file
     */
    public $fileId;

    /**
     * @var int ID of video attachment is for
     */
    public $videoId;

    /**
     * @var boolean Whether the attachment was made by a staff member
     */
    public $byStaff;

    /**
     * @var \DateTime Time and date attachment was made
     */
    public $dateCreated;
}