<?php

class CommentService extends ServiceAbstract
{
    public $found;
    private $db;
    protected static $table = 'comments';
    protected static $id_name = 'comment_id';

    /**
     * Delete a record
     * @param integer $id ID of record to be deleted
     * @return void Record is deleted from database
     */
    static function Delete ($id) {
        $db = Database::GetInstance();
        Plugin::Trigger ('comment.delete');
        $query = "DELETE FROM " . DB_PREFIX . self::$table . " WHERE " . self::$id_name . " = $id";
        $db->Query ($query);
    }

    /**
     * Make a comment visible to the public and notify user of new comment
     * @global object $config Site configuration settings
     * @param string $action Step in the approval proccess to perform. Allowed values: create|activate|approve
     * @return void Comment is activated, user is notified, and admin alerted.
     * If approval is required comment is marked pending and placed in queue
     */
    public function Approve ($action) {

        App::LoadClass ('User');
        App::LoadClass ('Video');
        App::LoadClass ('Privacy');
        App::LoadClass ('Mail');

        global $config;
        $send_alert = false;
        $video = new Video ($this->video_id);
        Plugin::Trigger ('comment.before_approve');

        
        // 1) Admin posted comment in Admin Panel
        // 2) Comment is posted by user
        // 3) Comment is being approved by admin for first time
        if ((in_array ($action, array ('create','activate'))) || $action == 'approve' && $this->released == 0) {

            // Comment is being posted by user, but approval is required
            if ($action == 'activate' && Settings::Get ('auto_approve_comments') == '0') {

                // Send Admin Approval Alert
                $send_alert = true;
                $subject = 'New Comment Awaiting Approval';
                $body = 'A new comment has been posted and is awaiting admin approval.';

                // Set Pending
                $this->Update (array ('status' => 'pending'));
                Plugin::Trigger ('comment.approve_required');

            } else {

                // Send Admin Alert
                if (in_array ($action, array ('create','activate')) && Settings::Get ('alerts_comments') == '1') {
                    $send_alert = true;
                    $subject = 'New Comment Posted';
                    $body = 'A new comment has been posted.';
                }

                // Activate & Release
                $this->Update (array ('status' => 'approved', 'released' => 1));

                // Send video owner new comment notifition, if opted-in
                $privacy = Privacy::LoadByUser ($video->user_id);
                if ($privacy->OptCheck ('video_comment')) {
                    $user = new User ($video->user_id);
                    $replacements = array (
                        'host'      => HOST,
                        'sitename'  => $config->sitename,
                        'email'     => $user->email,
                        'title'     => $video->title
                    );
                    $mail = new Mail();
                    $mail->LoadTemplate ('video_comment', $replacements);
                    $mail->Send ($user->email);
                    Plugin::Trigger ('comment.notify_member');
                }

                Plugin::Trigger ('comment.release');

            }

        // Comment is being re-approved
        } else if ($action == 'approve' && $this->released != 0) {
            // Activate Comment
            $this->Update (array ('status' => 'approved'));
            Plugin::Trigger ('comment.reapprove');
        }


        // Send admin alert
        if ($send_alert) {
            $body .= "\n\n=======================================================\n";
            $body .= "Author: $this->name\n";
            $body .= "Video URL: $video->url/\n";
            $body .= "Comments: $this->comments\n";
            $body .= "=======================================================";
            App::Alert ($subject, $body);
        }

        Plugin::Trigger ('comment.approve');

    }
}