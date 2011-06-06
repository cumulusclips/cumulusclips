<?php

class Database {

    static public $db;
    public $dbc;
    public $last_query;

    // Constructor method
    public function  __construct() {
        $this->dbc = @mysql_connect (DB_HOST, DB_USER, DB_PASS) OR die ('Unable to connect to database');
        @mysql_select_db (DB_NAME, $this->dbc) OR die ('Unable to select database');
    }



    /**
     * Retrieve DB object
     * @return object Returns new instance of DB object if non exists, or current
     * instance if already instantiated
     */
    static function GetInstance() {
        if (empty (self::$db)) self::$db = new self;
        return self::$db;
    }



    /**
     * Execute MySQL query method
     * @param string $query MySQL query to be executed
     * @return resource_id of query results if successful
     */
    public function Query ($query) {

        // Log query if requested
        if (LOG_QUERIES) {
            App::Log (QUERY_LOG, 'Date: ' . date ('m/d/Y h:i:sA') . "\t\t Query: $query");
        }

        $result = mysql_query ($query);
        $this->last_query = $query;
        if ($result) {
            return $result;
        } else {
            $this->KillDB ($this->last_query);
        }
    }



    // Retrieve result set as object
    public function FetchObj ($result) {
        return mysql_fetch_object ($result);
    }



    // Retrieve result set as numbered array
    public function FetchRow ($result) {
        return mysql_fetch_row ($result);
    }



    // Retrieve result set as named array
    public function FetchAssoc ($result) {
        return mysql_fetch_assoc ($result);
    }



    // Count the number of rows in query result resource
    public function Count ($result) {
        return mysql_num_rows ($result);
    }



    // Return the number of rows affected by the last UPDATE/DELETE query
    public function AffectedRows() {
        return mysql_affected_rows();
    }




    // Retrieve the last value created in the auto_increment field
    public function LastId() {
        return mysql_insert_id();
    }



    // Escape string for safe use in queries
    public function Escape ($string) {
        if (ini_get ('magic_quotes_gpc')) {
            $string = stripslashes ($string);
        }
        return mysql_real_escape_string (trim ($string), $this->dbc);
    }



    // Return error thrown my MySQL
    public function Error() {
        return mysql_error();
    }



    /**
     * Terminate execution of web site in case of Database error.
     * @return void Website execution is terminated. Errors are logged and sent
     * via Email. User is redirected to error page if possible.
     */
    private function KillDB() {

        // Log Database Error
        $date = date('m/d/Y G:iA');
        $message_log = "### MySQL Error - $date\n\n";
        $message_log .= "Error:\n" . $this->Error() . "\n\n";
        $message_log .= "Query: " . $this->last_query . "\n\n";
        App::Log (DB_ERR_LOG, $message_log);


        // Send Notification
        $subject = 'Site Error Encountered ' . $date;
        $message_alert = "An error was encountered on the website\n\n";
        $message_alert .= "Date: " . $date . "\n\n";
        $message_alert .= "Error:\n" . $this->Error() . "\n\n";
        $message_alert .= "Query:\n" . $this->last_query;
        App::Alert ($subject, $message_alert);

        if (!headers_sent()) {
            header ("Location: " . HOST . "/system-error/");
        } else {
            echo '<script>top.location = "' . HOST . '/system-error/";</script>';
            echo '<noscript>We are unable to continue due to a system error. We apologize for any inconvenience.</noscript>';
        }
        exit();

    }

}

?>