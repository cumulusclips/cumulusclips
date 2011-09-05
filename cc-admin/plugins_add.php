<?php

// Include required files
include_once (dirname (dirname (__FILE__)) . '/cc-core/config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Filesystem');


// Establish page variables, objects, arrays, etc
Plugin::Trigger ('admin.video_edit.start');
Functions::RedirectIf ($logged_in = User::LoginCheck(), HOST . '/login/');
$admin = new User ($logged_in);
Functions::RedirectIf (User::CheckPermissions ('admin_panel', $admin), HOST . '/myaccount/');
$page_title = 'Add New Plugin';
$message = null;
$admin_js[] = ADMIN . '/extras/uploadify/swfobject.js';
$admin_js[] = ADMIN . '/extras/uploadify/jquery.uploadify.v2.1.4.min.js';
$admin_js[] = ADMIN . '/js/uploadify.js';
$admin_css[] = ADMIN . '/extras/uploadify/uploadify.css';
$admin_meta['uploadHandler'] = ADMIN . '/plugins_add_ajax.php';
$admin_meta['token'] = session_id();
$admin_meta['sizeLimit'] = 1024*1024*100;
$admin_meta['fileDesc'] = 'Supported File Formats: (*.zip)';
$admin_meta['fileExt'] = '*.zip';
$timestamp = time();
$_SESSION['upload_key'] = md5 (md5 ($timestamp) . SECRET_KEY);
$clean_up = true;





/*************************
Handle Upload picture Form
*************************/

if (isset ($_POST['submitted'])) {
    
    // Validate enable option
    if (!empty ($_POST['enable']) && in_array ($_POST['enable'], array ('auto-enable', 'dont-enable'))) {
        $enable = ($_POST['enable'] == 'auto-enable') ? true : false;
    } else {
        $errors['enable'] = 'Invalid enable option selected';
    }


    ### Validate file upload
    try {

        // Validate timestamp
        if (!empty ($_POST['timestamp']) && is_numeric ($_POST['timestamp'])) {
            $upload_key = md5 (md5 ($_POST['timestamp']) . SECRET_KEY);
        } else {
            throw new Exception ('Invalid timestamp');
        }

        // Verify file AJAX values were set
        if (!empty ($_SESSION['upload'])) {
            $upload = unserialize ($_SESSION['upload']);
        } else {
            throw new Exception ('Invalid file upload');
        }

        // Validate video upload
        if (!is_array ($upload)) throw new Exception ('Invalid file upload');
        if (empty ($upload['key']) || empty ($upload['temp'])) throw new Exception ('Invalid file upload');
        if (!file_exists ($upload['temp'])) throw new Exception ('Invalid file upload');
        if ($upload['key'] != $upload_key) throw new Exception ('Invalid file upload');

        $_SESSION['upload'] = serialize (array ('key' => $_SESSION['upload_key'], 'temp' => $upload['temp'], 'name' => $upload['name']));
        $data['upload'] = $upload;

    } catch (Exception $e) {
        $errors['upload'] = $e->getMessage();
    }



    // Add plugin if no form errors were found
    if (empty ($errors)) {

        // Extract zip archive and move plugin
        try {

            // Extract plugin
            $temp_dir = DOC_ROOT . '/cc-content/.add-plugin';
            Filesystem::Open();
            Filesystem::Extract ($temp_dir . '/plugin.zip');


            // Check for duplicates
            $temp_contents = array_diff (scandir ($temp_dir), array ('.', '..', 'plugin.zip'));
            $plugin_name = array_pop ($temp_contents);
            if (file_exists (DOC_ROOT . '/cc-content/plugins/' . $plugin_name)) {
                throw new Exception ("Plugin cannot be added. It conflicts with another plugin.");
            }


            // Copy plugin cotnents to plugins dir
            Filesystem::CopyDir ($temp_dir . '/' . $plugin_name, DOC_ROOT . '/cc-content/plugins/' . $plugin_name);


            // Validate Plugin
            if (!Plugin::ValidPlugin ($plugin_name)) {
                throw new Exception ("Plugin contains errors. Please report this to it's developer");
            }


            // Clean up
            $clean_up = false;
            Filesystem::Delete ($temp_dir);
            Filesystem::Close();


            // Automatically enable plugin if requested by user
            if ($enable) {

                $installed_plugins = unserialize (Settings::Get ('installed_plugins'));
                $enabled_plugins = Plugin::GetEnabledPlugins();

                // Install plugin if applicable
                if (!in_array ($plugin_name, $installed_plugins)) {
                    if (method_exists ($plugin_name, 'Install')) call_user_func (array ($plugin_name, 'Install'));
                    $installed_plugins[] = $plugin_name;
                    Settings::Set ('installed_plugins', serialize ($installed_plugins));
                }

                // Enable plugin
                $enabled_plugins[] = $plugin_name;
                Settings::Set ('enabled_plugins', serialize ($enabled_plugins));

            }


            // Display success message
            $plugin_info = Plugin::GetPluginInfo ($plugin_name);
            $message = $plugin_info->name . ' has been added.';
            $message_type = 'success';
            unset ($data);


        } catch (Exception $e) {

            $message = $e->getMessage();
            $message_type = 'error';

            // Perform clean up if plugin contained errors
            if ($clean_up) {
                try {
                    Filesystem::Delete ($temp_dir);
                    Filesystem::Close();
                } catch (Exception $e) {
                    $message = $e->getMessage();
                    $message_type = 'error';
                }
            }

        }   //  END extract and move plugin


    } else {
        $message = 'The following error occured. Please correct it and try again.<br /><br /> - ';
        $message .= implode ('<br /> - ', $errors);
        $message_type = 'error';
    }   // END check for form errors

}


// Output Header
include ('header.php');

?>

<div id="plugins-add">

    <h1>Add New Plugin</h1>

    <?php if ($message): ?>
    <div class="<?=$message_type?>"><?=$message?></div>
    <?php endif; ?>


    <div class="block">

        <p class="row-shift">If you have a plugin in .zip format use this form
        to upload and add it to the system.</p>


        <form action="<?=ADMIN?>/plugins_add.php" method="post">

            <div class="row <?=(isset ($errors['upload'])) ? 'errors' : ''?>">
                <label>Plugin Zip File:</label>
                <div id="upload-box">
                    <input id="browse-button" type="button" class="button" value="Browse" />
                    <input id="upload" type="file" name="upload" />
                    <div class="uploadifyQueue" id="uploadQueue">
                    <?php if (isset ($data['upload'])): ?>
                        <div class="uploadifyQueueItem"><span class="fileName"><?=$data['upload']['name']?> - has been uploaded</span></div>
                    <?php endif; ?>
                    </div>
                    <input id="upload-button" type="button" class="button" value="Upload" />
                </div>
            </div>

            <div class="row <?=(isset ($errors['enable'])) ? 'errors' : ''?>">
                <label>Enable Plugin:</label>
                <div id="enable-options">
                    <input type="radio" name="enable" id="auto-enable" value="auto-enable" checked="checked" />
                    <label for="auto-enable">Automatically enable plugin</label>

                    <input type="radio" name="enable" id="dont-enable" value="dont-enable" />
                    <label for="dont-enable">Upload but do not enable plugin</label>
                </div>
            </div>

            <div class="row-shift">
                <input type="hidden" name="timestamp" value="<?=$timestamp?>" id="timestamp" />
                <input type="hidden" name="submitted" value="TRUE" />
                <input type="submit" class="button" value="Add Plugin" />
            </div>
            
        </form>

    </div>


</div>

<?php include ('footer.php'); ?>