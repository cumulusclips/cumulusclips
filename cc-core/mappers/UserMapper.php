<?php

class UserMapper
{
    public function getUserById($userId)
    {
        $db = Registry::get('db');
        $query = 'SELECT * FROM ' . DB_PREFIX . 'users WHERE user_id = :userId';
        $dbResults = $db->fetchRow($query, array(':userId' => $userId));
        if ($db->rowCount() == 1) {
            return $this->_map($dbResults);
        } else {
            return false;
        }
    }
    
    public function getUserByUsername($username)
    {
        $db = Registry::get('db');
        $query = 'SELECT * FROM ' . DB_PREFIX . 'users WHERE username = :username';
        $dbResults = $db->fetchRow($query, array(':username' => $username));
        if ($db->rowCount() == 1) {
            return $this->_map($dbResults);
        } else {
            return false;
        }
    }
    
    public function getUserByCustom(array $params)
    {
        $db = Registry::get('db');
        $query = 'SELECT * FROM ' . DB_PREFIX . 'users WHERE ';
        
        $queryParams = array();
        foreach ($params as $fieldName => $value) {
            $query .= "$fieldName = :$fieldName AND ";
            $queryParams[":$fieldName"] = $value;
        }
        $query = rtrim($query, ' AND ');
        
        $dbResults = $db->fetchRow($query, $queryParams);
        if ($db->rowCount() == 1) {
            return $this->_map($dbResults);
        } else {
            return false;
        }
    }

    protected function _map($dbResults)
    {
        $user = new User();
        $user->userId = $dbResults['user_id'];
        $user->username = $dbResults['username'];
        $user->email = $dbResults['email'];
        $user->password = $dbResults['password'];
        $user->status = ($dbResults['status'] == 1) ? true : false;
        $user->role = $dbResults['role'];
        $user->dateCreated = date(DATE_FORMAT, strtotime($dbResults['date_created']));
        $user->firstName = $dbResults['first_name'];
        $user->lastName = $dbResults['last_name'];
        $user->aboutMe = $dbResults['about_me'];
        $user->website = $dbResults['website'];
        $user->confirmCode = $dbResults['confirm_code'];
        $user->views = $dbResults['views'];
        $user->lastLogin = date(DATE_FORMAT, strtotime($dbResults['last_login']));
        $user->avatar = $dbResults['avatar'];
        $user->released = ($dbResults['released'] == 1) ? true : false;
        return $user;
    }

    public function save(User $user)
    {
        $user = Plugin::triggerFilter('video.beforeSave', $user);
        $db = Registry::get('db');
        if (!empty($user->userId)) {
            // Update
            Plugin::triggerEvent('video.update', $user);
            $query = 'UPDATE ' . DB_PREFIX . 'users SET';
            $query .= ' username = :username, email = :email, password = :password, status = :status, role = :role, date_created = :dateCreated, first_name = :firstName, last_name = :lastName, about_me = :aboutMe, website = :website, confirm_code = :confirmCode, views = :views, last_login = :lastLogin, avatar = :avatar, :released';
            $query .= ' WHERE userId = :userId';
            $bindParams = array(
                ':userId' => $user->userId,
                ':username' => $user->username,
                ':email' => $user->email,
                ':password' => $user->password,
                ':status' => (!empty($user->status)) ? $user->status : 'new',
                ':role' => (!empty($user->role)) ? $user->role : 'user',
                ':dateCreated' => date(DATE_FORMAT, strtotime($user->dateCreated)),
                ':firstName' => (!empty($user->firstName)) ? $user->firstName : null,
                ':lastName' => (!empty($user->lastName)) ? $user->lastName : null,
                ':aboutMe' => (!empty($user->aboutMe)) ? $user->aboutMe : null,
                ':website' => (!empty($user->website)) ? $user->website : null,
                ':confirmCode' => (!empty($user->confirmCode)) ? $user->confirmCode : null,
                ':views' => (isset($user->views)) ? $user->views : 0,
                ':lastLogin' => (!empty($user->lastLogin)) ? date(DATE_FORMAT, strtotime($user->lastLogin)) : null,
                ':avatar' => (!empty($user->avatar)) ? $user->avatar : null,
                ':released' => (isset($user->released) && $user->released === true) ? 1 : 0,
            );
        } else {
            // Create
            Plugin::triggerEvent('video.create', $user);
            $query = 'INSERT INTO ' . DB_PREFIX . 'users';
            $query .= ' (username, email, password, status, role, date_created, first_name, last_name, about_me, website, confirm_code, views, last_login, avatar, :released)';
            $query .= ' VALUES (:username, :email, :password, :status, :role, :dateCreated, :firstName, :lastName, :aboutMe, :website, :confirmCode, :views, :lastLogin, :avatar, :released)';
            $bindParams = array(
                ':username' => $user->username,
                ':email' => $user->email,
                ':password' => $user->password,
                ':status' => (!empty($user->status)) ? $user->status : 'new',
                ':role' => (!empty($user->role)) ? $user->role : 'user',
                ':dateCreated' => gmdate(DATE_FORMAT),
                ':firstName' => (!empty($user->duration)) ? $user->firstName : null,
                ':lastName' => (!empty($user->lastName)) ? $user->lastName : null,
                ':aboutMe' => (!empty($user->aboutMe)) ? $user->aboutMe : null,
                ':website' => (!empty($user->website)) ? $user->website : null,
                ':confirmCode' => (!empty($user->confirmCode)) ? $user->confirmCode : null,
                ':views' => (isset($user->views)) ? $user->views : 0,
                ':lastLogin' => (!empty($user->lastLogin)) ? date(DATE_FORMAT, strtotime($user->lastLogin)) : null,
                ':avatar' => (!empty($user->avatar)) ? $user->avatar : null,
                ':released' => (isset($user->released) && $user->released === true) ? 1 : 0,
            );
        }
            
        $db->query($query, $bindParams);
        $userId = (!empty($user->userId)) ? $user->userId : $db->lastInsertId();
        Plugin::triggerEvent('video.save', $userId);
        return $userId;
    }
    
    public function getMultipleUsersById(array $userIds)
    {
        $userList = array();
        if (empty($userIds)) return $userList;
        
        $db = Registry::get('db');
        $inQuery = implode(',', array_fill(0, count($userIds), '?'));
        $sql = 'SELECT * FROM ' . DB_PREFIX . 'users WHERE user_id IN (' . $inQuery . ')';
        $result = $db->fetchAll($sql, $userIds);

        foreach($result as $userRecord) {
            $userList[] = $this->_map($userRecord);
        }
        return $userList;
    }
    
    /**
     * Get video count Method
     * @return integer Returns the number of approved videos uploaded by the user
     */
    public function getVideoCount($userId)
    {
        $db = Registry::get('db');
        $query = "SELECT COUNT(video_id) FROM " . DB_PREFIX . "videos WHERE user_id = $userId AND status = 'approved'";
        $db->fetchRow($query);
        return $db->rowCount();
    }
}