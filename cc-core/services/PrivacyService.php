<?php

class PrivacyService extends ServiceAbstract
{
    /**
     * Delete a record
     * @param integer $id ID of record to be deleted
     * @return void Record is deleted from database
     */
    public static function delete($id)
    {
        $db = Database::GetInstance();
        Plugin::Trigger ('privacy.delete');
        $query = "DELETE FROM " . DB_PREFIX . self::$table . " WHERE " . self::$id_name . " = $id";
        $db->Query ($query);
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
}