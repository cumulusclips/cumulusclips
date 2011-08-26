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
     * @return void DB record's fields are loaded into object properties
     */
    private function Get ($id) {
        $query = 'SELECT * FROM ' . DB_PREFIX . self::$table . ' WHERE ' . self::$id_name . "= $id";
        $result = $this->db->Query ($query);
        $row = $this->db->FetchAssoc ($result);
        foreach ($row as $key => $value) {
            $this->$key = $value;
        }

        // User specific values
        $this->avatar_url = (empty ($this->avatar)) ? THEME . '/images/avatar.gif' : HOST . "/cc-content/uploads/avatars/$this->avatar";
        $this->date_created_formatted = date ('m/d/Y', strtotime ($this->date_created));
        $this->last_login = date ('m/d/Y', strtotime ($this->last_login));
        $this->video_count = $this->GetVideoCount();
        Plugin::Trigger ('user.get');

    }




    /**
     * Check if a record exists matching the given criteria
     * @param array $data Key/Value pairs to use in select criteria i.e. array (field_name => value)
     * @return integer|boolean Returns record ID if record is found or boolean false if not found
     */
    static function Exist ($data) {

        $db = Database::GetInstance();
        $query = 'SELECT ' . self::$id_name . ' FROM ' . DB_PREFIX . self::$table . ' WHERE';

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

        App::LoadClass ('Privacy');
        $db = Database::GetInstance();
        $query = 'INSERT INTO ' . DB_PREFIX . self::$table;
        $fields = '';
        $values = '';

        Plugin::Trigger ('user.before_create');
        foreach ($data as $_key => $_value) {
            $fields .= "$_key, ";
            $values .= "'" . $db->Escape ($_value) . "', ";
        }

        $fields = substr ($fields, 0, -2);
        $values = substr ($values, 0, -2);
        $query .= " ($fields) VALUES ($values)";
        $db->Query ($query);
        
        Privacy::Create (array ('user_id' => $db->LastId()));
        Plugin::Trigger ('user.create');
        return $db->LastId();

    }




    /**
     * Update current record using the given data
     * @param array $data Key/Value pairs of data to be updated i.e. array (field_name => value)
     * @return void Record is updated in DB
     */
    public function Update ($data) {

        Plugin::Trigger ('user.before_update');
        $query = 'UPDATE ' . DB_PREFIX . self::$table . " SET";
        foreach ($data as $_key => $_value) {
            $query .= " $_key = '" . $this->db->Escape ($_value) . "',";
        }

        $query = substr ($query, 0, -1);
        $id_name = self::$id_name;
        $query .= " WHERE $id_name = " . $this->$id_name;
        $this->db->Query ($query);
        $this->Get ($this->$id_name);
        Plugin::Trigger ('user.update');

    }




    /**
     * Delete a record
     * @param integer $id ID of record to be deleted
     * @return void Record is deleted from database
     */
    static function Delete ($id) {

        App::LoadClass ('Privacy');
        App::LoadClass ('Picture');
        App::LoadClass ('Video');
        App::LoadClass ('Subscription');
        App::LoadClass ('Rating');
        App::LoadClass ('Flag');
        App::LoadClass ('Favorite');
        App::LoadClass ('Comment');
        App::LoadClass ('Post');
        App::LoadClass ('Message');

        $db = Database::GetInstance();
        $user = new self ($id);
        Plugin::Trigger ('user.delete');

        // Delete Picture
        if (!empty ($user->picture)) Picture::Delete ($user->picture);

        // Delete Privacy Record
        $privacy_id = Privacy::Exist (array ('user_id' => $id));
        Privacy::Delete ($privacy_id);



        // Delete Comments
        $query = "SELECT comment_id FROM " . DB_PREFIX . "comments WHERE user_id = $id";
        $result = $db->Query ($query);
        while ($row = $db->FetchObj ($result)) Comment::Delete ($row->comment_id);

        // Delete Ratings
        $query = "SELECT rating_id FROM " . DB_PREFIX . "ratings WHERE user_id = $id";
        $result = $db->Query ($query);
        while ($row = $db->FetchObj ($result)) Rating::Delete ($row->rating_id);

        // Delete Favorites
        $query = "SELECT fav_id FROM " . DB_PREFIX . "favorites WHERE user_id = $id";
        $result = $db->Query ($query);
        while ($row = $db->FetchObj ($result)) Favorite::Delete ($row->fav_id);

        // Delete Flags
        $query = "SELECT flag_id FROM " . DB_PREFIX . "flags WHERE id = $id AND type = 'user'";
        $result = $db->Query ($query);
        while ($row = $db->FetchObj ($result)) Flag::Delete ($row->flag_id);

        // Delete Subscriptions
        $query = "SELECT sub_id FROM " . DB_PREFIX . "subscriptions WHERE user_id = $id OR member = $id";
        $result = $db->Query ($query);
        while ($row = $db->FetchObj ($result)) Subscription::Delete ($row->sub_id);

        // Delete Posts
        $query = "SELECT post_id FROM " . DB_PREFIX . "posts WHERE user_id = $id";
        $result = $db->Query ($query);
        while ($row = $db->FetchObj ($result)) Post::Delete ($row->post_id);

        // Delete Messages
        $query = "SELECT message_id FROM " . DB_PREFIX . "messages WHERE user_id = $id OR recipient = $id";
        $result = $db->Query ($query);
        while ($row = $db->FetchObj ($result)) Message::Delete ($row->message_id);

        // Delete Videos
        $query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE user_id = $id";
        $result = $db->Query ($query);
        while ($row = $db->FetchObj ($result)) Video::Delete ($row->video_id);

        // Delete Privacy
        $query = "SELECT privacy_id FROM " . DB_PREFIX . "privacy WHERE user_id = $id";
        $result = $db->Query ($query);
        while ($row = $db->FetchObj ($result)) Privacy::Delete ($row->privacy_id);

        // Delete User
        $query = "DELETE FROM " . DB_PREFIX . self::$table . " WHERE " . self::$id_name . " = $id";
        $db->Query ($query);

    }




    /**
     * Get video count Method
     * @return integer Returns the number of approved videos uploaded by the user
     */
    private function GetVideoCount() {
        $query = "SELECT COUNT(video_id) FROM " . DB_PREFIX . "videos WHERE user_id = $this->user_id AND status = 'approved'";
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
        Plugin::Trigger ('user.reset_password');
        $this->Update ($data);
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
     * Activate registered user's account
     * @return void User is activated, if user had anonymous comments they're
     * transfered to the main account
     */
    public function Activate() {

        // Update user status
        $this->Update (array ('status' => 'Active'));
        $msg = 'ID: ' . $this->user_id . "\nUsername: " . $this->username;
        @mail (MAIN_EMAIL, 'New Member Registered', $msg, 'From: Admin - TechieVideos.com <admin@techievideos.com>');
        Plugin::Trigger ('user.activate');
       

    }
    
    


    /**
     * Login a user
     * @param string $username Username of user to login
     * @param string $password Password of user to login
     * @return boolean User is logged in, returns true if login succeeded, false otherwise
     */
    static function Login ($username, $password) {
        $id = self::Exist (array ('username' => $username, 'password' => $password, 'status' => 'active'));
        if ($id) {
            $_SESSION['user_id'] = $id;
            Plugin::Trigger ('user.login');
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
        Plugin::Trigger ('user.logout');
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
                if (PREVIEW_LANG) $redirect_location = Functions::AppendQueryString ($redirect_location, array ('preview_lang' => PREVIEW_LANG));
                if (PREVIEW_THEME) $redirect_location = Functions::AppendQueryString ($redirect_location, array ('preview_theme' => PREVIEW_THEME));
                header ("Location: $redirect_location");
                exit();
            } else {
                return false;
            }
        }
    }




    /**
     * Change the status of a user's content
     * @param string $status The new status being assigned to the user's content
     * @return void User's related records are updated to the new status
     */
    public function UpdateContentStatus ($status) {

        switch ($status) {
            case 'new':
            case 'banned':
            case 'pending':

                // Set user's videos to 'User Not Available'
                $query = "UPDATE " . DB_PREFIX . "videos SET status = CONCAT('user not available - ',status) WHERE user_id = $this->user_id AND status NOT LIKE 'user not available - %'";
                $this->db->Query ($query);

                // Set user's comments to 'User Not Available'
                $query = "UPDATE " . DB_PREFIX . "comments SET status = CONCAT('user not available - ',status) WHERE user_id = $this->user_id AND status NOT LIKE 'user not available - %'";
                $this->db->Query ($query);
                break;

            case 'active':

                // Restore user's videos IF/APP
                $query = "UPDATE " . DB_PREFIX . "videos SET status = REPLACE(status,'user not available - ','') WHERE user_id = $this->user_id";
                $this->db->Query ($query);

                // Restore user's comments IF/APP
                $query = "UPDATE " . DB_PREFIX . "comments SET status = REPLACE(status,'user not available - ','') WHERE user_id = $this->user_id";
                $this->db->Query ($query);
                break;

        }

    }




    /**
     * Make a user visible to the public
     * @param boolean $bypass_admin_approval [optional] Whether or not to bypass admin approval
     * @return void If allowed, user is approved otherwise user is marked as pending approval
     */
    public function Approve ($bypass_admin_approval = false) {

        // Determine if video is allowed to be approved
        if ($bypass_admin_approval || Settings::Get ('auto_approve_users') == 'true') {

            $data = array ('status' => 'active');
            if ($this->released == 0) {

                $data['released'] = 1;

                // Update user's anonymous comments IF/APP
                $query = "UPDATE " . DB_PREFIX . "comments SET user_id = $this->user_id WHERE email = '$this->email'";
                $this->db->Query ($query);

            }

        } else {
            $data = array ('status' => 'pending');
        }

        $this->Update ($data);

    }

}

?>