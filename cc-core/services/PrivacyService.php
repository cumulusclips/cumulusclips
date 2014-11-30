<?php

class PrivacyService extends ServiceAbstract
{
    /**
     * Delete a privacy entry
     * @param Privacy $privacy Instance of privacy entry to be deleted
     * @return void Privacy entry is deleted from system
     */
    public function delete(Privacy $privacy)
    {
        $privacyMapper = $this->_getMapper();
        $privacyMapper->delete($privacy->privacyId);
    }

    /**
     * Verify if user accepts message type
     * @param User $user The user being checked for message type opt-in
     * @param string $messageType The message type to check
     * @return boolean Returns true if user accepts message type, false otherwise
     */
    public function optCheck(User $user, $messageType)
    {
        $privacyMapper = new PrivacyMapper();
        $privacy = $privacyMapper->getPrivacyByUser($user->userId);
        return ($privacy->$messageType === true);
    }
    
    /**
     * Retrieve instance of Privacy mapper
     * @return PrivacyMapper Mapper is returned
     */
    protected function _getMapper()
    {
        return new PrivacyMapper();
    }
}