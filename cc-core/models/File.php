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
     * @var int 
     */
    public $userId;
    
    /**
     * @var string 
     */
    public $title;
    
    /**
     * @var string 
     */
    public $description;
    
    /**
     * @var int Filesize in KB 
     */
    public $filesize;
    
    /**
     * @var string 
     */
    public $extension;
    
    /**
     * @var boolean 
     */
    public $attachable;
  
    /**
     * @var string 
     */
    public $dateCreated;
}