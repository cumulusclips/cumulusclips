<?php

class SubscriptionService extends ServiceAbstract
{
    /**
     * Determines whether user is subscribed to member
     * @param int $subscribingUserId User Id of subscribing user
     * @param int $memberUserId User id of member being subscribed to
     * @return boolean Returns boolean true if user is subscribed, false otherwise
     */
    public function checkSubscription($subscribingUserId, $memberUserId)
    {
        $subscriptionMapper = new SubscriptionMapper();
        return (boolean) $subscriptionMapper->getSubscriptionByCustom(array(
            'user_id' => $subscribingUserId,
            'member' => $memberUserId
        ));
    }
}