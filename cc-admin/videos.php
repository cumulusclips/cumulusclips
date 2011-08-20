<?php

### Created on February 28, 2009
### Created by Miguel A. Hurtado
### This script displays the site homepage


// Include required files
include ('../cc-core/config/admin.bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Video');
App::LoadClass ('Flag');
App::LoadClass ('Pagination');


// Establish page variables, objects, arrays, etc
Plugin::Trigger ('admin.videos.start');
//$logged_in = User::LoginCheck(HOST . '/login/');
//$admin = new User ($logged_in);
$records_per_page = 9;
$url = ADMIN . '/videos.php';
$query_string = array();
$categories = array();
$message = null;
$sub_header = null;



// Retrieve Category names
$query = "SELECT cat_id, cat_name FROM " . DB_PREFIX . "categories";
$result = $db->Query ($query);
while ($row = $db->FetchObj ($result)) {
    $categories[$row->cat_id] = $row->cat_name;
}



### Handle "Delete" video if requested
if (!empty ($_GET['delete']) && is_numeric ($_GET['delete'])) {

    // Validate video id
    if (Video::Exist (array ('video_id' => $_GET['delete']))) {
        Video::Delete($_GET['delete']);
        $message = 'Video has been deleted';
        $message_type = 'success';
    }

}


### Handle "Feature" video if requested
else if (!empty ($_GET['feature']) && is_numeric ($_GET['feature'])) {

    // Validate video id
    if (Video::Exist (array ('video_id' => $_GET['feature'], 'featured' => 0))) {
        $video = new Video ($_GET['feature']);
        $video->Update (array ('featured' => 1));
        $message = 'Video has been featured';
        $message_type = 'success';
    }

}


### Handle "Un-Feature" video if requested
else if (!empty ($_GET['unfeature']) && is_numeric ($_GET['unfeature'])) {

    // Validate video id
    if (Video::Exist (array ('video_id' => $_GET['unfeature'], 'featured' => 1))) {
        $video = new Video ($_GET['unfeature']);
        $video->Update (array ('featured' => 0));
        $message = 'Video has been unfeatured';
        $message_type = 'success';
    }

}


### Handle "Approve" video if requested
else if (!empty ($_GET['approve']) && is_numeric ($_GET['approve'])) {

    // Validate video id
    if (Video::Exist (array ('video_id' => $_GET['approve']))) {
        $video = new Video ($_GET['approve']);
        $video->Approve (true);
        $message = 'Video has been approved and is now available';
        $message_type = 'success';
    }

}


### Handle "Unban" video if requested
else if (!empty ($_GET['unban']) && is_numeric ($_GET['unban'])) {

    // Validate video id
    if (Video::Exist (array ('video_id' => $_GET['unban']))) {
        $video = new Video ($_GET['unban']);
        $video->Approve (true);
        $message = 'Video has been unbanned';
        $message_type = 'success';
    }

}


### Handle "Ban" video if requested
else if (!empty ($_GET['ban']) && is_numeric ($_GET['ban'])) {

    // Validate video id
    if (Video::Exist (array ('video_id' => $_GET['ban']))) {
        $video = new Video ($_GET['ban']);
        $video->Update (array ('status' => 'banned'));
        Flag::FlagDecision ($video->video_id, 'video', true);
        $message = 'Video has been banned';
        $message_type = 'success';
    }

}




### Determine which type (status) of video to display
$query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE";
$status = (!empty ($_GET['status'])) ? $_GET['status'] : 'approved';
switch ($status) {

    case 'pending':
        $query .= " status = 'pending approval'";
        $query_string['status'] = 'pending approval';
        $header = 'Pending Videos';
        $page_title = 'Pending Videos';
        break;
    case 'banned':
        $query .= " status = 'banned'";
        $query_string['status'] = 'banned';
        $header = 'Banned Videos';
        $page_title = 'Banned Videos';
        break;
    case 'featured':
        $query .= " status = 'approved' AND featured = 1";
        $query_string['status'] = 'featured';
        $header = 'Featured Videos';
        $page_title = 'Featured Videos';
        break;
    default:
        $query .= " status = 'approved'";
        $status = 'approved';
        $header = 'Approved Videos';
        $page_title = 'Approved Videos';
        break;

}



// Handle Search Member Form
if (isset ($_POST['search_submitted'])&& !empty ($_POST['search'])) {

    $like = $db->Escape (trim ($_POST['search']));
    $query_string['search'] = $like;
    $query .= " AND title LIKE '%$like%'";
    $sub_header = "Search Results for: <em>$like</em>";

} else if (!empty ($_GET['search'])) {

    $like = $db->Escape (trim ($_GET['search']));
    $query_string['search'] = $like;
    $query .= " AND title LIKE '%$like%'";
    $sub_header = "Search Results for: <em>$like</em>";

}



