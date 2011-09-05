<?php

// Include required files
include_once (dirname (dirname (__FILE__)) . '/cc-core/config/admin.bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Flag');


// Establish page variables, objects, arrays, etc
Plugin::Trigger ('admin.member_edit.start');
Functions::RedirectIf ($logged_in = User::LoginCheck(), HOST . '/login/');
$admin = new User ($logged_in);
Functions::RedirectIf (User::CheckPermissions ('admin_panel', $admin), HOST . '/myaccount/');
$page_title = 'Edit Member';
$data = array();
$errors = array();
$message = null;



// Build return to list link
if (!empty ($_SESSION['list_page'])) {
    $list_page = $_SESSION['list_page'];
} else {
    $list_page = ADMIN . '/member.php';
}



### Verify a member was provided
if (isset ($_GET['id']) && is_numeric ($_GET['id']) && $_GET['id'] != 0) {

    ### Retrieve member information
    $user = new User ($_GET['id']);
    if (!$user->found) {
        header ('Location: ' . ADMIN . '/members.php');
        exit();
    }

} else {
    header ('Location: ' . ADMIN . '/members.php');
    exit();
}





/***********************
Handle form if submitted
***********************/

if (isset ($_POST['submitted'])) {

    // Validate status
    if (!empty ($_POST['status']) && !ctype_space ($_POST['status'])) {
        $data['status'] = htmlspecialchars (trim ($_POST['status']));
    } else {
        $errors['status'] = 'Invalid status';
    }


    // Validate role
    if (!empty ($_POST['role']) && !ctype_space ($_POST['role'])) {
        $data['role'] = htmlspecialchars (trim ($_POST['role']));
    } else {
        $errors['role'] = 'Invalid role';
    }


    // Validate Email
    if (!empty ($_POST['email']) && !ctype_space ($_POST['email']) && preg_match ('/^[a-z0-9][a-z0-9_\.\-]+@[a-z0-9][a-z0-9\.\-]+\.[a-z0-9]{2,4}$/i',$_POST['email'])) {
        $email = array ('email' => $_POST['email']);
        $id = User::Exist ($email);
        if (!$id || $id == $user->user_id) {
            $data['email'] = $_POST['email'];
        } else {
            $errors['email'] = 'Email is unavailable';
        }

    } else {
        $errors['email'] = 'Invalid email address';
    }


    // Validate password
    if (!empty ($_POST['password']) && !ctype_space ($_POST['password'])) {
        $data['password'] = trim ($_POST['password']);
    }


    // Validate First Name
    if (!empty ($user->first_name) && $_POST['first_name'] == '') {
        $data['first_name'] = '';
    } elseif (!empty ($_POST['first_name']) && !ctype_space ($_POST['first_name'])) {
        $data['first_name'] = htmlspecialchars ($_POST['first_name']);
    }


    // Validate Last Name
    if (!empty ($user->last_name) && $_POST['last_name'] == '') {
        $data['last_name'] = '';
    } elseif (!empty ($_POST['last_name']) && !ctype_space ($_POST['last_name'])) {
        $data['last_name'] = htmlspecialchars ($_POST['last_name']);
    }


    // Validate website
    if (!empty ($user->website) && empty ($_POST['website'])) {
        $data['website'] = '';
    } else if (!empty ($_POST['website']) && !ctype_space ($_POST['website'])) {
        $website = $_POST['website'];
        if (preg_match ('/^(https?:\/\/)?[a-z0-9][a-z0-9\.-]+\.[a-z0-9]{2,4}.*$/i', $website, $matches)) {
            $website = (empty($matches[1])) ? 'http://' . $website : $website;
            $data['website'] = htmlspecialchars (trim ($website));
        } else {
            $errors['website'] = 'Invalid website';
        }
    }


    // Validate About Me
    if (!empty ($user->about_me) && empty ($_POST['about_me'])) {
        $data['about_me'] = '';
    } elseif (!empty ($_POST['about_me']) && !ctype_space ($_POST['about_me'])) {
        $data['about_me'] = htmlspecialchars ($_POST['about_me']);
    }



    ### Update User if no errors were found
    if (empty ($errors)) {

        // Perform addional actions based on status change
        if ($data['status'] != $user->status) {

            switch ($data['status']) {

                // Handle "Approve" action
                case 'active':
                    $user->UpdateContentStatus ('active');
                    $user->Approve ('approve');
                    break;


                // Handle "Ban" action
                case 'banned':
                    $user->UpdateContentStatus ('banned');
                    Flag::FlagDecision ($user->user_id, 'user', true);
                    break;


                // Handle "Pending" or "New" action
                case 'new':
                case 'pending':
                    $user->UpdateContentStatus ($data['status']);
                    break;

            }

        }

        $message = 'Member has been updated.';
        $message_type = 'success';
        $user->Update ($data);
        Plugin::Trigger ('admin.member_edit.update_member');
    } else {
        $message = 'The following errors were found. Please correct them and try again.';
        $message .= '<br /><br /> - ' . implode ('<br /> - ', $errors);
        $message_type = 'error';
    }

}


