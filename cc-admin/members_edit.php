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
$page_title = 'Edit Member';
$pageName = 'members-edit';
$data = array();
$errors = array();
$message = null;
$allowedRoles = $config->roles;

// Remove admin from allowed roles if user is not an admin
if ($adminUser->role != 'admin') {
    unset($allowedRoles->admin, $allowedRoles->mod);
}

// Build return to list link
if (!empty($_SESSION['list_page'])) {
    $list_page = $_SESSION['list_page'];
} else {
    $list_page = ADMIN . '/member.php';
}

// Verify a member was provided
if (isset($_GET['id']) && is_numeric($_GET['id']) && $_GET['id'] > 0) {

    // Retrieve member information
    $user = $userMapper->getUserById($_GET['id']);
    if ($user && ($user->role == 'admin' && $adminUser->role != 'admin')) {
        header('Location: ' . ADMIN . '/members.php?denied');
        exit();
    } else if (!$user) {
        header('Location: ' . ADMIN . '/members.php');
        exit();
    }
} else {
    header('Location: ' . ADMIN . '/members.php');
    exit();
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
        // Validate status
        if (!empty($_POST['status'])) {
            $user->status = trim($_POST['status']);
        } else {
            $errors['status'] = 'Invalid status';
        }

        // Validate role
        if (!empty($_POST['role']) && array_key_exists($_POST['role'], $allowedRoles)) {
            $user->role = trim($_POST['role']);
        } else {
            $errors['role'] = 'Invalid role';
        }

        // Validate Email
        if (!empty($_POST['email']) && preg_match('/^[a-z0-9][a-z0-9_\.\-]+@[a-z0-9][a-z0-9\.\-]+\.[a-z0-9]{2,4}$/i', $_POST['email'])) {
            $duplicateEmailUser = $userMapper->getUserByCustom(array('email' => $_POST['email']));
            if (!$duplicateEmailUser || $duplicateEmailUser->userId == $user->userId) {
                $user->email = $_POST['email'];
            } else {
                $errors['email'] = 'Email is unavailable';
            }

        } else {
            $errors['email'] = 'Invalid email address';
        }

        // Validate password
        if (!empty($_POST['password'])) {
            $password = trim($_POST['password']);
        }

        // Validate First Name
        if (!empty($user->firstName) && $_POST['first_name'] == '') {
            $user->firstName = '';
        } elseif (!empty($_POST['first_name'])) {
            $user->firstName = $_POST['first_name'];
        }

        // Validate Last Name
        if (!empty($user->lastName) && $_POST['last_name'] == '') {
            $user->lastName = '';
        } elseif (!empty($_POST['last_name'])) {
            $user->lastName = $_POST['last_name'];
        }

        // Validate website
        if (!empty($user->website) && empty($_POST['website'])) {
            $user->website = '';
        } else if (!empty ($_POST['website'])) {
            $website = $_POST['website'];
            if (preg_match('/^(https?:\/\/)?[a-z0-9][a-z0-9\.-]+\.[a-z0-9]{2,4}.*$/i', $website, $matches)) {
                $website = (empty($matches[1])) ? 'http://' . $website : $website;
                $user->website = trim($website);
            } else {
                $errors['website'] = 'Invalid website';
            }
        }

        // Validate About Me
        if (!empty($user->aboutMe) && empty($_POST['about_me'])) {
            $user->aboutMe = '';
        } elseif (!empty($_POST['about_me'])) {
            $user->aboutMe = $_POST['about_me'];
        }

        // Update User if no errors were found
        if (empty($errors)) {

            // Perform addional actions based on status change
            if ($user->status != $user->status) {

                switch ($user->status) {

                    // Handle "Approve" action
                    case 'active':
                        $userService->updateContentStatus($user, 'active');
                        $userService->approve($user, 'approve');
                        break;

                    // Handle "Ban" action
                    case 'banned':
                        $userService->updateContentStatus($user, 'banned');
                        $flagService = new FlagService();
                        $flagService->flagDecision($user, true);
                        break;

                    // Handle "Pending" or "New" action
                    case 'new':
                    case 'pending':
                        $userService->updateContentStatus($user);
                        break;
                }
            }

            if (isset($password)) $user->password = md5($user->password);
            $message = 'Member has been updated.';
            $message_type = 'alert-success';
            $userMapper->save($user);
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
include ('header.php');

?>

<h1>Update Member</h1>

<?php if ($message): ?>
<div class="alert <?=$message_type?>"><?=$message?></div>
<?php endif; ?>

<p><a href="<?=$list_page?>">Return to previous screen</a></p>
<p>An asterisk (*) denotes required field.</p>

<form action="<?=ADMIN?>/members_edit.php?id=<?=$user->userId?>" method="post">

    <div class="form-group <?=(isset($errors['status'])) ? 'has-error' : ''?>">
        <label class="control-label">*Status:</label>
        <select name="status" class="form-control">
            <option value="active"<?=(isset($data['status']) && $data['status'] == 'active') || (!isset($data['status']) && $user->status == 'active')?' selected="selected"':''?>>Active</option>
            <option value="new"<?=(isset($data['status']) && $data['status'] == 'new') || (!isset($data['status']) && $user->status == 'new')?' selected="selected"':''?>>New</option>
            <option value="pending"<?=(isset($data['status']) && $data['status'] == 'pending') || (!isset($data['status']) && $user->status == 'pending')?' selected="selected"':''?>>Pending</option>
            <option value="banned"<?=(isset($data['status']) && $data['status'] == 'banned') || (!isset($data['status']) && $user->status == 'banned')?' selected="selected"':''?>>Banned</option>
        </select>
    </div>

    <div class="form-group <?=(isset($errors['status'])) ? 'has-error' : ''?>">
        <label class="control-label">*Role:</label>
        <select name="role" class="form-control">
        <?php foreach ((array) $allowedRoles as $key => $value): ?>
            <option value="<?=$key?>" <?=($user->role == $key) ? 'selected="selected"' : ''?>><?=$value->name?></option>
        <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group <?=(isset($errors['email'])) ? 'has-error' : ''?>">
        <label class="control-label">*Email:</label>
        <input class="form-control" type="text" name="email" value="<?=$user->email?>" />
    </div>

    <div class="form-group">
        <label>Username:</label>
        <p><a href="<?=HOST?>/members/<?=$user->username?>/"><?=$user->username?></a></p>
    </div>

    <div class="form-group">
        <label class="<?=(isset($errors['password'])) ? 'error' : ''?>">Password:</label>
        <input name="password" type="password" class="form-control mask" value="" />
    </div>

    <div class="form-group <?=(isset($errors['first_name'])) ? 'has-error' : ''?>">
        <label class="control-label">First Name:</label>
        <input class="form-control" type="text" name="first_name" value="<?=htmlspecialchars($user->firstName)?>" />
    </div>

    <div class="form-group <?=(isset($errors['last_name'])) ? 'has-error' : ''?>">
        <label class="control-label">Last Name:</label>
        <input class="form-control" type="text" name="last_name" value="<?=htmlspecialchars($user->lastName)?>" />
    </div>

    <div class="form-group <?=(isset($errors['website'])) ? 'has-error' : ''?>">
        <label class="control-label">Website:</label>
        <input class="form-control" type="text" name="website" value="<?=htmlspecialchars($user->website)?>" />
    </div>

    <div class="form-group <?=(isset($errors['about_me'])) ? 'has-error' : ''?>">
        <label class="control-label">About Me:</label>
        <textarea rows="7" cols="50" class="form-control" name="about_me"><?=htmlspecialchars($user->aboutMe)?></textarea>
    </div>

    <input type="hidden" value="yes" name="submitted" />
    <input type="hidden" name="nonce" value="<?=$formNonce?>" />
    <input type="submit" class="button" value="Update Member" />

</form>

<?php include ('footer.php'); ?>