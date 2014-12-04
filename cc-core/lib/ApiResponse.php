<?php

class ApiResponse
{
    /**
     * @var boolean Result of API call 
     */
    public $result;
    
    /**
     * @var string Message regarding status or result of API call 
     */
    public $message = '';
    
    /**
     * @var mixed Data provided by API
     */
    public $data = array();
}