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
    }

    /**
     * Perform flag related action on a record
     * @param Video|User|Comment $object Instance of the content being decided on
     * @param boolean $decision The action to be performed on the record. True bans, False declines the flag
     * @return void All flags raised against content are updated
     */
    public function flagDecision ($object, $decision)
    {
        if ($object instanceof Video) {
            $type = 'video';
            $id = $object->videoId;
        } else if ($object instanceof User) {
            $type = 'user';
            $id = $object->userId;
        } else if ($object instanceof Comment) {
            $type = 'comment';
            $id = $object->commentId;
        } else {
            throw new Exception('Unknown content type');
        }
        
        $db = Registry::get('db');
        $status = ($decision) ? 'approved' : 'declined';
        $query = "UPDATE " . DB_PREFIX . "flags SET status = '$status' WHERE type = '$type' AND object_id = $id";
        $db->query($query);
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