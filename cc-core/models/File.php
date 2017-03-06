<?php

class File extends Model
{
    /**
     * @var int
     */
    public $fileId;

    /**
     * @var string
     */
    public $filename;

    /**
     * @var string
     */
    public $type;

    /**
     * @var int
     */
    public $userId;

    /**
     * @var string Name assigned to file
     */
    public $name;

    /**
     * @var int Filesize in bytes
     */
    public $filesize;

    /**
     * @var string
     */
    public $extension;

    /**
     * @var \DateTime
     */
    public $dateCreated;
}