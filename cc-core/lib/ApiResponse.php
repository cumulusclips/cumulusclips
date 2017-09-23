<?php

class ApiResponse
{
    /**
     * @var int HTTP status code for successful action
     */
    const HTTP_SUCCESS = 200;

    /**
     * @var int HTTP status code for when resource is successfully created
     */
    const HTTP_CREATED = 201;

    /**
     * @var int HTTP status code when bad request was made by client
     */
    const HTTP_BAD_REQUEST = 400;

    /**
     * @var int HTTP status code when login is required for action
     */
    const HTTP_UNAUTHORIZED = 401;

    /**
     * @var int HTTP status code user does not have permission for action
     */
    const HTTP_FORBIDDEN = 403;

    /**
     * @var int HTTP status code resource does not exist
     */
    const HTTP_NOT_FOUND = 404;

    /**
     * @var int HTTP status code when resource already exists
     */
    const HTTP_CONFLICT = 409;

    /**
     * @var int HTTP status code when internal errors occur
     */
    const HTTP_SERVER_ERROR = 500;

    /**
     * @var int HTTP status code for response
     */
    public $statusCode;

    /**
     * @var boolean Result of API call
     */
    public $result;

    /**
     * @var string Message regarding status or result of API call
     */
    public $message;

    /**
     * @var mixed Data provided by API
     */
    public $data = null;

    /**
     * @var mixed Information pertinent to response
     */
    public $other = null;

    /**
     * Converts API response object to a string
     * @return string Returns string representation of the object
     */
    public function __toString()
    {
        return json_encode($this);
    }

    /**
     * Outputs HTTP response to buffer
     *
     * @param \ApiResponse $response The response to be output
     */
    public static function sendResponse(\ApiResponse $response)
    {
        $statusCode = empty($response->statusCode) ? static::HTTP_SUCCESS : $response->statusCode;
        self::sendHeader($statusCode);
        header('Content-Type: application/json');
        echo json_encode(array(
            'result' => $response->result,
            'message' => $response->message,
            'data' => $response->data,
            'other' => $response->other
        ));
        exit();
    }

    /**
     * Sends HTTP header for given status code
     *
     * @param int $statusCode Status code to output
     */
    protected static function sendHeader($statusCode)
    {
        switch ($statusCode) {
            case 200: $text = 'OK'; break;
            case 201: $text = 'Created'; break;
            case 400: $text = 'Bad Request'; break;
            case 401: $text = 'Unauthorized'; break;
            case 403: $text = 'Forbidden'; break;
            case 404: $text = 'Not Found'; break;
            case 405: $text = 'Method Not Allowed'; break;
            case 409: $text = 'Conflict'; break;
            case 413: $text = 'Request Entity Too Large'; break;
            case 415: $text = 'Unsupported Media Type'; break;
            case 500: $text = 'Internal Server Error'; break;
            default:
                exit('Unknown http status code "' . htmlentities($statusCode) . '"');
            break;
        }

        $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');

        header($protocol . ' ' . $statusCode . ' ' . $text);
    }
}