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
$query = "SELECT " . DB_PREFIX . "categories.cat_id, cat_name, COUNT(video_id) AS video_count ";
$query .= "FROM " . DB_PREFIX . "categories LEFT JOIN " . DB_PREFIX . "videos ON " . DB_PREFIX . "categories.cat_id = " . DB_PREFIX . "videos.cat_id ";
$query .= "GROUP BY " . DB_PREFIX . "categories.cat_id ORDER BY cat_name asc";
$result = $db->Query ($query);
while ($row = $db->FetchObj ($result)) {
    $categories[$row->cat_name] = $row;
}





/***********************
Handle form if submitted
***********************/

if (isset ($_POST['submitted_add'])) {

    try {

        // Validate title
        if (empty ($_POST['cat_name']) || ctype_space ($_POST['cat_name'])) {
            throw new Exception ('Invalid category name. Please try again.');
        }

        $data['cat_name'] = htmlspecialchars (trim ($_POST['cat_name']));

        foreach ($categories as $category) {
            $category_slug = Functions::CreateSlug ($category->cat_name);
            $new_category_slug = Functions::CreateSlug ($data['cat_name']);
            if ($category_slug == $new_category_slug) {
                throw new Exception ('Category name or slug already exists. Please note that in the slug special characters are replaced by hyphens.');
            }
        }

        $id = Category::Create ($data);
        $categories[$data['cat_name']] = (object) array ('cat_id' => $id, 'cat_name' => $data['cat_name'], 'video_count' => 0);
        ksort ($categories);
        $message = $data['cat_name'] . ' was successfully created.';
        $message_type = 'success';
        unset ($data);

    } catch (Exception $e) {
        $errors['cat_name'] = true;
        $message = $e->getMessage();
        $message_type = 'error';
    }

}





if (isset ($_POST['submitted_edit'])) {

    // Validate category
    if (isset ($_POST['category']) && is_numeric ($_POST['category']) && Category::Exists (array ('cat_id' => $_POST['category']))) {
        $data['category'] = $_POST['category'];
    } else {
        $errors['category'] = 'Invalid category';
    }


    // Validate action
    if (!empty ($_POST['action']) && in_array ($_POST['action'], array ('move','delete'))) {
        $data['action'] = $_POST['action'];
    } else {
        $errors['action'] = 'Invalid action';
    }


    // Validate move category
    if (isset ($_POST['move']) && is_numeric ($_POST['move']) && Category::Exists (array ('cat_id' => $_POST['move']))) {
        $data['move'] = $_POST['move'];
    } else {
        $errors['move'] = 'Invalid move category';
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

    <h1>Add Category</h1>
    <div id="add-category" class="block">
        <form action="<?=ADMIN?>/videos_categories.php" method="post">
            <span class="<?=(isset ($errors['cat_name']))?'errors':''?>"><strong>Category Name:</strong></span>
            <input type="text" class="text" name="cat_name" value="<?=(isset($data['cat_name']))?$data['cat_name']:''?>"/>
            <input type="hidden" name="submitted_add" value="TRUE" />
            <input type="submit" class="button" value="Add Category" />
        </form>
    </div>

    
    <h1>Video Categories</h1>

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
                        <input type="hidden" name="category" value="<?=$category_obj->cat_id?>" />
                        <input type="hidden" name="action" value="" />
                        <input type="hidden" name="submitted_edit" value="TRUE" />
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