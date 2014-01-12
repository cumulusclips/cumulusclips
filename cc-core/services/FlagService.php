<?php

class FlagService extends ServiceAbstract
{
    /**
     * Delete a flag
     * @param Flag $flag Instance of flage to be deleted
     * @return void Flag is deleted from system
     */
    public function delete(Flag $flag)
    {
        $flagMapper = $this->_getMapper();
        $flagMapper->delete($flag->flagId);
        Plugin::Trigger ('flag.delete');
    }

    /**
     * Perform flag related action on a record
     * @param integer $id The id of the record being updated
     * @param string $type Type of record being updated. Possible values are: video, user, comment
     * @param boolean $decision The action to be performed on the record. True bans, False declines the flag
     * @return void All flags raised against record are updated
     */
    public function FlagDecision ($id, $type, $decision)
    {
        $db = Database::GetInstance();
        if ($decision) {
            // Content is being banned - Update flag requests
            $query = "UPDATE " . DB_PREFIX . "flags SET status = 'approved' WHERE type = '$type' AND id = $id";
            $db->Query ($query);
        } else {
            // Ban request is declined - Update flag requests
            $query = "UPDATE " . DB_PREFIX . "flags SET status = 'declined' WHERE type = '$type' AND id = $id";
            $db->Query ($query);
        } 
    }
    
    /**
     * Retrieve instance of Flag mapper
     * @return FlagMapper Mapper is returned
     */
    protected function _getMapper()
    {
        return new FlagMapper();
    }
}