<?php

class Playlist
{
    /**
     * @var int
     */
    public $playlistId;

    /**
     * @var string
     */
    public $name;

    /**
     * @var int
     */
    public $userId;

    /**
     * @var string
     */
    public $type;

    /**
     * @var boolean
     */
    public $public;

    /**
     * @var string
     */
    public $dateCreated;

    /**
     * @var \PlaylistEntry[]
     */
    public $entries;
}