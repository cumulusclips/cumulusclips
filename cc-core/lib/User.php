<?php

class User {

    public $found;
    private $db;
    protected static $table = 'users';
    protected static $id_name = 'user_id';



    /**
     * Instantiate object
     * @param integer $id ID of record to be instantiated
     * @return object Returns object of class type
     */
    public function  __construct ($id) {
        $this->db = Database::GetInstance();
        if (self::Exist (array (self::$id_name => $id))) {
            $this->Get ($id);
            $this->found = true;
        } else {
            $this->found = false;
        }
    }



    /**
     * Extract values from database and set them to object properties
     * @param integer $id ID of record to be instantiated
     * @return void
     */
    private function Get ($id) {
        $query = 'SELECT * FROM ' . self::$table . ' WHERE ' . self::$id_name . "= $id";
        $result = $this->db->Query ($query);
        $row = $this->db->FetchAssoc ($result);
        foreach ($row as $key => $value) {
            $this->$key = $value;
        }

        // User specific values
        $this->avatar = "http://www.gravatar.com/avatar/" . md5 (strtolower ($this->email)) . "?default=" . urlencode (THEME . '/images/user_placeholder.gif') . "&size=100";
        $this->date_joined = date ('m/d/Y', strtotime ($this->date_joined));
        $this->last_login = date ('m/d/Y', strtotime ($this->last_login));
        $this->video_count = $this->GetVideoCount();

    }



    /**
     * Check if a record exists matching the given criteria
     * @param array $data Key/Value pairs to use in select criteria i.e. array (field_name => value)
     * @return integer|boolean Returns record ID if record is found or boolean false if not found
     */
    static function Exist ($data) {

        $db = Database::GetInstance();
        $query = 'SELECT ' . self::$id_name . ' FROM ' . self::$table . ' WHERE';

        foreach ($data as $key => $value) {
            $value = $db->Escape ($value);
            $query .= " $key = '$value' AND";
        }

        $query = substr ($query, 0, -4);
        $result = $db->Query ($query);

        if ($db->Count($result) > 0) {
            $row = $db->FetchAssoc ($result);
            return $row[self::$id_name];
        } else {
            return false;
        }

    }



    /**
     * Create a new record using the given criteria
     * @param array $data Key/Value pairs to use as data for new record i.e. array (field_name => value)
     * @return integer Returns the ID of the newly created record
     */
    static function Create ($data) {

        $db = Database::GetInstance();
        $query = 'INSERT INTO ' . self::$table;
        $fields = '';
        $values = '';

        foreach ($data as $_key => $_value) {
            $fields .= "$_key, ";
            $values .= "'" . $db->Escape ($_value) . "', ";
        }

        $fields = substr ($fields, 0, -2);
        $values = substr ($values, 0, -2);
        $query .= " ($fields) VALUES ($values)";
        $db->Query ($query);
        return $db->LastId();

    }



    /**
     * Update current record using the given data
     * @param array $data Key/Value pairs of data to be updated i.e. array (field_name => value)
     * @return void
     */
    public function Update ($data) {

        $query = 'UPDATE ' . self::$table . " SET";
        foreach ($data as $_key => $_value) {
            $query .= " $_key = '" . $this->db->Escape ($_value) . "',";
        }

        $query = substr ($query, 0, -1);
        $id_name = self::$id_name;
        $query .= " WHERE $id_name = " . $this->$id_name;
        $this->db->Query ($query);
        $this->Get ($this->$id_name);

    }



    /**
     * Get video count Method
     * @return integer Returns the number of approved videos uploaded by the user
     */
    private function GetVideoCount() {
        $query = "SELECT COUNT(video_id) FROM videos WHERE user_id = $this->user_id AND status = 6";
        $result = $this->db->Query ($query);
        $row = $this->db->FetchRow ($result);
        return $row[0];
    }
    
    
    