// Output Header
include ('header.php');

?>

<div id="members-edit">

    <h1>Update Member</h1>

    <?php if ($message): ?>
    <div class="<?=$message_type?>"><?=$message?></div>
    <?php endif; ?>


    <div class="block">

        <p><a href="<?=$list_page?>">Return to previous screen</a></p>

        <form action="<?=ADMIN?>/members_edit.php?id=<?=$user->user_id?>" method="post">

            <div class="row-shift">An asterisk (*) denotes required field.</div>

            <div class="row<?=(isset ($errors['status'])) ? ' errors' : ''?>">
                <label>*Status:</label>
                <select name="status" class="dropdown">
                    <option value="active"<?=(isset ($data['status']) && $data['status'] == 'active') || (!isset ($data['status']) && $user->status == 'active')?' selected="selected"':''?>>Active</option>
                    <option value="new"<?=(isset ($data['status']) && $data['status'] == 'new') || (!isset ($data['status']) && $user->status == 'new')?' selected="selected"':''?>>New</option>
                    <option value="pending"<?=(isset ($data['status']) && $data['status'] == 'pending') || (!isset ($data['status']) && $user->status == 'pending')?' selected="selected"':''?>>Pending</option>
                    <option value="banned"<?=(isset ($data['status']) && $data['status'] == 'banned') || (!isset ($data['status']) && $user->status == 'banned')?' selected="selected"':''?>>Banned</option>
                </select>
            </div>

            <div class="row<?=(isset ($errors['status'])) ? ' errors' : ''?>">
                <label>*Role:</label>
                <select name="role" class="dropdown">
                <?php foreach ($config->roles as $key => $value): ?>
                    <option value="<?=$key?>" <?=(isset ($data['role']) && $data['role'] == $key) || (!isset ($data['role']) && $user->role == $key)?'selected="selected"':''?>><?=$value['name']?></option>
                <?php endforeach; ?>
                </select>
            </div>

            <div class="row<?=(isset ($errors['email'])) ? ' errors' : ''?>">
                <label>*Email:</label>
                <input class="text" type="text" name="email" value="<?=(isset ($errors, $data['email'])) ? $data['email'] : $user->email?>" />
            </div>

            <div class="row">
                <label>Username:</label>
                <p><a href="<?=HOST?>/members/<?=$user->username?>/"><?=$user->username?></a></p>
            </div>

            <div class="row">
                <label class="<?=(isset ($errors['password'])) ? 'errors' : ''?>">Password:</label>
                <input name="password" type="password" class="text mask" value="<?=(!empty ($errors) && !empty ($data['password'])) ? htmlspecialchars ($data['password']):''?>" />
            </div>

            <div class="row<?=(isset ($errors['first_name'])) ? ' errors' : ''?>">
                <label>First Name:</label>
                <input class="text" type="text" name="first_name" value="<?=(isset ($data['first_name'])) ? $data['first_name'] : $user->first_name?>" />
            </div>

            <div class="row<?=(isset ($errors['last_name'])) ? ' errors' : ''?>">
                <label>Last Name:</label>
                <input class="text" type="text" name="last_name" value="<?=(isset ($data['last_name'])) ? $data['last_name'] : $user->last_name?>" />
            </div>

            <div class="row<?=(isset ($errors['website'])) ? ' errors' : ''?>">
                <label>Website:</label>
                <input class="text" type="text" name="website" value="<?=(isset ($data['website'])) ? $data['website'] : $user->website?>" />
            </div>

            <div class="row<?=(isset ($errors['about_me'])) ? ' errors' : ''?>">
                <label>About Me:</label>
                <textarea rows="7" cols="50" class="text" name="about_me"><?=(isset ($data['about_me'])) ? $data['about_me'] : $user->about_me?></textarea>
            </div>
            
            <div class="row-shift">
                <input type="hidden" value="yes" name="submitted" />
                <input type="submit" class="button" value="Update Member" />
            </div>

        </form>

    </div>

</div>

<?php include ('footer.php'); ?>