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

                // Notify comment author of reply to their comment if apl.
                if ($comment->parentId) {
                    $parentComment = $commentMapper->getCommentById($comment->parentId);
                    $parentAuthor = $this->getCommentAuthor($parentComment);
                    
                    // Verify parent comment author is opted-in and not replying to himself
                    if ($comment->userId != $parentComment->userId && $privacyService->OptCheck($parentAuthor, Privacy::COMMENT_REPLY)) {
                        $replacements = array (
                            'host'      => HOST,
                            'sitename'  => $config->sitename,
                            'email'     => $parentAuthor->email,
                            'title'     => $video->title,
                            'videoUrl'  => $videoService->getUrl($video),
                            'comments'  => $comment->comments
                        );
                        $mail = new Mail();
                        $mail->LoadTemplate('comment_reply', $replacements);
                        $mail->Send($parentAuthor->email);
                    }
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
     * Generate a comment card for a given comment
     * @param Comment $comment The comment to generate the comment card for
     * @return CommentCard Returns comment card for given comment
     */
    public function getCommentCard(Comment $comment)
    {
        $commentMapper = $this->_getMapper();
        $commentCard = new CommentCard();
        $commentCard->comment = $comment;
        $commentCard->author = $this->getCommentAuthor($comment);
        $commentCard->avatar = $this->getCommentAvatar($comment);

        // Retrieve parent comment if any
        if ($comment->parentId != 0) {
            $commentCard->parentComment = $commentMapper->getCommentById($comment->parentId);
            $commentCard->parentAuthor = $this->getCommentAuthor($commentCard->parentComment);
        }
        return $commentCard;
    }

    /**
     * Retrieve subset of a video's comments
     * @param Video $video Video for which to retrieve comments
     * @param int $limit Amount of comments to retrieve
     * @param int $offset Comment Id of comment to use as a starting point (will return succeeding comments, not including this one)
     * @return array Returns list of CommentsCards
     */
    public function getVideoComments(Video $video, $limit, $offsetId = 0)
    {
        $commentCards = array();
        $commentList = $this->_getCommentThread($video->videoId, $limit, $offsetId);
        $commentMapper = $this->_getMapper();
        foreach ($commentList as $commentId) {
            $comment = $commentMapper->getCommentById($commentId);
            $commentCards[] = $this->getCommentCard($comment);
        }
        return $commentCards;
    }

    protected function _getCommentThread($videoId, $limit, $offsetId = 0, $thread = array())
    {
        $commentMapper = $this->_getMapper();

        // Get children of starting comment
        // recursive trickle down - stop when no more children or limit is reached
        $thread = $this->_getChildCommentThread($videoId, $limit, $offsetId, $thread);

        // Root thread requested (Above would have retrieved all neccessary comments)
        if ($offsetId == 0) return $thread;

        // Get sibblings of starting comment
        $startingPointComment = $commentMapper->getCommentById($offsetId);
        if (!$startingPointComment) throw new Exception('Offset comment not found');
        $startingPointParentId = $startingPointComment->parentId;
        $results = $commentMapper->getThreadedCommentIds($videoId, $limit, $startingPointParentId, $offsetId);
        foreach ($results as $sibblingId) {
            if (count($thread) == $limit) return $thread;
            $thread[] = $sibblingId;
            // Get children of starting comment's sibblings (nephews)
            $thread = $this->_getChildCommentThread($videoId, $limit, $sibblingId, $thread);
        }
        
        // Verify we're not at the root of the thread
        if ($startingPointParentId != 0) {
            
            // Find sibblings of parent comment(uncles)
            // Recursively bubble up - stop when no more comments or limit is reached
            $bubbleUpParentId = $startingPointParentId;
            while ($bubbleUpParentId != 0) {
                $parentComment = $commentMapper->getCommentById($bubbleUpParentId);
                $results = $commentMapper->getThreadedCommentIds($videoId, $limit, $parentComment->parentId, $parentComment->commentId);
                foreach ($results as $uncleId) {
                    if (count($thread) == $limit) return $thread;
                    $thread[] = $uncleId;
                    // Get children of parent comment's sibblings (cousins, 2nd cousings, etc.)
                    $thread = $this->_getCommentThread($videoId, $limit, $uncleId, $thread);
                }
                if (count($thread) == $limit) return $thread;
                $bubbleUpParentId = $parentComment->parentId;
            }
        }
        return $thread;
    }

    protected function _getChildCommentThread($videoId, $limit, $parentId, $thread = array())
    {
        $commentMapper = $this->_getMapper();
        $results = $commentMapper->getThreadedCommentIds($videoId, $limit, $parentId);
        foreach ($results as $childId) {
            if (count($thread) == $limit) break;
            $thread[] = $childId;
            // Trickle down to find all all children, grandchildren, etc.
            $thread = $this->_getChildCommentThread($videoId, $limit, $childId, $thread);
        }
        return $thread;
    }

    /**
     * Retrieve avatar image to display for a comment's author
     * @param Comment $comment Instance of comment to find avatar for
     * @return string|null Returns URL to avatar image of comment's author, or null if user doesn't have an avatar set
     */
    public function getCommentAvatar(Comment $comment)
    {
        $userService = new UserService();
        $userMapper = new UserMapper();
        $user = $userMapper->getUserById($comment->userId);
        return $userService->getAvatarUrl($user);
    }

    /**
     * Retrieve author of a comment 
     * @param Comment $comment Comment to retrieve author for
     * @return User Returns instance of User for comment's author
     */
    public function getCommentAuthor(Comment $comment)
    {
        $userMapper = new UserMapper();
        return $userMapper->getUserById($comment->userId);
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