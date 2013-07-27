<?php

class UserMapper
{
    public function getUserById($userId)
    {
        $db = Registry::get('db');
        $query = 'SELECT * FROM ' . DB_PREFIX . 'users WHERE userId = :userId';
        $dbResults = $db->fetchRow($query, array(':userId' => $userId));
        if ($db->rowCount() == 1) {
            return $this->_map($dbResults);
        } else {
            return false;
        }
    }
    
    public function getUserByUser($username)
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

    protected function _map($dbResults)
    {
        $user = new User();
        $user->userId = $dbResults['userId'];
        $user->username = $dbResults['username'];
        $user->email = $dbResults['email'];
        $user->password = $dbResults['password'];
        $user->status = ($dbResults['status'] == 1) ? true : false;
        $user->role = $dbResults['role'];
        $user->dateCreated = date(DATE_FORMAT, strtotime($dbResults['dateCreated']));
        $user->firstName = $dbResults['firstName'];
        $user->lastName = $dbResults['lastName'];
        $user->aboutMe = $dbResults['aboutMe'];
        $user->website = $dbResults['website'];
        $user->confirmCode = $dbResults['confirmCode'];
        $user->views = $dbResults['views'];
        $user->lastLogin = date(DATE_FORMAT, strtotime($dbResults['lastLogin']));
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
            $query .= ' username = :username, email = :email, password = :password, status = :status, role = :role, dateCreated = :dateCreated, firstName = :firstName, lastName = :lastName, aboutMe = :aboutMe, website = :website, confirmCode = :confirmCode, views = :views, lastLogin = :lastLogin, avatar = :avatar, :released';
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
            $query .= ' (username, email, password, status, role, dateCreated, firstName, lastName, aboutMe, website, confirmCode, views, lastLogin, avatar, :released)';
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
}