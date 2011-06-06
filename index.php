<?php


// new Video
if ($auto_approve) {
    $video->Update ('Approved');
    $video->Approve();
} else {
    $video->Update('Pending');
}


// new user
if ($auto_approve) {
    $user->Update ('Approved');
    $user->Approve();
} else {
    $user->Update('Pending');
}


// new comment
if ($auto_approve) {
    $comment->Update ('Approved');
    $comment->Approve();
} else {
    $comment->Update('Pending');
}
















function Approve() {

    if ($this->released == 0) {
        // Do Stuff
        $this->Update (array ('released' => 1));
    }

}




switch ($action) {
    case 'unban':
    case 'approve':
        Functions::AdminStatusChange($id, $type, 'approve');
        $record->Approve();
        break;
    case 'pending':
        Functions::AdminStatusChange($id, $type, $action);
        break;
    case 'ban':
        Functions::AdminStatusChange($id, $type, $action);
        Flag::FlagDecision ($id, $type, $action);
        break;
}

?>