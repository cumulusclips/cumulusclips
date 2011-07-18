<?php

### Created on February 28, 2009
### Created by Miguel A. Hurtado
### This script displays the site homepage


// Include required files
include ('../cc-core/config/admin.bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Filesystem');


// Establish page variables, objects, arrays, etc
Plugin::Trigger ('admin.index.start');
//$logged_in = User::LoginCheck(HOST . '/login/');
//$admin = new User ($logged_in);
$page_title = 'Admin Panel';
$content = 'index.tpl';
$first_run = null;


// Execute post install / first run operations
if (isset ($_GET['first_run']) && file_exists (DOC_ROOT . '/install')) {
    Filesystem::Open();
    Filesystem::Delete (DOC_ROOT . '/install');
    Filesystem::Close();
    $first_run = true;
}


// Output Header
include ('header.php');

?>

<h1>Dashboard</h1>

<?php if ($first_run): ?>
<div class="success">
    <p>All done! Your video site is now ready for use. This is your admin panel,
    we went ahead and logged you in so that you can start exploring.</p>

    <p>Your login for the main site and the admin panel are one in the same. To
    enter the admin panel simply login and click on 'Admin'.</p>

    <p>Thank you for choosing CumulusClips to power your video sharing platform.</p>
    <p><a href="" class="button">View My Site</a></p>
</div>
<?php endif; ?>

<div></div>

<?php include ('footer.php'); ?>