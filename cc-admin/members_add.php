<?php

// Init application
include_once(dirname(dirname(__FILE__)) . '/cc-core/system/admin.bootstrap.php');

// Verify if user is logged in
$authService->enforceTimeout(true);

// Verify user can access admin panel
$userService = new \UserService();
Functions::redirectIf($userService->checkPermissions('admin_panel', $adminUser), HOST . '/account/');

// Establish page variables, objects, arrays, etc
$userMapper = new UserMapper();
$user = new User();
$page_title = 'Add New Member';
$data = array();
$errors = array();
$message = null;
$allowedRoles = $config->roles;

// Remove admin from allowed roles if user is not an admin
if ($adminUser->role != 'admin') {
    unset($allowedRoles->admin, $allowedRoles->mod);
}

// Handle form if submitted
if (isset($_POST['submitted'])) {

    // Validate form nonce token and submission speed
    if (
        !empty($_POST['nonce'])
        && !empty($_SESSION['formNonce'])
        && !empty($_SESSION['formTime'])
        && $_POST['nonce'] == $_SESSION['formNonce']
        && time() - $_SESSION['formTime'] >= 2
    ) {
        // Validate role
        if (!empty($_POST['role']) && array_key_exists($_POST['role'], $allowedRoles)) {
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
         if (!empty($_POST['username']) && preg_match('/^[a-z0-9]+$/i', $_POST['username'])) {
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
            $message_type = 'alert-success';

        } else {
            $message = 'The following errors were found. Please correct them and try again.';
            $message .= '<br><br> - ' . implode('<br> - ', $errors);
            $message_type = 'alert-danger';
        }

    } else {
        $message = 'Expired or invalid session';
        $message_type = 'alert-danger';
    }
}

// Generate new form nonce
$formNonce = md5(uniqid(rand(), true));
$_SESSION['formNonce'] = $formNonce;
$_SESSION['formTime'] = time();

// Output Header
$pageName = 'members-add';
include('header.php');

?>

<h1>Add New Member</h1>

<?php if ($message): ?>
<div class="alert <?=$message_type?>"><?=$message?></div>
<?php endif; ?>

<p>An asterisk (*) denotes required field.</p>

<form action="<?=ADMIN?>/members_add.php" method="post">

    <div class="form-group <?=(isset ($errors['status'])) ? 'has-error' : ''?>">
        <label class="control-label">*Role:</label>
        <select name="role" class="form-control">
        <?php foreach ((array) $allowedRoles as $key => $value): ?>
            <option value="<?=$key?>" <?=(isset ($user->role) && $user->role == $key)?'selected="selected"':''?>><?=$value->name?></option>
        <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group <?=(isset ($errors['email'])) ? 'has-error' : ''?>">
        <label class="control-label">*E-mail:</label>
        <input name="email" type="text" class="form-control" value="<?=isset($user->email) ? $user->email : ''?>" />
    </div>

    <div class="form-group <?=(isset ($errors['username'])) ? 'has-error' : ''?>">
        <label class="control-label">*Username:</label>
        <input name="username" type="text" class="form-control" value="<?=isset($user->username) ? $user->username:''?>" maxlength="30" />
        <br /><span id="status"></span>
        <p>Username can only contain alphanumeric (a-z, 0-9) characters, no spaces or special characters.</p>
    </div>


    <div class="form-group <?=(isset ($errors['password'])) ? 'has-error' : ''?>">
        <label class="control-label">*Password:</label>
        <input name="password" type="password" class="form-control mask" value="<?=isset($user->password) ? htmlspecialchars($user->password) : ''?>" />
    </div>

    <div class="form-group">
        <label>First Name:</label>
        <input name="first_name" type="text" class="form-control" value="<?=isset($user->firstName) ? htmlspecialchars($user->firstName) : ''?>" />
    </div>

    <div class="form-group">
        <label>Last Name:</label>
        <input name="last_name" type="text" class="form-control" value="<?=isset($user->lastName) ? htmlspecialchars($user->lastName) : ''?>" />
    </div>

    <div class="form-group">
        <label class="<?=(isset ($errors['website'])) ? 'has-error' : ''?>">Website:</label>
        <input name="website" type="text" class="form-control" value="<?=isset($user->website) ? htmlspecialchars($user->website) : ''?>" />
    </div>

    <div class="form-group">
        <label>About Me:</label>
        <textarea name="about_me" rows="5" cols="50" class="form-control"><?=isset($user->aboutMe) ? htmlspecialchars($user->aboutMe) : ''?></textarea>
    </div>

    <input type="hidden" value="yes" name="submitted" />
    <input type="hidden" name="nonce" value="<?=$formNonce?>" />
    <input type="submit" class="button" value="Create Member" />

</form>

<?php include ('footer.php'); ?>