// Retrieve total count
$query .= " ORDER BY video_id DESC";
$result_count = $db->Query ($query);
$total = $db->Count ($result_count);

// Initialize pagination
$url .= (!empty ($query_string)) ? '?' . http_build_query($query_string) : '';
$pagination = new Pagination ($url, $total, $records_per_page, false);
$start_record = $pagination->GetStartRecord();
$_SESSION['list_page'] = $pagination->GetURL();

// Retrieve limited results
$query .= " LIMIT $start_record, $records_per_page";
$result = $db->Query ($query);


// Output Header
include ('header.php');

?>

<div id="videos">

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
            <select name="status" data-jump="<?=ADMIN?>/videos.php">
                <option <?=(isset($status) && $status == 'approved') ? 'selected="selected"' : ''?>value="approved">Approved</option>
                <option <?=(isset($status) && $status == 'featured') ? 'selected="selected"' : ''?>value="featured">Featured</option>
                <option <?=(isset($status) && $status == 'pending') ? 'selected="selected"' : ''?>value="pending">Pending</option>
                <option <?=(isset($status) && $status == 'banned') ? 'selected="selected"' : ''?>value="banned">Banned</option>
            </select>
        </div>

        <div class="search">
            <form method="POST" action="<?=ADMIN?>/videos.php?status=<?=$status?>">
                <input type="hidden" name="search_submitted" value="true" />
                <input type="text" name="search" value="" />&nbsp;
                <input type="submit" name="submit" class="button" value="Search" />
            </form>
        </div>
    </div>

    <?php if ($total > 0): ?>

        <div class="block list">
            <table>
                <thead>
                    <tr>
                        <td class="video-title large">Title</td>
                        <td class="category large">Category</td>
                        <td class="large">Member</td>
                        <td class="large">Upload Date</td>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = $db->FetchObj ($result)): ?>

                    <?php $odd = empty ($odd) ? true : false; ?>
                    <?php $video = new Video ($row->video_id); ?>

                    <tr class="<?=$odd ? 'odd' : ''?>">
                        <td class="video-title">
                            <a href="<?=ADMIN?>/videos_edit.php?id=<?=$video->video_id?>" class="large"><?=$video->title?></a><br />
                            <div class="record-actions invisible">

                                <?php if (in_array ($status, array ('approved','featured'))): ?>
                                    <a href="<?=HOST?>/videos/<?=$video->video_id?>/<?=$video->slug?>/" target="_ccsite">Watch</a>
                                <?php endif; ?>


                                <a href="<?=ADMIN?>/videos_edit.php?id=<?=$video->video_id?>">Edit</a>


                                <?php if ($status == 'approved'): ?>
                                    <?php if ($video->featured == 1): ?>
                                        <a href="<?=$pagination->GetURL('unfeature='.$video->video_id)?>">Un-Feature</a>
                                    <?php else: ?>
                                        <a href="<?=$pagination->GetURL('feature='.$video->video_id)?>">Feature</a>
                                    <?php endif ?>
                                <?php endif; ?>

                                    
                                <?php if ($status == 'featured'): ?>
                                    <a href="<?=$pagination->GetURL('unfeature='.$video->video_id)?>">Un-Feature</a>
                                <?php endif; ?>


                                <?php if ($status == 'pending'): ?>
                                    <a class="approve" href="<?=$pagination->GetURL('approve='.$video->video_id)?>">Approve</a>
                                <?php elseif (in_array ($status, array ('approved','featured'))): ?>
                                    <a class="delete" href="<?=$pagination->GetURL('ban='.$video->video_id)?>">Ban</a>
                                <?php elseif ($status == 'banned'): ?>
                                    <a href="<?=$pagination->GetURL('unban='.$video->video_id)?>">Unban</a>
                                <?php endif; ?>


                                <a class="delete confirm" href="<?=$pagination->GetURL('delete='.$video->video_id)?>" data-confirm="You're about to delete this video. This cannot be undone. Do you want to proceed?">Delete</a>
                            </div>
                        </td>
                        <td class="category"><?=$categories[$video->cat_id]?></td>
                        <td><?=$video->username?></td>
                        <td><?=$video->date_created?></td>
                    </tr>

                <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <?=$pagination->paginate()?>

    <?php else: ?>
        <div class="block"><strong>No videos found</strong></div>
    <?php endif; ?>

</div>

<?php include ('footer.php'); ?>