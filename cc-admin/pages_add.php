<?php

### Created on February 28, 2009
### Created by Miguel A. Hurtado
### This script displays the site homepage


// Include required files
include ('../cc-core/config/admin.bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Page');


// Establish page variables, objects, arrays, etc
Plugin::Trigger ('admin.videos.start');
//$logged_in = User::LoginCheck(HOST . '/login/');
//$admin = new User ($logged_in);
$url = ADMIN . '/pages.php';
$query_string = array();
$message = null;
$sub_header = null;








// Output Header
include ('header.php');

?>

<div id="pages-add">

    <h1><?=$header?></h1>
    <?php if ($sub_header): ?>
    <h3><?=$sub_header?></h3>
    <?php endif; ?>


    <?php if ($message): ?>
    <div class="<?=$message_type?>"><?=$message?></div>
    <?php endif; ?>


    <div id="browse-header">

        <div class="jump">
            Jump To:
            <select name="status" data-jump="<?=ADMIN?>/pages.php">
                <option <?=(isset($status) && $status == 'published') ? 'selected="selected"' : ''?>value="published">Published</option>
                <option <?=(isset($status) && $status == 'draft') ? 'selected="selected"' : ''?>value="draft">Draft</option>
            </select>
        </div>

        <a class="button add">Add New</a>

        <div class="search">
            <form method="POST" action="<?=ADMIN?>/pages.php?status=<?=$status?>">
                <input type="hidden" name="search_submitted" value="true" />
                <input type="text" name="search" value="" />&nbsp;
                <input type="submit" name="submit" class="button" value="Search" />
            </form>
        </div>

    </div>



    <div class="block">
        <input type="text" name="title" />
        <textarea></textarea>
    </div>

</div>

<?php include ('footer.php'); ?>