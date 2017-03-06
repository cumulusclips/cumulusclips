<?php

// Init application
include_once(dirname(dirname(__FILE__)) . '/cc-core/system/admin.bootstrap.php');

// Verify if user is logged in
$authService->enforceTimeout(true);

// Verify user can access admin panel
$userService = new \UserService();
Functions::redirectIf($userService->checkPermissions('admin_panel', $adminUser), HOST . '/account/');

// Establish page variables, objects, arrays, etc
$page_title = 'Video Categories';
$categories = array();
$data = array();
$errors = array();
$message = null;
$categoryMapper = new CategoryMapper();

// Handle create category form
if (isset($_POST['submitted_add'])) {

    // Validate form nonce token and submission speed
    if (
        !empty($_POST['nonce'])
        && !empty($_SESSION['formNonce'])
        && !empty($_SESSION['formTime'])
        && $_POST['nonce'] == $_SESSION['formNonce']
        && time() - $_SESSION['formTime'] >= 2
    ) {
        try {
            // Validate title
            if (empty($_POST['cat_name'])) {
                throw new Exception('Invalid category name. Please try again.');
            }

            $category = new Category();
            $category->slug = Functions::createSlug(trim($_POST['cat_name']));
            $category->name = trim($_POST['cat_name']);

            if ($categoryMapper->getCategoryBySlug($category->slug)) {
                throw new Exception('Category name or slug already exists. Please note that in the slug special characters are replaced by hyphens.');
            }

            $categoryMapper->save($category);
            $message = $category->name . ' was successfully created.';
            $messageType = 'alert-success';
            unset($category);

        } catch (Exception $e) {
            $errors['cat_name'] = true;
            $message = $e->getMessage();
            $messageType = 'alert-danger';
        }

    } else {
        $message = 'Expired or invalid session';
        $messageType = 'alert-danger';
    }
}

// Handle move/delete category form
if (isset($_POST['submitted_edit'])) {

    // Validate form nonce token and submission speed
    if (
        !empty($_POST['nonce'])
        && !empty($_SESSION['formNonce'])
        && !empty($_SESSION['formTime'])
        && $_POST['nonce'] == $_SESSION['formNonce']
        && time() - $_SESSION['formTime'] >= 2
    ) {
        // Validate move category
        if (isset($_POST['move']) && is_numeric($_POST['move']) && $categoryMapper->getCategoryById($_POST['move'])) {
            $data['move'] = $_POST['move'];
        } else {
            $errors['move'] = 'Invalid receiving (move to) category';
        }

        // Validate category
        if (isset($_POST['category']) && is_numeric($_POST['category']) && $categoryMapper->getCategoryById($_POST['category'])) {
            $data['category'] = $_POST['category'];
        } else {
            $errors['category'] = 'Invalid source category';
        }

        // Verify videos aren't moved to same category
        if (isset($data['category'], $data['move'])) {
            if ($data['category'] == $data['move']) $errors['category'] = "Can't move videos to the same category";
        }

        // Validate action
        if (!empty($_POST['action']) && in_array($_POST['action'], array('move','delete'))) {
            $data['action'] = $_POST['action'];
        } else {
            $errors['action'] = 'Invalid category action';
        }

        // Move videos if no errors were made
        if (empty($errors)) {

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
            $messageType = 'alert-success';
            unset ($data);

        } else {
            $message = 'The following errors were found. Please correct them and try again.';
            $message .= '<br><br> - ' . implode('<br> - ', $errors);
            $messageType = 'alert-danger';
        }

    } else {
        $message = 'Expired or invalid session';
        $messageType = 'alert-danger';
    }
}

// Retrieve Category names
$query = "SELECT " . DB_PREFIX . "categories.category_id, name, COUNT(video_id) AS video_count ";
$query .= "FROM " . DB_PREFIX . "categories LEFT JOIN " . DB_PREFIX . "videos ON " . DB_PREFIX . "categories.category_id = " . DB_PREFIX . "videos.category_id ";
$query .= "WHERE status = 'approved' OR video_id IS NULL ";
$query .= "GROUP BY " . DB_PREFIX . "categories.category_id ORDER BY name asc";
$categories = $db->fetchAll($query, array(), PDO::FETCH_OBJ);

// Generate new form nonce
$formNonce = md5(uniqid(rand(), true));
$_SESSION['formNonce'] = $formNonce;
$_SESSION['formTime'] = time();

// Output Header
$pageName = 'videos-categories';
include('header.php');

?>

<?php if ($message): ?>
<div class="alert <?=$messageType?>"><?=$message?></div>
<?php endif; ?>

<h1>Add Category</h1>
<div id="add-category" class="form-inline">
    <form action="<?=ADMIN?>/videos_categories.php" method="post" class="<?=(isset($errors['cat_name'])) ? 'has-error' : ''?>">
        <label class="control-label">Category Name:</label>
        <input type="text" class="form-control" name="cat_name" value="<?=(!empty($category->name))?$category->name:''?>"/>
        <input type="hidden" name="submitted_add" value="TRUE" />
        <input type="hidden" name="nonce" value="<?=$formNonce?>" />
        <input type="submit" class="button" value="Add Category" />
    </form>
</div>

<h1>Video Categories</h1>

<?php if (count($categories) > 1): ?>

    <ul class="list-group">
    <?php foreach ($categories as $categoryObj): ?>

        <li class="list-group-item">

            <span class="badge"><?=$categoryObj->video_count?></span>
            <h3 class="list-group-item-heading"><?=$categoryObj->name?></h3>
            <p><a href="" class="category-action" data-action="move">Move Videos</a> &nbsp;&nbsp;|&nbsp;&nbsp; <a href="" class="delete category-action" data-action="delete">Delete</a></p>

            <div class="category-action-effect hide form-inline">
                <form action="<?=ADMIN?>/videos_categories.php" method="post">
                    <label>Move videos to: </label>
                    <select name="move" class="form-control">
                    <?php $list = $categories; ?>
                    <?php foreach ($list as $value): ?>
                        <?php if ($categoryObj->category_id == $value->category_id) continue; ?>
                        <option value="<?=$value->category_id?>"><?=$value->name?></option>
                    <?php endforeach; ?>
                    </select>
                    <input type="hidden" name="category" value="<?=$categoryObj->category_id?>" />
                    <input type="hidden" name="action" value="" />
                    <input type="hidden" name="submitted_edit" value="TRUE" />
                    <input type="hidden" name="nonce" value="<?=$formNonce?>" />
                    <input type="submit" class="button move-videos" value="Move Videos" />
                    <input type="submit" class="button delete-category" value="Delete Category" />
                </form>
            </div>
        </li>

    <?php endforeach; ?>
    </ul>

<?php else: ?>
    <ul class="list-group">
        <li class="list-group-item">
            <span class="badge"><?=$categories[0]->video_count?></span>
            <h3 class="list-group-item-heading"><?=$categories[0]->name?></h3>
        </li>
    </ul>
<?php endif; ?>

<?php include('footer.php'); ?>