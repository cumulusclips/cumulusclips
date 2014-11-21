<?php

class MessageService extends ServiceAbstract
{
    /**
     * Remove a message from the system
     * @param Message $message Message to be deleted
     * @return MessageService Provides fluent interface
     */
    public function delete(Message $message)
    {
        $messageMapper = new MessageMapper();
        $messageMapper->delete($message->messageId);
        return $this;
    }
}