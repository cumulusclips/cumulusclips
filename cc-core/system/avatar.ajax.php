<?php

// Include required files
include_once (dirname (dirname (__FILE__)) . '/config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Avatar');


### Retrieve video information
if (!isset ($_POST['token'], $_POST['timestamp'])) App::Throw404();

session_write_close();
session_id ($_POST['token']);
session_start();


// Validate upload key
$upload_key = md5 (md5 ($_POST['timestamp']) . SECRET_KEY);
if (!isset ($_SESSION['upload_key']) || $_SESSION['upload_key'] != $upload_key) App::Throw404();
Functions::RedirectIf ($logged_in = User::LoginCheck(), HOST . '/login/');
$user = new User ($logged_in);




try {

    ### Verify upload was made
    if (empty ($_FILES) || !isset ($_FILES['upload']['name'])) {
        throw new Exception (Language::GetText('error_uploadify_empty')) ;
    }
    


    ### Check for upload errors
    if ($_FILES['upload']['error'] != 0) {
        App::Alert ('Error During Avatar Upload', 'There was an HTTP FILE POST error (Error code #' . $_FILES['upload']['error'] . ').');
        throw new Exception (Language::GetText('error_uploadify_system', array ('host' => HOST)));
    }



    ### Validate filesize
    if ($_FILES['upload']['size'] > 1024*30 || filesize ($_FILES['upload']['tmp_name']) > 1024*30) {
        throw new Exception (Language::GetText('error_uploadify_filesize'));
    }



    ### Validate avatar extension
    $extension = Functions::GetExtension ($_FILES['upload']['name']);
    if (!in_array ($extension, array('gif','png','jpg','jpeg'))) {
        throw new Exception (Language::GetText('error_uploadify_extension'));
    }



    ### Validate image data
    $handle = fopen ($_FILES['upload']['tmp_name'],'r');
    $image_data = fread ($handle, filesize ($_FILES['upload']['tmp_name']));
    if (!@imagecreatefromstring ($image_data)) throw new Exception (Language::GetText('error_uploadify_extension'));



    ### Change permissions on avatar & delete previous IF/APP
    try {
        $avatar_path = UPLOAD_PATH . '/avatars';
        $save_as = Avatar::CreateFilename ($extension);
        Avatar::SaveAvatar ($_FILES['upload']['tmp_name'], $extension, $save_as);

        // Check for existing avatar
        if (!empty ($user->avatar)) Avatar::Delete ($user->avatar);

        Filesystem::setPermissions("$avatar_path/$save_as", 0644);
    } catch (Exception $e) {
        App::Alert ('Error During Avatar Upload', $e->getMessage());
        throw new Exception (Language::GetText('error_uploadify_system', array ('host' => HOST)));
    }



    ### Update User
    $user->Update (array ('avatar' => $save_as));
    Plugin::Trigger ('update_profile.update_avatar');

    // Output success message
    exit (json_encode (array ('result' => 1, 'msg' => (string) Language::GetText('success_avatar_updated'), 'other' => $user->avatar_url)));

} catch (Exception $e) {
    exit (json_encode (array ('result' => 0, 'msg' => $e->getMessage())));
}

?>