<?php

// Include required files
include_once (dirname (dirname (__FILE__)) . '/cc-core/config/admin.bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Category');


// Establish page variables, objects, arrays, etc
Functions::RedirectIf ($logged_in = User::LoginCheck(), HOST . '/login/');
$admin = new User ($logged_in);
Functions::RedirectIf (User::CheckPermissions ('admin_panel', $admin), HOST . '/myaccount/');
$page_title = 'Video Categories';
$categories = array();
$data = array();
$errors = array();
$message = null;





/**************************
Handle create category form
**************************/

if (isset ($_POST['submitted_add'])) {

    try {

        // Validate title
        if (empty ($_POST['cat_name']) || ctype_space ($_POST['cat_name'])) {
            throw new Exception ('Invalid category name. Please try again.');
        }

        $data['slug'] = Functions::CreateSlug (trim ($_POST['cat_name']));
        $data['cat_name'] = htmlspecialchars (trim ($_POST['cat_name']));

        if (Category::Exist (array ('slug' => $data['slug']))) {
            throw new Exception ('Category name or slug already exists. Please note that in the slug special characters are replaced by hyphens.');
        }

        Category::Create ($data);
        $message = $data['cat_name'] . ' was successfully created.';
        $message_type = 'success';
        unset ($data);

    } catch (Exception $e) {
        $errors['cat_name'] = true;
        $message = $e->getMessage();
        $message_type = 'errors';
    }

}





/*******************************
Handle move/delete category form
*******************************/

if (isset ($_POST['submitted_edit'])) {

    // Validate move category
    if (isset ($_POST['move']) && is_numeric ($_POST['move']) && Category::Exist (array ('cat_id' => $_POST['move']))) {
        $data['move'] = $_POST['move'];
    } else {
        $errors['move'] = 'Invalid receiving (move to) category';
    }

    // Validate category
    if (isset ($_POST['category']) && is_numeric ($_POST['category']) && Category::Exist (array ('cat_id' => $_POST['category']))) {
        $data['category'] = $_POST['category'];
    } else {
        $errors['category'] = 'Invalid source category';
    }

    // Verify videos aren't moved to same category
    if (isset ($data['category'], $data['move'])) {
        if ($data['category'] == $data['move']) $errors['category'] = "Can't move videos to the same category";
    }

    // Validate action
    if (!empty ($_POST['action']) && in_array ($_POST['action'], array ('move','delete'))) {
        $data['action'] = $_POST['action'];
    } else {
        $errors['action'] = 'Invalid category action';
    }


    // Move videos if no errors were made
    if (empty ($errors)) {

        // Move videos
        $query = "UPDATE " . DB_PREFIX . "videos SET cat_id = {$data['move']} WHERE cat_id = {$data['category']}";
        $db->Query ($query);

        // Delete category if requested
        if ($data['action'] == 'delete')  {
            $cat = new Category ($data['category']);
            Category::Delete ($data['category']);
            $message = "$cat->cat_name has been deleted.";
        } else {
            $message = 'Videos has been moved.';
        }

        // Output message
        $message_type = 'success';
        unset ($data);
        
    } else {
        $message = 'The following errors were found. Please correct them and try again.';
        $message .= '<br /><br /> - ' . implode ('<br /> - ', $errors);
        $message_type = 'errors';
    }

}



// Retrieve Category names
$query = "SELECT " . DB_PREFIX . "categories.cat_id, cat_name, COUNT(video_id) AS video_count ";
$query .= "FROM " . DB_PREFIX . "categories LEFT JOIN " . DB_PREFIX . "videos ON " . DB_PREFIX . "categories.cat_id = " . DB_PREFIX . "videos.cat_id ";
$query .= "GROUP BY " . DB_PREFIX . "categories.cat_id ORDER BY cat_name asc";
$result = $db->Query ($query);
while ($row = $db->FetchObj ($result)) $categories[] = $row;


// Output Header
include ('header.php');

?>

<div id="videos-categories">

    <?php if ($message): ?>
    <div class="message <?=$message_type?>"><?=$message?></div>
    <?php endif; ?>

    <h1>Add Category</h1>
    <div id="add-category" class="block">
        <form action="<?=ADMIN?>/videos_categories.php" method="post">
            <span class="<?=(isset ($errors['cat_name']))?'error':''?>"><strong>Category Name:</strong></span>
            <input type="text" class="text" name="cat_name" value="<?=(isset($data['cat_name']))?$data['cat_name']:''?>"/>
            <input type="hidden" name="submitted_add" value="TRUE" />
            <input type="submit" class="button" value="Add Category" />
        </form>
    </div>

    
    <h1>Video Categories</h1>

    <?php if (count($categories) > 1): ?>

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
        <div class="block">
            <p><strong><?=$categories[0]->cat_name?></strong> (<?=$categories[0]->video_count?> videos)</p>
        </div>
    <?php endif; ?>


</div>

<?php include ('footer.php'); ?>