    /**
     * Generate a new password for user
     * @return void User's password is reset and updated in DB
     */
    public function ResetPassword() {
        $password = Functions::Random (10,true);
        $data = array ('password' => md5 ($password));
        $this->Update ($data);
        $this->password = $password;
    }
	
	
	
    /**
     * Generate a unique random string for a user account activation token
     * @return string Random user account activation token
     */
    static function CreateToken() {
        $db = Database::GetInstance();
        do {
            $token = Functions::Random(40);
            if (!self::Exist (array ('confirm_code' => $token))) $token_available = true;
        } while (empty ($token_available));
        return $token;
    }
	
	
	
    /**
     * Generate a unique random string for a user's picture filename
     * @param string $extension the extension for the filename
     * @return string Random filename
     */
    static function CreatePictureToken ($extension) {
        do {
            $token = Functions::Random(10);
            if (file_exists (UPLOAD_PATH . '/thumbs/' . $token . ".$extension")) $token_available = true;
        } while (empty ($token_available));
        return $token;
    }



    /**
     * Create an anonymous user
     * @param string $ip_address IP Address of anonymous user
     * @return int user id of new anonymous user
     */
    static function CreateAnonymous() {
        $db = Database::GetInstance();
        $ip = $_SERVER['REMOTE_ADDR'];
        $query = "INSERT INTO users_anonymous (ip, date_created) values ('$ip', NOW())";
        $db->Query ($query);
        $id = $db->LastId()*-1;
        setcookie('cc_anonymous', $id, time()+3600*24*365*10,'/');
        return $id;
    }



    /**
     * Determine whether user is registered as anonymous user
     * @return boolean Returns true if user is registered anonymous, false otherwise
     */
    static function IsAnonymous() {
        if (!empty ($_COOKIE['cc_anonymous']) && is_numeric ($_COOKIE['cc_anonymous']) && $_COOKIE['cc_anonymous'] < 0) {
            return true;
        } else {
            return false;
        }
    }



    /**
     * Activate registered user's account
     * @return void User is activated, if user was anonymous his anonymous
     * actions are transfered to the main account, and the anonymous cookie is
     * removed.
     */
    public function ActivateUser() {

        // Update user status
        $this->Update (array ('account_status' => 'Active'));
        $msg = 'ID: ' . $this->user_id . "\nUsername: " . $this->username;
        @mail (MAIN_EMAIL, 'New Member Registered', $msg, 'From: Admin - TechieVideos.com <admin@techievideos.com>');

        // Convert if anonymous user
        if (self::IsAnonymous()) {

            // Update user's anonymous ratings
            $anon_id = $_COOKIE['tv_anonymous'];
            $query = "UPDATE ratings SET user_id = $this->user_id WHERE user_id = $anon_id";
            $this->db->Query ($query);
            setcookie('cc_anonymous',null,time()-3600*24*365*10,'/');

        }
    }
    
    
    
    /**
     * Login a user
     * @param string $username Username of user to login
     * @param string $password Password of user to login
     * @return boolean Returns true and logs user in if valid login, returns false otherwise
     */
    static function Login ($username, $password) {
        $id = self::Exist (array ('username' => $username, 'password' => $password, 'account_status' => 'Active'));
        if ($id) {
            $_SESSION['user_id'] = $id;
            return true;
        } else {
            return false;
        }
    }



    /**
     *  Log a user out of website
     * @return void
     */
    static function Logout() {
        unset ($_SESSION['user_id']);
    }



    /**
     * Check if user is logged in, with optional redirect
     * @param string $redirect_location optional Location to redirect user if login check fails
     * @return boolean|mixed Returns logged in users' ID if user is logged,
     * if user login check fails and redirect is provided user is redirected,
     * returns boolean false otherwise
     */
    static function LoginCheck ($redirect_location = null) {
        if (!empty ($_SESSION['user_id']) && self::Exist (array ('user_id' => $_SESSION['user_id']))) {
            return $_SESSION['user_id'];
        }  else {
            if ($redirect_location) {
                header ("Location: $redirect_location");
                exit();
            } else {
                return false;
            }
        }
    }

}

?>