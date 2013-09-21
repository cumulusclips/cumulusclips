<?php

class SubscriptionMapper
{
    public function getSubscriptionById($subscriptionId)
    {
        $db = Registry::get('db');
        $query = 'SELECT * FROM ' . DB_PREFIX . 'subscriptions WHERE subscriptionId = :subscriptionId';
        $dbResults = $db->fetchRow($query, array(':subscriptionId' => $subscriptionId));
        if ($db->rowCount() == 1) {
            return $this->_map($dbResults);
        } else {
            return false;
        }
    }

    protected function _map($dbResults)
    {
        $subscription = new Subscription();
        $subscription->subscriptionId = $dbResults['subscriptionId'];
        $subscription->userId = $dbResults['userId'];
        $subscription->member = $dbResults['member'];
        $subscription->dateCreated = date(DATE_FORMAT, strtotime($dbResults['dateCreated']));
        return $subscription;
    }

    public function save(Subscription $subscription)
    {
        $subscription = Plugin::triggerFilter('video.beforeSave', $subscription);
        $db = Registry::get('db');
        if (!empty($subscription->subscriptionId)) {
            // Update
            Plugin::triggerEvent('video.update', $subscription);
            $query = 'UPDATE ' . DB_PREFIX . 'subscriptions SET';
            $query .= ' userId = :userId, member = :member, dateCreated = :dateCreated';
            $query .= ' WHERE subscriptionId = :subscriptionId';
            $bindParams = array(
                ':subscriptionId' => $subscription->subscriptionId,
                ':userId' => $subscription->userId,
                ':member' => $subscription->member,
                ':dateCreated' => date(DATE_FORMAT, strtotime($subscription->dateCreated))
            );
        } else {
            // Create
            Plugin::triggerEvent('video.create', $subscription);
            $query = 'INSERT INTO ' . DB_PREFIX . 'subscriptions';
            $query .= ' (userId, member, dateCreated)';
            $query .= ' VALUES (:userId, :member, :dateCreated)';
            $bindParams = array(
                ':userId' => $subscription->userId,
                ':member' => $subscription->member,
                ':dateCreated' => gmdate(DATE_FORMAT)
            );
        }
            
        $db->query($query, $bindParams);
        $subscriptionId = (!empty($subscription->subscriptionId)) ? $subscription->subscriptionId : $db->lastInsertId();
        Plugin::triggerEvent('video.save', $subscriptionId);
        return $subscriptionId;
    }
    
    
    public function isSubscribed($subscribingUser, $subscribedMember)
    {
        $db = Registry::get('db');
        $query = 'SELECT * FROM ' . DB_PREFIX . 'subscriptions WHERE user_id = :user AND member = :member';
        $db->fetchRow($query, array(':user' => $subscribingUser, ':member' =>$subscribedMember));
        return ($db->rowCount() == 1) ? true : false;
    }
}