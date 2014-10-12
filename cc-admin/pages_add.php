<?php

// Init application
include_once(dirname(dirname(__FILE__)) . '/cc-core/config/admin.bootstrap.php');

// Verify if user is logged in
$userService = new UserService();
$adminUser = $userService->loginCheck();
Functions::RedirectIf($adminUser, HOST . '/login/');
Functions::RedirectIf($userService->checkPermissions('admin_panel', $adminUser), HOST . '/account/');

// Establish page variables, objects, arrays, etc
$pageMapper = new PageMapper();
$pageService = new PageService();
$view = View::getView();
$page = new Page();
$data = array();
$errors = array();
$message = null;
$page_title = 'Add New Page';
$admin_js[] = ADMIN . '/extras/tiny_mce/jquery.tinymce.js';
$admin_js[] = ADMIN . '/extras/tiny_mce/tiny_mce.js';
$admin_js[] = ADMIN . '/js/tinymce.js';



// Build return to list link
if (!empty ($_SESSION['list_page'])) {
    $list_page = $_SESSION['list_page'];
} else {
    $list_page = ADMIN . '/pages.php';
}



// Retrieve list of available layouts
foreach (glob (THEME_PATH . '/layouts/*.phtml') as $filename) {
    $layouts[] = basename ($filename, '.phtml');
}





/***********************
HANDLE FORM IF SUBMITTED
***********************/

if (isset ($_POST['submitted'])) {

    // Validate layout
    if (!empty ($_POST['layout']) && !ctype_space ($_POST['layout'])) {
        $page->layout = $_POST['layout'];
    } else {
        $errors['layout'] = "You didn't provide a valid layout";
    }


    // Validate status
    if (!empty ($_POST['status']) && in_array ($_POST['status'], array ('published', 'draft'))) {
        $page->status = $_POST['status'];
    } else {
        $errors['status'] = "You didn't provide a valid status";
    }


    // Validate title
    if (!empty ($_POST['title']) && !ctype_space ($_POST['title'])) {
        $page->title = trim ($_POST['title']);
    } else {
        $errors['title'] = "You didn't enter a valid title";
    }


    // Validate slug
    if (!empty ($_POST['slug']) && !ctype_space ($_POST['slug'])) {
        $slug = Functions::CreateSlug (trim ($_POST['slug']));
        if (!$pageService->isReserved($slug) && !$pageMapper->getPageBySlug($slug)) {
            $page->slug = $slug;
        } else {
            $errors['slug'] = "URL is not available";
        }
    } else {
        $errors['slug'] = "You didn't enter a valid URL";
    }


    // Validate content
    if (!empty ($_POST['content']) && !ctype_space ($_POST['content'])) {
        $page->content = trim ($_POST['content']);
    } else {
        $page->content = '';
    }


    // Create page if no errors were found
    if (empty ($errors)) {
        $pageMapper->save($page);
        $page = new Page();
        $message = 'Page has been created';
        $message_type = 'success';
    } else {
        $message = 'Errors were found. Please correct the errors below and try again.<br /><br />- ';
        $message .= implode ('<br />- ', $errors);
        $message_type = 'errors';
    }

}


// Output Header
include ('header.php');

?>

<div id="pages-add">

    <h1>Add New Page</h1>

    <?php if ($message): ?>
    <div class="message <?=$message_type?>"><?=$message?></div>
    <?php endif; ?>


    <div class="block">

        <p><a href="<?=$list_page?>">Return to previous screen</a></p>

        <form method="post" action="<?=ADMIN?>/pages_add.php">

            <div class="row <?=(isset ($errors['title'])) ? 'error' : '' ?>">
                <label>*Title:</label>
                <input id="page-title" class="text" type="text" name="title" />
            </div>

            <div id="page-slug" class="row  <?=(isset ($errors['title'])) ? 'error' : '' ?>">
                
                <label>*URL:</label>
                <input type="hidden" name="slug" />
                
                <div id="empty-slug">
                    Not Set
                    <div class="options"><a tabindex="-1" href="" class="edit">Edit</a></div>
                </div>
                
                <div id="view-slug">
                    <?=HOST?>/<span></span>/
                    <div class="options"><a tabindex="-1" href="" class="edit">Edit</a></div>
                </div>
                
                <div id="edit-slug">
                    <?=HOST?>/<input class="text" type="text" name="edit-slug" />/
                    <div class="options">
                        <a tabindex="-1" href="" class="done">Done</a>
                        <a tabindex="-1" href="" class="cancel">Cancel</a>
                    </div>
                </div>

            </div>

            <div class="row">
                <label>Content:</label>
                <textarea class="text tinymce" name="content" rows="7" cols="50"><?=(!empty($page->content)) ? $page->content : ''?></textarea>
            </div>

            <div class="row">
                <label>*Status:</label>
                <select class="dropdown" name="status">
                    <option value="published">Published</option>
                    <option value="draft">Draft</option>
                </select>
            </div>

            <div class="row">
                <label>*Layout:</label>
                <select class="dropdown" name="layout">
                    <?php foreach ($layouts as $layout): ?>
                        <option value="<?=$layout?>"><?=$layout?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="row-shift">
                <input type="hidden" name="submitted" value="TRUE" />
                <input tabindex="4" type="submit" class="button" value="Add Page" />
            </div>
            
        </form>
    </div>

</div>

<?php include ('footer.php'); ?>