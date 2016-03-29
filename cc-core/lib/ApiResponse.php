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

    /**
     * Converts API response object to a string
     * @return string Returns string representation of the object
     */
    public function __toString()
    {
        return json_encode($this);
    }
}