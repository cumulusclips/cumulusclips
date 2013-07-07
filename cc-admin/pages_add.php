<?php

// Include required files
include_once (dirname (dirname (__FILE__)) . '/cc-core/config/admin.bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Page');


// Establish page variables, objects, arrays, etc
Functions::RedirectIf ($logged_in = User::LoginCheck(), HOST . '/login/');
$admin = new User ($logged_in);
Functions::RedirectIf (User::CheckPermissions ('admin_panel', $admin), HOST . '/myaccount/');
$data = array();
$errors = array();
$message = null;
$page_title = 'Add New Page';
$page = null;
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
foreach (glob (THEME_PATH . '/layouts/*.header.tpl') as $filename) {
    $layouts[] = basename ($filename, '.header.tpl');
}





/***********************
HANDLE FORM IF SUBMITTED
***********************/

if (isset ($_POST['submitted'])) {

    // Validate layout
    if (!empty ($_POST['layout']) && !ctype_space ($_POST['layout'])) {
        $data['layout'] = $_POST['layout'];
    } else {
        $errors['layout'] = "You didn't provide a valid layout";
    }


    // Validate status
    if (!empty ($_POST['status']) && in_array ($_POST['status'], array ('published', 'draft'))) {
        $data['status'] = $_POST['status'];
    } else {
        $errors['status'] = "You didn't provide a valid status";
    }


    // Validate title
    if (!empty ($_POST['title']) && !ctype_space ($_POST['title'])) {
        $data['title'] = htmlspecialchars (trim ($_POST['title']));
    } else {
        $errors['title'] = "You didn't enter a valid title";
    }


    // Validate slug
    if (!empty ($_POST['slug']) && !ctype_space ($_POST['slug'])) {
        $slug = Functions::CreateSlug (trim ($_POST['slug']));
        if (!Page::IsReserved ($slug) && !Page::Exist (array ('slug' => $slug))) {
            $data['slug'] = $slug;
        } else {
            $errors['slug'] = "URL is not available";
        }
    } else {
        $errors['slug'] = "You didn't enter a valid URL";
    }


    // Validate content
    if (!empty ($_POST['content']) && !ctype_space ($_POST['content'])) {
        $data['content'] = trim ($_POST['content']);
    } else {
        $data['content'] = '';
    }


    // Create page if no errors were found
    if (empty ($errors)) {
        $page_id = Page::Create ($data);
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
    <div class="<?=$message_type?>"><?=$message?></div>
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
                <textarea class="text tinymce" name="content" rows="7" cols="50"></textarea>
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