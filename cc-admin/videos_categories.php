<?php

### Created on February 28, 2009
### Created by Miguel A. Hurtado
### This script displays the site homepage


// Include required files
include ('../cc-core/config/admin.bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Category');


// Establish page variables, objects, arrays, etc
Plugin::Trigger ('admin.video_edit.start');
//$logged_in = User::LoginCheck(HOST . '/login/');
//$admin = new User ($logged_in);
$page_title = 'Video Categories';
$categories = array();
$data = array();
$errors = array();
$message = null;


// Retrieve Category names
$query = "SELECT " . DB_PREFIX . "videos.cat_id, cat_name, COUNT(video_id) AS video_count ";
$query .= "FROM " . DB_PREFIX . "videos INNER JOIN " . DB_PREFIX . "categories ON " . DB_PREFIX . "videos.cat_id = " . DB_PREFIX . "categories.cat_id ";
$query .= "GROUP BY " . DB_PREFIX . "videos.cat_id ORDER BY cat_name asc";
$result = $db->Query ($query);
while ($row = $db->FetchObj ($result)) {
    $categories[] = $row;
}





/***********************
Handle form if submitted
***********************/

if (isset ($_POST['submitted_add'])) {


    // Validate title
    if (!empty ($_POST['title']) && !ctype_space ($_POST['title'])) {
        $data['title'] = htmlspecialchars (trim ($_POST['title']));
    } else {
        $errors['title'] = 'Invalid title';
    }

}

if (false) {


    // Validate title
    if (!empty ($_POST['title']) && !ctype_space ($_POST['title'])) {
        $data['title'] = htmlspecialchars (trim ($_POST['title']));
    } else {
        $errors['title'] = 'Invalid title';
    }


    // Validate description
    if (!empty ($_POST['description']) && !ctype_space ($_POST['description'])) {
        $data['description'] = htmlspecialchars (trim ($_POST['description']));
    } else {
        $errors['description'] = 'Invalid description';
    }


    // Validate tags
    if (!empty ($_POST['tags']) && !ctype_space ($_POST['tags'])) {
        $data['tags'] = htmlspecialchars (trim ($_POST['tags']));
    } else {
        $errors['tags'] = 'Invalid tags';
    }


    // Validate cat_id
    if (!empty ($_POST['cat_id']) && is_numeric ($_POST['cat_id'])) {
        $data['cat_id'] = $_POST['cat_id'];
    } else {
        $errors['cat_id'] = 'Invalid category';
    }



    // Update video if no errors were made
    if (empty ($errors)) {

        // Create record
//        $data['user_id'] = $user->user_id;
        $data['user_id'] = 1;
        $data['filename'] = basename ($data['upload']['temp'], '.' . Functions::GetExtension ($data['upload']['temp']));
        unset ($data['upload']);
        $data['status'] = 'pending conversion';
        $id = Video::Create ($data);

        // Begin encoding
        $cmd_output = $config->debug_conversion ? CONVERSION_LOG : '/dev/null';
        $converter_cmd = 'nohup ' . $config->php . ' ' . DOC_ROOT . '/cc-core/system/encode.php --video="' . $id . '" >> ' .  $cmd_output . ' &';
        exec ($converter_cmd);

        // Output message
        $message = 'Video has been created.';
        $message_type = 'success';
        unset ($data);
        
    } else {
        $message = 'The following errors were found. Please correct them and try again.';
        $message .= '<br /><br /> - ' . implode ('<br /> - ', $errors);
        $message_type = 'error';
    }

}


// Output Header
include ('header.php');

?>

<div id="videos-categories">

    <?php if ($message): ?>
    <div class="<?=$message_type?>"><?=$message?></div>
    <?php endif; ?>

    <h2>Add Category</h2>
    <div id="add-category" class="block">
        <form action="<?=ADMIN?>/videos_categories.php" metho="post">
            <strong>Category Name:</strong>
            <input type="text" class="text" name="name" />
            <input type="hidden" name="submitted_add" value="TRUE" />
            <input type="submit" class="button" value="Add Category" />
        </form>
    </div>

    
    <h2>Video Categories</h2>

    <?php if (count($categories) > 0): ?>

        <?php foreach ($categories as $category_obj): ?>

            <div class="block">
                <p><strong><?=$category_obj->cat_name?></strong> (<?=$category_obj->video_count?> videos)</p>
                <p><a href="" class="category-action" data-action="move">Move Videos</a> &nbsp;&nbsp;|&nbsp;&nbsp; <a href="" class="delete category-action" data-action="delete">Delete</a></p>

                <div class="hide">
                    <form action="<?=ADMIN?>/videos_categories.php" method="post">
                        <strong>Move videos to: </strong>
                        <select name="move" class="dropdown">
                        <?php $list = $categories; ?>
                        <?php foreach ($list as $value): ?>
                            <?php if ($category_obj->cat_id == $value->cat_id) continue; ?>
                            <option value="<?=$value->cat_id?>"><?=$value->cat_name?></option>
                        <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="action" value="" />
                        <input type="hidden" name="submitted_edit" value="TRUE" />
                        <input type="hidden" name="category" value="<?=$category_obj->cat_id?>" />
                        <input type="submit" class="button move-videos" value="Move Videos" />
                        <input type="submit" class="button delete-category" value="Delete Category" />
                    </form>
                </div>

            </div>

        <?php endforeach; ?>

    <?php else: ?>
        <div class="block"><strong>No Categories added yet.</strong></div>
    <?php endif; ?>


</div>

<?php include ('footer.php'); ?>