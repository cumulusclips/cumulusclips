<?php

### Created on February 28, 2009
### Created by Miguel A. Hurtado
### This script displays the site homepage


// Include required files
include ('../cc-core/config/admin.bootstrap.php');
App::LoadClass ('User');


// Establish page variables, objects, arrays, etc
Plugin::Trigger ('admin.member_edit.start');
//$logged_in = User::LoginCheck(HOST . '/login/');
//$admin = new User ($logged_in);
$page_title = 'Add New Member';
$data = array();
$errors = array();
$message = null;





/***********************
Handle form if submitted
***********************/

if (isset ($_POST['submitted'])) {

    // Validate Username
    if (!empty ($_POST['username']) && !ctype_space ($_POST['username'])) {
        if (!User::Exist (array ('username' => $_POST['username']))) {
            $data['username'] = htmlspecialchars (trim ($_POST['username']));
        } else {
            $errors['username'] = 'Username is unavailable';
        }
    } else {
        $errors['username'] = 'Invalid username';
    }


    // Validate password
    if (!empty ($_POST['password']) && !ctype_space ($_POST['password'])) {
        $data['password'] = htmlspecialchars (trim ($_POST['password']));
    } else {
        $errors['password'] = 'Invalid password';
    }


    // Validate email
    if (!empty ($_POST['email']) && preg_match ('/^[a-z0-9][a-z0-9\._-]+@[a-z0-9][a-z0-9\.-]+\.[a-z0-9]{2,4}$/i', $_POST['email'])) {
        if (!User::Exist (array ('email' => $_POST['email']))) {
            $data['email'] = htmlspecialchars (trim ($_POST['email']));
        } else {
            $errors['email'] = 'Email is unavailable';
        }
    } else {
        $errors['email'] = 'Invalid email address';
    }


    // Validate make admin
    if (isset ($_POST['admin']) && $_POST['admin'] == '1') {
        $data['admin'] = 1;
    }


    // Validate first name
    if (!empty ($_POST['first_name']) && !ctype_space ($_POST['first_name'])) {
        $data['first_name'] = htmlspecialchars (trim ($_POST['first_name']));
    }


    // Validate last name
    if (!empty ($_POST['last_name']) && !ctype_space ($_POST['last_name'])) {
        $data['last_name'] = htmlspecialchars (trim ($_POST['last_name']));
    }


    // Validate website
    if (!empty ($_POST['website']) && !ctype_space ($_POST['website'])) {
        $website = $_POST['website'];
        if (preg_match ('/^(https?:\/\/)?[a-z0-9][a-z0-9\.-]+\.[a-z0-9]{2,4}.*$/i', $website, $matches)) {
            $website = (empty($matches[1])) ? 'http://' . $website : $website;
            $data['website'] = htmlspecialchars (trim ($website));
        } else {
            $errors['website'] = 'Invalid website';
        }
    }


    // Validate about me
    if (!empty ($_POST['about_me']) && !ctype_space ($_POST['about_me'])) {
        $data['about_me'] = htmlspecialchars (trim ($_POST['about_me']));
    }



    ### Create user if no errors were found
    if (empty ($errors)) {

        // Create user
        $data['released'] = 1;
        $data['date_created'] = date('Y-m-d H:i:s');
        $data['status'] = 'active';
        User::Create ($data);
        unset ($data);

        // Output message
        $message = 'Member has been added.';
        $message_type = 'success';

    } else {
        $message = 'The following errors were found. Please correct them and try again.';
        $message .= '<br /><br /> - ' . implode ('<br /> - ', $errors);
        $message_type = 'error';
    }

}


// Output Header
include ('header.php');

?>

<div id="members-add">

    <h1>Add New Member</h1>

    <?php if ($message): ?>
    <div class="<?=$message_type?>"><?=$message?></div>
    <?php endif; ?>


    <div class="block">

        <form action="<?=ADMIN?>/members_add.php" method="post">

            <div class="row-shift">An asterisk (*) denotes required field.</div>

            <div class="row">
                <label class="<?=(isset ($errors['email'])) ? 'errors' : ''?>">*E-mail:</label>
                <input name="email" type="text" class="text" value="<?=(isset ($errors, $data['email'])) ? $data['email'] : ''?>" />
            </div>

            <div class="row">
                <label class="<?=(isset ($errors['username'])) ? 'errors' : ''?>">*Username:</label>
                <input name="username" type="text" class="text" value="<?=(isset ($errors, $data['username'])) ? $data['username']:''?>" maxlength="30" />
                <br /><span id="status"></span>
            </div>

            <div class="row-shift">Username can only contain alphanumeric (a-z, 0-9) characters, no spaces or special characters.</div>

            <div class="row">
                <label class="<?=(isset ($errors['password'])) ? 'errors' : ''?>">*Password:</label>
                <input name="password" type="password" class="text mask" value="<?=(isset ($errors, $data['password'])) ? $data['password']:''?>" />
            </div>

            <div class="row">
                <label>First Name:</label>
                <input name="first_name" type="text" class="text" value="<?=(isset ($errors, $data['first_name'])) ? $data['first_name'] : ''?>" />
            </div>

            <div class="row">
                <label>Last Name:</label>
                <input name="last_name" type="text" class="text" value="<?=(isset ($errors, $data['last_name'])) ? $data['last_name'] : ''?>" />
            </div>

            <div class="row">
                <label class="<?=(isset ($errors['website'])) ? 'errors' : ''?>">Website:</label>
                <input name="website" type="text" class="text" value="<?=(isset ($errors, $data['website'])) ? $data['website'] : ''?>" />
            </div>

            <div class="row">
                <label>About Me:</label>
                <textarea name="about_me" rows="5" cols="50" class="text"><?=(isset ($errors, $data['about_me'])) ? $data['about_me']:''?></textarea>
            </div>

            <div class="row-shift">
                <input name="admin" id="make_admin" type="checkbox" value="1" <?=(isset ($errors, $data['admin'])) ? 'checked="checked"':''?> />
                <label for="make_admin">Make member a system admin</label>
            </div>

            <div class="row-shift">
                <input type="hidden" name="submitted" value="TRUE" />
                <input type="submit" class="button" value="Create Member" />
            </div>

        </form>

    </div>

</div>

<?php include ('footer.php'); ?>