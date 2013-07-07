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
$page_title = 'Edit Page';
$layouts = array();
$admin_js[] = ADMIN . '/extras/tiny_mce/jquery.tinymce.js';
$admin_js[] = ADMIN . '/extras/tiny_mce/tiny_mce.js';
$admin_js[] = ADMIN . '/js/tinymce.js';



// Build return to list link
if (!empty ($_SESSION['list_page'])) {
    $list_page = $_SESSION['list_page'];
} else {
    $list_page = ADMIN . '/pages.php';
}



// Validate & load requested record
if (!empty ($_GET['id']) && is_numeric ($_GET['id'])) {
    $page = new Page ($_GET['id']);
    if (!$page->found) header ("Location: " . ADMIN . '/pages.php');
} else {
    header ("Location: " . ADMIN . '/pages.php');
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
        if ($slug == $page->slug || (!Page::IsReserved ($slug) && !Page::Exist (array ('slug' => $slug)))) {
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


    // Update record if no errors were found
    if (empty ($errors)) {
        $page->Update ($data);
        $message = 'Page has been updated';
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

<div id="pages-edit">

    <h1>Edit Page</h1>

    <?php if ($message): ?>
    <div class="message <?=$message_type?>"><?=$message?></div>
    <?php endif; ?>


    <div class="block">

        <p><a href="<?=$list_page?>">Return to previous screen</a></p>

        <form method="post" action="<?=ADMIN?>/pages_edit.php?id=<?=$page->page_id?>">

            <div class="row <?=(isset ($errors['title'])) ? 'error' : '' ?>">
                <label>*Title:</label>
                <input class="text" type="text" name="title" value="<?=$page->title?>" />
            </div>

            <div id="page-slug" class="row  <?=(isset ($errors['title'])) ? 'error' : '' ?>">
                
                <label>*URL:</label>
                <input type="hidden" name="slug" value="<?=$page->slug?>" />
                
                <div id="empty-slug">
                    Not Set
                    <div class="options"><a tabindex="-1" href="" class="edit">Edit</a></div>
                </div>
                
                <div id="view-slug">
                    <?=HOST?>/<span><?=$page->slug?></span>/
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
                <textarea class="text tinymce" name="content" rows="7" cols="50"><?=$page->content?></textarea>
            </div>

            <div class="row">
                <label>*Status:</label>
                <select class="dropdown" name="status">
                    <option <?=($page->status=='published')?'selected="selected"':''?> value="published">Published</option>
                    <option <?=($page->status=='draft')?'selected="selected"':''?> value="draft">Draft</option>
                </select>
            </div>

            <div class="row">
                <label>*Layout:</label>
                <select class="dropdown" name="layout">
                    <?php foreach ($layouts as $layout): ?>
                        <option <?=($page->layout==$layout)?'selected="selected"':''?> value="<?=$layout?>"><?=$layout?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="row-shift">
                <input type="hidden" name="submitted" value="TRUE" />
                <a href="<?=HOST?>/page/?preview=<?=$page->page_id?>" class="button preview" target="_ccsite">Preview</a>
                <input type="submit" class="button" value="Update Page" />
            </div>
            
        </form>
    </div>

</div>

<?php include ('footer.php'); ?>