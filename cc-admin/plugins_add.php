<?php

### Created on February 28, 2009
### Created by Miguel A. Hurtado
### This script displays the site homepage


// Include required files
include ('../cc-core/config/admin.bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Filesystem');


// Establish page variables, objects, arrays, etc
Plugin::Trigger ('admin.video_edit.start');
//$logged_in = User::LoginCheck(HOST . '/login/');
//$admin = new User ($logged_in);
$page_title = 'Add Plugin';
$message = null;






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


    // Validate file upload
    try {

        // Check file was uploaded
        if (empty ($_FILES['upload']['name'])) {
            throw new Exception ('Invalid plugin zip file');
        }


        // Check for browser upload errors
        if (!empty ($_FILES['upload']['error'])) {

            switch ($_FILES['upload']['error']) {

                case 1:
                case 2:
                    throw new Exception ('Uploaded file exceeds maximum upload filesize');
                    break;

                case 3:
                case 4:
                    throw new Exception ('File was not uploaded properly');
                    break;

                default:
                    throw new Exception ('A system error has occured');
                    break;

            }
            
        }

 
        // Validate mime-type sent by browser
        if (!preg_match ('/application\/(zip|x\-zip|octet\-stream|x\-zip\-compressed)/i', $_FILES['upload']['type'])) {
            throw new Exception ('Uploaded file was not in zip format/mime-type');
        }

   
        // Validate file extension
        $extension = Functions::GetExtension ($_FILES['upload']['name']);
        if (!preg_match ('/zip/i', $extension)) {
            throw new Exception ('Uploaded file was not in zip format');
        }
    
    } catch (Exception $e) {
        $errors['upload'] = $e->getMessage();
    }



    // Add plugin if no form errors were found
    if (empty ($errors)) {

        // Extract zip archive and move plugin
        try {

            // Create temp dir
            $temp = DOC_ROOT . '/cc-content/.add-plugin';
            Filesystem::Open();
            Filesystem::CreateDir ($temp);


            // Make temp dir writeable
            Filesystem::SetPermissions ($temp, 0777);


            // Move zip to temp dir
            if (!move_uploaded_file ($_FILES['upload']['tmp_name'], $temp . '/plugin.zip')) {
                $clean_up = true;
                throw new Exception ('Uploaded file could not be moved from OS temp directory');
            }


            // Extract plugin
            Filesystem::Extract ($temp . '/plugin.zip');


            // Check for duplicates
            $temp_contents = array_diff (scandir ($temp), array ('.', '..', 'plugin.zip'));
            $plugin_name = array_pop ($temp_contents);
            if (file_exists (DOC_ROOT . '/cc-content/plugins/' . $plugin_name)) {
                $clean_up = true;
                throw new Exception ("Plugin cannot be added. It conflicts with another plugin.");
            }


            // Copy plugin cotnents to plugins dir
            Filesystem::CopyDir ($temp . '/' . $plugin_name, DOC_ROOT . '/cc-content/plugins/' . $plugin_name);


            // Validate Plugin
            if (!Plugin::ValidPlugin ($plugin_name)) {
                $clean_up = true;
                throw new Exception ("Plugin contains errors. Please report this to it's developer");
            }


            // Clean up
            Filesystem::Delete ($temp);
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
            $message = $plugin_info->name . ' has been added successfully!';
            $message_type = 'success';


        } catch (Exception $e) {

            $message = $e->getMessage();
            $message_type = 'error';

            // Perform clean up if plugin contained errors
            if (isset ($clean_up)) {
                try {
                    Filesystem::Delete ($temp);
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

    <h1>Add Plugins</h1>

    <?php if ($message): ?>
    <div class="<?=$message_type?>"><?=$message?></div>
    <?php endif; ?>


    <div class="block">

        <p class="row-shift">If you have a plugin in .zip format use this form
        to upload and add it to the system.</p>


        <form action="<?=ADMIN?>/plugins_add.php" method="post" enctype="multipart/form-data">

            <div id="upload-row" class="row <?=(isset($errors['upload'])) ? ' errors' : ''?>">
                <label>*Plugin Zip File:</label>
                <input id="upload-visible" class="text" type="text" name="upload-visible" />
                <input type="button" class="button" value="Browse" />
                <input id="upload" type="file" name="upload" />
            </div>

            <div class="row <?=(isset ($errors['enable'])) ? 'errors' : ''?>">
                <label>*Enable Plugin:</label>
                <div id="enable-options">
                    <input type="radio" name="enable" id="auto-enable" value="auto-enable" checked="checked" />
                    <label for="auto-enable">Automatically enable plugin</label>

                    <input type="radio" name="enable" id="dont-enable" value="dont-enable" />
                    <label for="dont-enable">Upload but do not enable plugin</label>
                </div>
            </div>

            <div class="row-shift">
                <input type="hidden" name="MAX_FILE_SIZE" value="<?=1024*1024*100?>" />
                <input type="hidden" name="submitted" value="TRUE" />
                <input type="submit" class="button" value="Add Plugin" />
            </div>
            
        </form>

    </div>


</div>

<?php include ('footer.php'); ?>