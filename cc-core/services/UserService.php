<?php

class UserService extends ServiceAbstract
{
    /**
     * Delete a user
     * @param User $user Instance of user to be deleted
     * @return void User is deleted from system
     */
    public function delete(User $user)
    {
        // Delete Avatar
        if (!empty($user->avatar)) Avatar::delete($user->avatar);

        // Delete Comments
        $commentService = new CommentService();
        $commentMapper = new CommentMapper();
        $comments = $commentMapper->getMultipleCommentsByCustom(array('user_id' => $user->userId));
        foreach ($comments as $comment) $commentService->delete($comment);

        // Delete Ratings
        $ratingService = new RatingService();
        $ratingMapper = new RatingMapper();
        $ratings = $ratingMapper->getMultipleRatingsByCustom(array('user_id' => $user->userId));
        foreach ($ratings as $rating) $ratingService->delete($rating);

        // Delete Flags
        $flagService = new FlagService();
        $flagMapper = new FlagMapper();
        $flags = $flagMapper->getMultipleFlagsByCustom(array('user_id' => $user->userId));
        foreach ($flags as $flag) $flagService->delete($flag);

        // Delete Subscriptions
        $subscriptionService = new SubscriptionService();
        $subscriptionMapper = new SubscriptionMapper();
        $subscriptions = $subscriptionMapper->getMultipleSubscriptionsByCustom(array('user_id' => $user->userId));
        foreach ($subscriptions as $subscription) $subscriptionService->delete($subscription);

        // Delete Messages
        $messageService = new MessageService();
        $messageMapper = new MessageMapper();
        $messages = $messageMapper->getMultipleMessagesByCustom(array('user_id' => $user->userId));
        foreach ($messages as $message) $messageService->delete($message);

        // Delete Playlists
        $playlistService = new PlaylistService();
        $playlistMapper = new PlaylistMapper();
        $playlists = $playlistMapper->getMultiplePlaylistsByCustom(array('user_id' => $user->userId));
        foreach ($playlists as $playlist) $playlistService->delete($playlist);

        // Delete Videos
        $videoService = new VideoService();
        $videoMapper = new VideoMapper();
        $videos = $videoMapper->getMultipleVideosByCustom(array('user_id' => $user->userId));
        foreach ($videos as $video) $videoService->delete($video);

        // Delete Privacy
        $privacyService = new PrivacyService();
        $privacyMapper = new PrivacyMapper();
        $privacy = $privacyMapper->getPrivacyByUser($user->userId);
        $privacyService->delete($privacy);

        // Delete User
        $userMapper = $this->_getMapper();
        $userMapper->delete($user->userId);
    }

    /**
     * Get video count Method
     * @param User $user User to retrieve video count for
     * @return integer Returns the number of approved videos uploaded by the user
     */
    public function getVideoCount(User $user)
    {
        $videoMapper = new VideoMapper();
        return $videoMapper->getVideoCount($user->userId);
    }

    /**
     * Get count of a member's public non-special playlists
     * @param User $user User to retrieve playlist count for
     * @return integer Returns the number of playlists by the user
     */
    public function getPlaylistCount(User $user)
    {
        $db = Registry::get('db');
        $query = "SELECT COUNT(playlist_id) as count FROM " . DB_PREFIX . "playlists WHERE user_id = $user->userId AND public = 1 AND type NOT IN ('watch_later', 'favorites')";
        $row = $db->fetchRow($query);
        return (int) $row['count'];
    }

    /**
     * Generate and save a new password for user
     * @param User $user Instance of user to have password reset
     * @return string Returns user's new password
     */
    public function resetPassword(User $user)
    {
        $userMapper = $this->_getMapper();
        $password = Functions::random(10,true);
        $user->password = md5($password);
        $userMapper->save($user);
        return $password;
    }

