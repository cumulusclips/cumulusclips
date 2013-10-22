<?php

class SubscriptionMapper extends MapperAbstract
{
    public function getSubscriptionById($subscriptionId)
    {
        return $this->getSubscriptionByCustom(array('subscription_id' => $subscriptionId));
    }
    
    public function getSubscriptionByCustom(array $params)
    {
        $db = Registry::get('db');
        $query = 'SELECT * FROM ' . DB_PREFIX . 'subscriptions WHERE ';
        
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
        $subscription = new Subscription();
        $subscription->subscriptionId = $dbResults['subscription_id'];
        $subscription->userId = $dbResults['user_id'];
        $subscription->member = $dbResults['member'];
        $subscription->dateCreated = date(DATE_FORMAT, strtotime($dbResults['date_created']));
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
            $query .= ' user_id = :userId, member = :member, date_created = :dateCreated';
            $query .= ' WHERE subscription_id = :subscriptionId';
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
            $query .= ' (user_id, member, date_created)';
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
    
    /**
     * Delete a subscription record
     * @param int $subscriptionId Id of subscription to be deleted
     * @return void Subscription record is deleted from database
     */
    public function delete($subscriptionId)
    {
        Plugin::triggerEvent('subscription.delete');
        $db = Registry::get('db');
        $query = 'DELETE FROM ' . DB_PREFIX . 'subscriptions WHERE subscription_id = :subscriptionId';
        $db->query($query, array(':subscriptionId' => $subscriptionId));
    }
    
    
    public function isSubscribed($subscribingUser, $subscribedMember)
    {
        $db = Registry::get('db');
        $query = 'SELECT * FROM ' . DB_PREFIX . 'subscriptions WHERE user_id = :user AND member = :member';
        $db->fetchRow($query, array(':user' => $subscribingUser, ':member' =>$subscribedMember));
        return ($db->rowCount() == 1) ? true : false;
    }
}