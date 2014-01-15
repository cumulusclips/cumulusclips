<?php

// Init application
include_once(dirname(dirname(__FILE__)) . '/cc-core/config/admin.bootstrap.php');

// Verify if user is logged in
$userService = new UserService();
$adminUser = $userService->loginCheck();
Functions::RedirectIf($adminUser, HOST . '/login/');
Functions::RedirectIf($userService->checkPermissions('admin_panel', $adminUser), HOST . '/myaccount/');

// Establish page variables, objects, arrays, etc
$page_title = 'Video Categories';
$categories = array();
$data = array();
$errors = array();
$message = null;
$categoryMapper = new CategoryMapper();




/**************************
Handle create category form
**************************/

if (isset ($_POST['submitted_add'])) {

    try {

        // Validate title
        if (empty ($_POST['cat_name']) || ctype_space ($_POST['cat_name'])) {
            throw new Exception ('Invalid category name. Please try again.');
        }

        $category = new Category();
        $category->slug = Functions::CreateSlug (trim ($_POST['cat_name']));
        $category->name = trim($_POST['cat_name']);
        
        if ($categoryMapper->getCategoryBySlug($data['slug'])) {
            throw new Exception ('Category name or slug already exists. Please note that in the slug special characters are replaced by hyphens.');
        }

        $categoryMapper->save($category);
        $message = $category->name . ' was successfully created.';
        $message_type = 'success';
        unset ($category);

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
    if (isset ($_POST['move']) && is_numeric ($_POST['move']) && $categoryMapper->getCategoryById($_POST['move'])) {
        $data['move'] = $_POST['move'];
    } else {
        $errors['move'] = 'Invalid receiving (move to) category';
    }

    // Validate category
    if (isset ($_POST['category']) && is_numeric ($_POST['category']) && $categoryMapper->getCategoryById($_POST['category'])) {
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
        $query = 'UPDATE ' . DB_PREFIX . 'videos SET category_id = :categoryId WHERE category_id = :oldCategoryId';
        $db->query($query, array(':categoryId' => $data['move'], ':oldCategoryId' => $data['category']));

        // Delete category if requested
        if ($data['action'] == 'delete')  {
            $deletedCategory = $categoryMapper->getCategoryById($data['category']);
            $categoryMapper->delete($deletedCategory->categoryId);
            $message = "$deletedCategory->name has been deleted.";
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
$query = "SELECT " . DB_PREFIX . "categories.category_id, name, COUNT(video_id) AS video_count ";
$query .= "FROM " . DB_PREFIX . "categories LEFT JOIN " . DB_PREFIX . "videos ON " . DB_PREFIX . "categories.category_id = " . DB_PREFIX . "videos.category_id ";
$query .= "GROUP BY " . DB_PREFIX . "categories.category_id ORDER BY name asc";
$categories = $db->fetchAll($query, PDO::FETCH_OBJ);


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
            <input type="text" class="text" name="cat_name" value="<?=(!empty($category->name))?$category->name:''?>"/>
            <input type="hidden" name="submitted_add" value="TRUE" />
            <input type="submit" class="button" value="Add Category" />
        </form>
    </div>

    
    <h1>Video Categories</h1>

    <?php if (count($categories) > 1): ?>

        <?php foreach ($categories as $category_obj): ?>

            <div class="block">
                <p><strong><?=$category_obj->name?></strong> (<?=$category_obj->video_count?> videos)</p>
                <p><a href="" class="category-action" data-action="move">Move Videos</a> &nbsp;&nbsp;|&nbsp;&nbsp; <a href="" class="delete category-action" data-action="delete">Delete</a></p>

                <div class="hide">
                    <form action="<?=ADMIN?>/videos_categories.php" method="post">
                        <strong>Move videos to: </strong>
                        <select name="move" class="dropdown">
                        <?php $list = $categories; ?>
                        <?php foreach ($list as $value): ?>
                            <?php if ($category_obj->category_id == $value->category_id) continue; ?>
                            <option value="<?=$value->category_id?>"><?=$value->name?></option>
                        <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="category" value="<?=$category_obj->category_id?>" />
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