    /**
     * Generate a unique random string for a user account activation token
     * @return string Random user account activation token
     */
    public function createToken()
    {
         $userMapper = $this->_getMapper();
        do {
            $token = Functions::random(40);
            $tokenAvailable = ($userMapper->getUserByCustom(array('confirm_code' => $token))) ? false : true;
        } while (empty($tokenAvailable));
        return $token;
    }

    /**
     * Login a user
     *
     * @deprecated Depracated in 2.5, removed in 2.6. Use AuthService::validateCredentials/login instead
     * @param string $username Username of user to login
     * @param string $password Password of user to login
     * @return boolean User is logged in, returns true if login succeeded, false otherwise
     */
    public function login($username, $password)
    {
        $authService = new \AuthService();
        $user = $authService->validateCredentials($username, $password);

        if (!$user) return false;

        $authService->login($user);
        return true;
    }

    /**
     *  Log a user out of website
     *
     * @deprecated Depracated in 2.5, removed in 2.6. Use AuthService::logout instead
     * @return void
     */
    public function logout()
    {
        $authService = new \AuthService();
        return $authService->logout();
    }

    /**
     * Check if user is logged in, with optional redirect
     *
     * @deprecated Depracated in 2.5, removed in 2.6. Use AuthService::getAuthUser instead
     * @return boolean|User Returns instance user if logged in, boolean false otherwise
     */
    public function loginCheck()
    {
        $authService = new \AuthService();
        return $authService->getAuthUser();
    }

    /**
     * Check if a user has a given permission
     * @param string $permission Name of permission to check
     * @param mixed $userToCheck (optional) User object or ID of user to check permissions for. If null, logged in user is used
     * @return boolean Returns true if user's role has permission, false otherwise
     */
    public function checkPermissions($permission, $userToCheck = null)
    {
        $config = Registry::get('config');
        $authService = new \AuthService();
        $user = false;

        // Retrieve user information
        if (isset($userToCheck)) {

            if ($userToCheck instanceof User) {
                $user = $userToCheck;
            } else if (is_numeric($userToCheck)) {
                $userMapper = $this->_getMapper();
                $user = $userMapper->getUserById($userToCheck);
            } else {
                throw new \InvalidArgumentException('Invalid value passed as user to check');
            }

        } else {
            $user = $authService->getAuthUser();
        }

        if (!$user) return false;

        // Check for given permission in user's role
        if (isset($config->roles->{$user->role})) {
            $permissionsList = $config->roles->{$user->role}->permissions;
            return in_array ($permission, $permissionsList);
        } else {
            return false;
        }
    }

    /**
     * Change the status of a user's content
     * @param User $user Instance of user who's content is getting updated
     * @param string $status The new status being assigned to the user's content
     * @return void User's related records are updated to the new status
     */
    public function updateContentStatus(User $user, $status)
    {
        $db = Registry::get('db');
        switch ($status) {
            case 'banned':
            case 'pending':

                // Set user's videos to 'User Not Available'
                $query = "UPDATE " . DB_PREFIX . "videos SET status = CONCAT('user not available - ',status) WHERE user_id = :userId AND status NOT LIKE 'user not available - %'";
                $db->Query ($query, array(':userId' => $user->userId));

                // Set user's comments to 'User Not Available'
                $query = "UPDATE " . DB_PREFIX . "comments SET status = CONCAT('user not available - ',status) WHERE user_id = :userId AND status NOT LIKE 'user not available - %'";
                $db->Query ($query, array(':userId' => $user->userId));
                break;

            case 'active':

                // Restore user's videos IF/APP
                $query = "UPDATE " . DB_PREFIX . "videos SET status = REPLACE(status,'user not available - ','') WHERE user_id = :userId";
                $db->Query ($query, array(':userId' => $user->userId));

                // Restore user's comments IF/APP
                $query = "UPDATE " . DB_PREFIX . "comments SET status = REPLACE(status,'user not available - ','') WHERE user_id = :userId";
                $db->Query ($query, array(':userId' => $user->userId));
                break;
        }
    }

