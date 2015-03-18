<?php

// Init application
include_once(dirname(dirname(__FILE__)) . '/cc-core/system/admin.bootstrap.php');

// Verify if user is logged in
$userService = new UserService();
$adminUser = $userService->loginCheck();
Functions::redirectIf($adminUser, HOST . '/login/');
Functions::redirectIf($userService->checkPermissions('admin_panel', $adminUser), HOST . '/account/');

// Establish page variables, objects, arrays, etc
$userMapper = new UserMapper();
$user = new User();
$page_title = 'Add New Member';
$data = array();
$errors = array();
$message = null;

// Handle form if submitted
if (isset($_POST['submitted'])) {

    // Validate role
    if (!empty($_POST['role'])) {
        $user->role = trim($_POST['role']);
    } else {
        $errors['role'] = 'Invalid role';
    }

    // Validate email
    if (!empty($_POST['email']) && preg_match('/^[a-z0-9][a-z0-9\._-]+@[a-z0-9][a-z0-9\.-]+\.[a-z0-9]{2,4}$/i', $_POST['email'])) {
        $emailCheck = $userMapper->getUserByCustom(array('email' => $_POST['email']));
        if (!$emailCheck) {
            $user->email = trim($_POST['email']);
        } else {
            $errors['email'] = 'Email is unavailable';
        }
    } else {
        $errors['email'] = 'Invalid email address';
    }

    // Validate Username
    if (!empty($_POST['username'])) {
        $usernameCheck = $userMapper->getUserByCustom(array('username' => $_POST['username']));
        if (!$usernameCheck) {
            $user->username = trim($_POST['username']);
        } else {
            $errors['username'] = 'Username is unavailable';
        }
    } else {
        $errors['username'] = 'Invalid username';
    }

    // Validate password
    if (!empty($_POST['password'])) {
        $user->password = trim($_POST['password']);
    } else {
        $errors['password'] = 'Invalid password';
    }

    // Validate first name
    if (!empty($_POST['first_name'])) {
        $user->firstName = trim($_POST['first_name']);
    }

    // Validate last name
    if (!empty($_POST['last_name'])) {
        $user->lastName = trim($_POST['last_name']);
    }

    // Validate website
    if (!empty($_POST['website'])) {
        $website = $_POST['website'];
        if (preg_match('/^(https?:\/\/)?[a-z0-9][a-z0-9\.-]+\.[a-z0-9]{2,4}.*$/i', $website, $matches)) {
            $website = (empty($matches[1]) ? 'http://' : '') . $website;
            $user->website = trim($website);
        } else {
            $errors['website'] = 'Invalid website';
        }
    }

    // Validate about me
    if (!empty($_POST['about_me'])) {
        $user->aboutMe = trim($_POST['about_me']);
    }

    // Create user if no errors were found
    if (empty($errors)) {

        // Create user
        $newUser = $userService->create($user);
        $userService->approve($newUser, 'create');
        unset($user);

        // Output message
        $message = 'Member has been added.';
        $message_type = 'success';

    } else {
        $message = 'The following errors were found. Please correct them and try again.';
        $message .= '<br><br> - ' . implode('<br> - ', $errors);
        $message_type = 'errors';
    }
}

// Output Header
$pageName = 'members-add';
include('header.php');

?>

<div id="members-add">

    <h1>Add New Member</h1>

    <?php if ($message): ?>
    <div class="message <?=$message_type?>"><?=$message?></div>
    <?php endif; ?>


    <div class="block">

        <form action="<?=ADMIN?>/members_add.php" method="post">

            <div class="row-shift">An asterisk (*) denotes required field.</div>

            <div class="row<?=(isset ($errors['status'])) ? ' error' : ''?>">
                <label>*Role:</label>
                <select name="role" class="dropdown">
                <?php foreach ((array) $config->roles as $key => $value): ?>
                    <option value="<?=$key?>" <?=(isset ($user->role) && $user->role == $key)?'selected="selected"':''?>><?=$value->name?></option>
                <?php endforeach; ?>
                </select>
            </div>

            <div class="row">
                <label class="<?=(isset ($errors['email'])) ? 'error' : ''?>">*E-mail:</label>
                <input name="email" type="text" class="text" value="<?=isset($user->email) ? $user->email : ''?>" />
            </div>

            <div class="row">
                <label class="<?=(isset ($errors['username'])) ? 'error' : ''?>">*Username:</label>
                <input name="username" type="text" class="text" value="<?=isset($user->username) ? $user->username:''?>" maxlength="30" />
                <br /><span id="status"></span>
            </div>

            <div class="row-shift">Username can only contain alphanumeric (a-z, 0-9) characters, no spaces or special characters.</div>

            <div class="row">
                <label class="<?=(isset ($errors['password'])) ? 'error' : ''?>">*Password:</label>
                <input name="password" type="password" class="text mask" value="<?=isset($user->password) ? htmlspecialchars($user->password) : ''?>" />
            </div>

            <div class="row">
                <label>First Name:</label>
                <input name="first_name" type="text" class="text" value="<?=isset($user->firstName) ? htmlspecialchars($user->firstName) : ''?>" />
            </div>

            <div class="row">
                <label>Last Name:</label>
                <input name="last_name" type="text" class="text" value="<?=isset($user->lastName) ? htmlspecialchars($user->lastName) : ''?>" />
            </div>

            <div class="row">
                <label class="<?=(isset ($errors['website'])) ? 'error' : ''?>">Website:</label>
                <input name="website" type="text" class="text" value="<?=isset($user->website) ? htmlspecialchars($user->website) : ''?>" />
            </div>

            <div class="row">
                <label>About Me:</label>
                <textarea name="about_me" rows="5" cols="50" class="text"><?=isset($user->aboutMe) ? htmlspecialchars($user->aboutMe) : ''?></textarea>
            </div>

            <div class="row-shift">
                <input type="hidden" name="submitted" value="TRUE" />
                <input type="submit" class="button" value="Create Member" />
            </div>

        </form>

    </div>

</div>

<?php include ('footer.php'); ?>