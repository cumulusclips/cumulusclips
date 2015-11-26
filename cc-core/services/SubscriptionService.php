<?php

class SubscriptionService extends ServiceAbstract
{
    /**
     * Retrieve instance of Subscription mapper
     * @return SubscriptionMapper Mapper is returned
     */
    protected function _getMapper()
    {
        return new SubscriptionMapper();
    }

    /**
     * Subscribe a user to another member
     * @param int $subscribingUserId User Id of subscribing user
     * @param int $memberUserId User id of member being subscribed to
     * @return void User is subscribed
     */
    public function subscribe($subscribingUserId, $memberUserId)
    {
        $subscription = new Subscription();
        $subscription->userId = $subscribingUserId;
        $subscription->member = $memberUserId;
        $subscriptionMapper = $this->_getMapper();
        $subscriptionMapper->save($subscription);
    }

    /**
     * Unubscribe a user from another member
     * @param int $unsubscribingUserId User Id of unsubscribing user
     * @param int $memberUserId User id of member being unsubscribed from
     * @return void User is unsubscribed
     */
    public function unsubscribe($unsubscribingUserId, $memberUserId)
    {
        $subscriptionMapper = $this->_getMapper();
        $subscription = $subscriptionMapper->getSubscriptionByCustom(array(
            'user_id' => $unsubscribingUserId,
            'member' => $memberUserId
        ));
        $this->delete($subscription);
    }

    /**
     * Determines whether user is subscribed to member
     * @param int $subscribingUserId User Id of subscribing user
     * @param int $memberUserId User id of member being subscribed to
     * @return boolean Returns boolean true if user is subscribed, false otherwise
     */
    public function checkSubscription($subscribingUserId, $memberUserId)
    {
        $subscriptionMapper = $this->_getMapper();
        return (boolean) $subscriptionMapper->getSubscriptionByCustom(array(
            'user_id' => $subscribingUserId,
            'member' => $memberUserId
        ));
    }
    
    /**
     * Deletes a subscription from the system
     * @param Subscription $subscription Subscription being deleted
     * @return SubscriptionService Provides fluent interface
     */
    public function delete(Subscription $subscription)
    {
        $subscriptionMapper = $this->_getMapper();
        $subscriptionMapper->delete($subscription->subscriptionId);
        return $this;
    }
}