    /**
     * Creates a new user in the system
     * @param User $user User to be created
     * @return returns newly created user
     */
    public function create(User $user)
    {
        // Save new user
        $userMapper = $this->_getMapper();
        $user->password = md5($user->password);
        $user->confirmCode = $this->createToken();
        $user->status = 'new';
        $userId = $userMapper->save($user);

        // Create user's privacy record
        $privacy = new Privacy();
        $privacy->userId = $userId;
        $privacy->videoComment = true;
        $privacy->newMessage = true;
        $privacy->newVideo = true;
        $privacy->videoReady = true;
        $privacy->commentReply = true;
        $privacyMapper = new PrivacyMapper();
        $privacyMapper->save($privacy);

        // Create user's favorites playlist
        $playlistMapper = new PlaylistMapper();
        $favorites = new Playlist();
        $favorites->userId = $userId;
        $favorites->public = false;
        $favorites->type = 'favorites';
        $playlistMapper->save($favorites);

        // Create user's watch later playlist
        $watchLater = new Playlist();
        $watchLater->userId = $userId;
        $watchLater->public = false;
        $watchLater->type = 'watch_later';
        $playlistMapper->save($watchLater);

        return $userMapper->getUserById($userId);
    }

    /**
     * Make a user visible to the public and notify admin of registration
     * @param User $user User to get updated
     * @param string $action Step in the approval proccess to perform. Allowed values: create|activate|approve
     * @return void User is activated, and admin alerted. If approval is
     * required user is marked pending and placed in queue
     */
    public function approve(User $user, $action)
    {
        $send_alert = false;
        $userMapper = $this->_getMapper();

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
            } else {

                // Send Admin Alert
                if (in_array ($action, array ('create','activate')) && Settings::Get ('alerts_users') == '1') {
                    $send_alert = true;
                    $subject = 'New Member Registered';
                    $body = 'A new member has registered.';
                }

                // Activate & Release
                $user->status = 'active';
                $user->released = true;
                $userMapper->save($user);

                // Send Welcome email
                if ($action == 'approve') {
                    $config = Registry::get('config');
                    $mailer = new Mailer($config);
                    $mailer->setTemplate ('account_approved', array('sitename' => $config->sitename));
                    $mailer->Send ($user->email);
                }
            }

        // User is being re-approved
        } else if ($action == 'approve' && $user->released) {
            // Activate User
            $user->status = 'active';
            $userMapper->save($user);
        }

        // Send admin alert
        if ($send_alert) {
            $body .= "\n\n=======================================================\n";
            $body .= "Username: $user->username\n";
            $body .= "Profile URL: " . HOST . "/members/$user->username/\n";
            $body .= "=======================================================";
            App::Alert ($subject, $body);
        }
    }

    /**
     * Retrieve URL to current user's avatar
     * @param User Instance of user who's avatar is wanted
     * @return string URL to user's uploaded avatar or default theme avatar if none is set
     */
    public function getAvatarUrl(User $user)
    {
        return (!empty($user->avatar)) ? HOST . "/cc-content/uploads/avatars/$user->avatar" : null;
    }

    /**
     * Retrieve a list of users who are subscribed to given member
     * @param User $member Instance of user whose subscribers will be retrieved
     * @return array Returns list of User objects
     */
    public function getSubscribedUsers(User $member)
    {
        $userMapper = $this->_getMapper();
        $subscriptionMapper = new SubscriptionMapper();
        $subscriptionList = $subscriptionMapper->getMultipleSubscriptionsByCustom(array('member' => $member->userId));
        $userIdList = array();
        foreach ($subscriptionList as $subscription) {
            $userIdList[] = $subscription->userId;
        }
        return $userMapper->getUsersFromList($userIdList);
    }

    /**
     * Retrieve instance of User mapper
     * @return UserMapper Mapper is returned
     */
    protected function _getMapper()
    {
        return new UserMapper();
    }
}