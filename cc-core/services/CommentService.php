<?php

class CommentService extends ServiceAbstract
{
    /**
     * Delete a comment
     * @param Comment $comment Instance of comment to be deleted
     * @return void Comment is deleted from system
     */
    public function delete(Comment $comment)
    {
        $commentMapper = $this->_getMapper();
        $commentMapper->delete($comment->commentId);
        Plugin::triggerEvent('comment.delete');
    }

    /**
     * Make a comment visible to the public and notify user of new comment
     * @param Comment $comment The comment being approved
     * @param string $action Step in the approval proccess to perform. Allowed values: create|activate|approve
     * @return void Comment is activated, user is notified, and admin alerted.
     * If approval is required comment is marked pending and placed in queue
     */
    public function approve(Comment $comment, $action)
    {
        $config = Registry::get('config');
        $send_alert = false;
        $videoMapper = new VideoMapper();
        $videoService = new VideoService();
        $commentMapper = new CommentMapper();
        $video = $videoMapper->getVideoById($comment->videoId);
        Plugin::triggerEvent('comment.before_approve');
        
        // 1) Admin posted comment in Admin Panel
        // 2) Comment is posted by user
        // 3) Comment is being approved by admin for first time
        if ($action == 'create' || ($action == 'approve' && !$comment->released)) {

            // Comment is being posted by user, but approval is required
            if ($action == 'create' && Settings::get('auto_approve_comments') == '0') {

                // Send Admin Approval Alert
                $send_alert = true;
                $subject = 'New Comment Awaiting Approval';
                $body = 'A new comment has been posted and is awaiting admin approval.';

                // Set Pending
                $comment->status = 'pending';
                $commentMapper->save($comment);
                Plugin::triggerEvent('comment.approve_required');
                
            } else {

                // Send Admin Alert
                if ($action == 'create' && Settings::get('alerts_comments') == '1') {
                    $send_alert = true;
                    $subject = 'New Comment Posted';
                    $body = 'A new comment has been posted.';
                }

                // Activate & Release
                $comment->status = 'approved';
                $comment->released = true;
                $commentMapper->save($comment);

                // Send video owner new comment notifition, if opted-in and he wasn't comment author
                $privacyService = new PrivacyService();
                $userMapper = new UserMapper();
                $user = $userMapper->getUserById($video->userId);
                if ($comment->userId != $video->userId && $privacyService->OptCheck($user, Privacy::VIDEO_COMMENT)) {
                    $replacements = array (
                        'host'      => HOST,
                        'sitename'  => $config->sitename,
                        'email'     => $user->email,
                        'title'     => $video->title
                    );
                    $mail = new Mail();
                    $mail->LoadTemplate('video_comment', $replacements);
                    $mail->Send($user->email);
                    Plugin::triggerEvent('comment.notify_member');
                }

                Plugin::triggerEvent('comment.release');
            }

        // Comment is being re-approved
        } else if ($action == 'approve' && $comment->released) {
            Plugin::triggerEvent('comment.reapprove');
        }

        // Send admin alert
        if ($send_alert) {
            $body .= "\n\n=======================================================\n";
            $body .= "Author: $comment->name\n";
            $body .= 'Video URL: ' . $videoService->getUrl($video) . "/\n";
            $body .= "Comments: $comment->comments\n";
            $body .= "=======================================================";
            App::Alert($subject, $body);
        }

        Plugin::triggerEvent('comment.approve');
    }
    
    /**
     * Retrieve avatar image to display for a comment's author
     * @param Comment $comment Instance of comment to find avatar for
     * @return string Returns URL to avatar image of comment's author, if any, or default avatar image
     */
    public function getCommentAvatar(Comment $comment)
    {
        if (empty($comment->userId)) {
            return false;
        } else {
            $userService = new UserService();
            $userMapper = new UserMapper();
            $user = $userMapper->getUserById($comment->userId);
            return $userService->getAvatarUrl($user);
        }
    }
    
    /**
     * Retrieve instance of Comment mapper
     * @return CommentMapper Mapper is returned
     */
    protected function _getMapper()
    {
        return new CommentMapper();
    }
}