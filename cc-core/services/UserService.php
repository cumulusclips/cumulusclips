<?php

class UserService extends ServiceAbstract
{
    public $found;
    private $db;
    protected static $table = 'users';
    protected static $id_name = 'user_id';

    /**
     * Delete a record
     * @param integer $id ID of record to be deleted
     * @return void Record is deleted from database
     */
    public static function delete($id)
    {
        $db = Database::GetInstance();
        $user = new self ($id);
        Plugin::triggerEvent('user.delete');

        // Delete Avatar
        if (!empty ($user->avatar)) Avatar::Delete ($user->avatar);

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
    private function getVideoCount()
    {
        $query = "SELECT COUNT(video_id) FROM " . DB_PREFIX . "videos WHERE user_id = $this->user_id AND status = 'approved'";
        $result = $this->db->Query ($query);
        $row = $this->db->FetchRow ($result);
        return $row[0];
    }
    
    /**
     * Generate and save a new password for user
     * @return string Returns user's new password
     */
    public function resetPassword()
    {
        $password = Functions::Random (10,true);
        $data = array ('password' => md5 ($password));
        Plugin::triggerEvent('user.reset_password');
        $this->Update ($data);
        return $password;
    }
	
    /**
     * Generate a unique random string for a user account activation token
     * @return string Random user account activation token
     */
    public static function createToken()
    {
        $db = Database::GetInstance();
        do {
            $token = Functions::Random(40);
            if (!self::Exist (array ('confirm_code' => $token))) $token_available = true;
        } while (empty ($token_available));
        return $token;
    }
    
    /**
     * Login a user
     * @param string $username Username of user to login
     * @param string $password Password of user to login
     * @return boolean User is logged in, returns true if login succeeded, false otherwise
     */
    public static function login($username, $password)
    {
        $id = self::Exist (array ('username' => $username, 'password' => md5 ($password), 'status' => 'active'));
        if ($id) {
            $_SESSION['user_id'] = $id;
            $user = new self ($id);
            $user->Update (array ('last_login' => date ('Y-m-d H:i:s')));
            Plugin::triggerEvent('user.login');
            return true;
        } else {
            return false;
        }
    }

    /**
     *  Log a user out of website
     * @return void
     */
    public static function logout()
    {
        unset ($_SESSION['user_id']);
        Plugin::triggerEvent('user.logout');
    }

    /**
     * Check if user is logged in, with optional redirect
     * @param string $redirect_location optional Location to redirect user if login check fails
     * @return boolean|integer Returns logged in users' ID if user is logged, boolean false otherwise
     */
    public static function loginCheck()
    {
        if (!empty ($_SESSION['user_id']) && self::Exist (array ('user_id' => $_SESSION['user_id']))) {
            return $_SESSION['user_id'];
        } else {
            return false;
        }
    }

    /**
     * Check if a user has a given permission
     * @global object $config Site configuration settings
     * @param string $permission Name of permission to check
     * @param mixed $user_to_check (optional) User object or ID of user to check permissions for. If null, logged in user is used
     * @return boolean Returns true if user's role has permission, false otherwise
     */
    public static function checkPermissions($permission, $user_to_check = null)
    {
        global $config;
        
        // Retrieve user information
        if (!empty ($user_to_check) && is_object ($user_to_check) && get_class ($user_to_check) == __CLASS__) {
            $user = $user_to_check;
        } else if (!empty ($user_to_check)) {
            $user = new self ($user_to_check);
        } else if ($logged_in = self::LoginCheck()) {
            $user = new self ($logged_in);
        } else {
            return false;
        }

        // Check for given permission in user's role
        if (array_key_exists ($user->role, $config->roles)) {
            $permissions_list = $config->roles[$user->role]['permissions'];
            return in_array ($permission, $permissions_list);
        } else {
            return false;
        }
    }

    /**
     * Change the status of a user's content
     * @param string $status The new status being assigned to the user's content
     * @return void User's related records are updated to the new status
     */
    public function updateContentStatus($status)
    {
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
     * Make a user visible to the public and notify admin of registration
     * @param User $user User to get updated
     * @param string $action Step in the approval proccess to perform. Allowed values: create|activate|approve
     * @return void User is activated, and admin alerted. If approval is
     * required user is marked pending and placed in queue
     */
    public function approve($user, $action)
    {
        $send_alert = false;
        $userMapper = new UserMapper();
        Plugin::triggerEvent('user.before_approve');

        // 1) Admin created user in Admin Panel
        // 2) User signed up & activated
        // 3) User is being approved by admin for first time
        if ((in_array ($action, array ('create','activate'))) || $action == 'approve' && $user->released == 0) {

            // User is activating account, but approval is required
            if ($action == 'activate' && Settings::Get ('auto_approve_users') == '0') {

                // Send Admin Approval Alert
                $send_alert = true;
                $subject = 'New Member Awaiting Approval';
                $body = 'A new member has registered and is awaiting admin approval.';

                // Set Pending
                $user->status = 'pending';
                $userMapper->save($user);
                Plugin::triggerEvent('user.approve_required');
            } else {

                // Send Admin Alert
                if (in_array ($action, array ('create','activate')) && Settings::Get ('alerts_users') == '1') {
                    $send_alert = true;
                    $subject = 'New Member Registered';
                    $body = 'A new member has registered.';
                }

                // Activate & Release
                $user->status = 'active';
                $user->released = 1;
                $userMapper->save($user);

                // Update user's anonymous comments IF/APP
                $query = "UPDATE " . DB_PREFIX . "comments SET user_id = ? WHERE email = ?";
                Registry::get('db')->query($query, array($user->userId, $user->email));

                // Send Welcome email
                if ($action == 'approve') {
                    App::LoadClass ('Mail');
                    $mail = new Mail();
                    $mail->LoadTemplate ('account_approved', array('sitename' => Registry::get('config')->sitename));
                    $mail->Send ($this->email);
                }

                Plugin::triggerEvent('user.release');
            }

        // User is being re-approved
        } else if ($action == 'approve' && $user->released != 0) {
            // Activate User
            $user->status = 'active';
            $userMapper->save($user);
            Plugin::triggerEvent('user.reapprove');
        }

        // Send admin alert
        if ($send_alert) {
            $body .= "\n\n=======================================================\n";
            $body .= "Username: $user->username\n";
            $body .= "Profile URL: " . HOST . "/members/$user->username/\n";
            $body .= "=======================================================";
            App::Alert ($subject, $body);
        }

        Plugin::triggerEvent('user.approve');
    }
    
    /**
     * Retrieve URL to current user's avatar
     * @return string URL to user's uploaded avatar or default theme avatar if none is set
     */
    public function getAvatarUrl($user)
    {
        return (empty($user->avatar)) ? View::GetFallbackUrl('images/avatar.gif') : HOST . "/cc-content/uploads/avatars/$user->avatar";
    }
}