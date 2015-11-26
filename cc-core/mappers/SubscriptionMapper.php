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
        $query = preg_replace('/\sAND\s$/', '', $query);
        
        $dbResults = $db->fetchRow($query, $queryParams);
        if ($db->rowCount() > 0) {
            return $this->_map($dbResults);
        } else {
            return false;
        }
    }
    
    public function getMultipleSubscriptionsByCustom(array $params)
    {
        $db = Registry::get('db');
        $query = 'SELECT * FROM ' . DB_PREFIX . 'subscriptions WHERE ';
        
        $queryParams = array();
        foreach ($params as $fieldName => $value) {
            $query .= "$fieldName = :$fieldName AND ";
            $queryParams[":$fieldName"] = $value;
        }
        $query = preg_replace('/\sAND\s$/', '', $query);
        $dbResults = $db->fetchAll($query, $queryParams);
        
        $subscriptionList = array();
        foreach($dbResults as $record) {
            $subscriptionList[] = $this->_map($record);
        }
        return $subscriptionList;
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
        $db = Registry::get('db');
        if (!empty($subscription->subscriptionId)) {
            // Update
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
        return $subscriptionId;
    }
    
    /**
     * Delete a subscription record
     * @param int $subscriptionId Id of subscription to be deleted
     * @return SubscriptionMapper Provides fluent interface
     */
    public function delete($subscriptionId)
    {
        $db = Registry::get('db');
        $query = 'DELETE FROM ' . DB_PREFIX . 'subscriptions WHERE subscription_id = :subscriptionId';
        $db->query($query, array(':subscriptionId' => $subscriptionId));
    }
}