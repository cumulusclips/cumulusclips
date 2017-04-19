<?php

class CommentService extends ServiceAbstract
{
    /**
     * Delete a comment and all it's descendant comments
     * @param Comment $comment Instance of comment to be deleted
     * @return void Comment is deleted from system
     */
    public function delete(Comment $comment)
    {
        $commentMapper = $this->_getMapper();
        $commentCount = $commentMapper->getVideoCommentCount($comment->videoId);
        $commentsToDelete = $this->_getChildCommentThread($comment->videoId, $commentCount, $comment->commentId);
        $commentsToDelete[] = $comment->commentId;
        foreach ($commentsToDelete as $commentId) {
            $commentMapper->delete($commentId);
        }
    }

    /**
     * Approve a comment for display according to who posted, what site settings are,
     * and whether or not comment has been previously approved
     * @param Comment $comment The comment being approved
     * @param string $action Step in the approval proccess to perform. Allowed values: create|activate|approve
     * @return CommentService Provides fluent interface
     * @throws Exception Thrown if invalid action is provided
     */
    public function approve(Comment $comment, $action)
    {
        $videoMapper = new VideoMapper();
        $videoService = new VideoService();
        $video = $videoMapper->getVideoById($comment->videoId);
        $commentMapper = $this->_getMapper();

        // Determine which comment action is being performed
        if ($action == 'create') {

            // Check if auto-approval of comments is turned on
            if (Settings::get('auto_approve_comments') == '1') {
                $this->_releaseComment($comment);
            } else {
                // Check if admin created  comment (auto-approve is so)
                $commentAuthor = $this->getCommentAuthor($comment);
                if ($commentAuthor->role == 'administrator') {
                    $this->_releaseComment($comment);
                } else {
                    // Set Pending
                    $comment->status = 'pending';
                    $commentMapper->save($comment);

                    // Send Admin Approval Alert
                    $subject = 'New Comment Awaiting Approval';
                    $body = 'A new comment has been posted and is awaiting admin approval.';
                    $body .= "\n\n=======================================================\n";
                    $body .= 'Author: ' . $this->getCommentAuthor($comment)->username . "\n";
                    $body .= 'Video URL: ' . $videoService->getUrl($video) . "/\n";
                    $body .= "Comments: $comment->comments\n";
                    $body .= "=======================================================";
                    App::alert($subject, $body);
                }
            }

        } else if ($action == 'approve') {
            // Check if comment has been approved in the past
            if (!$comment->released) {
                $this->_releaseComment($comment);
            } else {
                // Approve comment
                $comment->status = 'approved';
                $commentMapper->save($comment);
            }

        } else {
            throw new Exception('Invalid comment approval action');
        }
    }

    /**
     * Mark comment as approved and notify any stakeholders of new comment
     * @param Comment $comment Comment being approved
     * @return CommentService Provides fluent interface
     */
    protected function _releaseComment(Comment $comment)
    {
        $config = Registry::get('config');
        $videoMapper = new VideoMapper();
        $videoService = new VideoService();
        $video = $videoMapper->getVideoById($comment->videoId);
        $commentMapper = $this->_getMapper();

        // Approve comment
        $comment->status = 'approved';
        $comment->released = true;
        $commentMapper->save($comment);

        // Send admin alert
        if (Settings::get('alerts_comments') == '1') {
            $subject = 'New Comment Posted';
            $body = 'A new comment has been posted.';
            $body .= "\n\n=======================================================\n";
            $body .= 'Author: ' . $this->getCommentAuthor($comment)->username . "\n";
            $body .= 'Video URL: ' . $videoService->getUrl($video) . "/\n";
            $body .= "Comments: $comment->comments\n";
            $body .= "=======================================================";
            App::alert($subject, $body);
        }

        // Send video owner notifition of new comment, if opted-in and he wasn't comment author
        $privacyService = new PrivacyService();
        $userMapper = new UserMapper();
        $user = $userMapper->getUserById($video->userId);
        if ($comment->userId != $video->userId && $privacyService->optCheck($user, Privacy::VIDEO_COMMENT)) {
            $replacements = array (
                'host'      => HOST,
                'sitename'  => $config->sitename,
                'email'     => $user->email,
                'title'     => $video->title
            );
            $mailer = new Mailer($config);
            $mailer->setTemplate('video_comment', $replacements);
            $mailer->send($user->email);
        }

        // Notify comment author of reply to their comment if apl.
        if ($comment->parentId) {
            $parentComment = $commentMapper->getCommentById($comment->parentId);
            $parentAuthor = $this->getCommentAuthor($parentComment);

            // Verify parent comment author is opted-in and not replying to himself
            if ($comment->userId != $parentComment->userId && $privacyService->optCheck($parentAuthor, Privacy::COMMENT_REPLY)) {
                $replacements = array (
                    'host'      => HOST,
                    'sitename'  => $config->sitename,
                    'email'     => $parentAuthor->email,
                    'title'     => $video->title,
                    'videoUrl'  => $videoService->getUrl($video),
                    'comments'  => $comment->comments
                );
                $mailer = new Mailer($config);
                $mailer->setTemplate('comment_reply', $replacements);
                $mailer->send($parentAuthor->email);
            }
        }
        return $this;
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

    /**
     * Generates a thread of comments from a given point. Traversal is done until no more comments are found or limit is reached
     * @param int $videoId The video whose comments are being retrieved
     * @param int $limit The number of comment ids to include in the output thread
     * @param int $offsetId (optional) The comment id of the starting point within the thread. If not provided, retrieves root comments
     * @param array $thread (optional) Starting thread of comments which will beadded to. If not provided, the returned thread will be built out from scratch
     * @return array Returns a thread of comment ids based on limit and starting point rules
     * @throws Exception Thrown if given starting point is not a valid comment id
     */
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

    /**
     * Retrieves a parent comment's descendant threads. Traversal is done until no more descendants are found or limit is reached
     * @param int $videoId The video whose comments are being retrieved
     * @param int $limit The number of comment ids to include in the output thread
     * @param int $parentId Id of parent comment whose descendant comments are being
     * @param array $thread (optional) Starting thread of comments which will be added to. If not provided, the returned thread will be built out from scratch
     * @return array Returns given parent comment's descendants threads
     */
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