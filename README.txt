[PERMISSIONS]

Make sure the following directories are writeable by PHP and the webserver:

/cc-core/log
/cc-content/uploads/temp
/cc-content/uploads/flv
/cc-content/uploads/mp4
/cc-content/uploads/thumbs





[VIDEO STATUS CODES]

1 - New
2 - Pending Conversion
3 - Processing
4 - Pending Approval
5 - Approved
6 - Banned





[REQUIREMENTS]

System

FFMPEG
QT-FASTSTART
MYSQL
APACHE 2.X
    MOD_REWRITE
PHP5
    XML
    ZIP
    CURL
    PHP5-FFMPEG

upload_max_filesize = 100M
post_max_size = 100M
max_execution_time = 1500





[META & TITLE TAGS]

http://www.seomoz.org/learn-seo/meta-description





[PLUGIN HOOK LOCATIONS]

NOTES:
- Before Updates occur right before query
- Updates occur after update query at last possible location
- Deletes / Removals occur after delete at last possible location
- Before Additons occur right before query
- Additions occur after create at last possible location


### AJAX & SYSTEM FILES

app.start - /cc-core/config/bootstrap.php

username.ajax.start -  - /cc-core/system/username.ajax.php


flag.ajax.start - /cc-core/system/flag.ajax.php
flag.ajax.login_check - /cc-core/system/flag.ajax.php
flag.ajax.flag_video - /cc-core/system/flag.ajax.php
flag.ajax.flag_member - /cc-core/system/flag.ajax.php
flag.ajax.flag_comment - /cc-core/system/flag.ajax.php
flag.ajax.alert - /cc-core/system/flag.ajax.php
flag.ajax.before_flag - /cc-core/system/flag.ajax.php
flag.ajax.flag - /cc-core/system/flag.ajax.php


rate.ajax.start - /cc-core/system/rate.ajax.php
rate.ajax.login_check - /cc-core/system/rate.ajax.php
rate.ajax.rate_video - /cc-core/system/rate.ajax.php
rate.ajax.rate_video_duplicate - /cc-core/system/rate.ajax.php


comment.ajax.start - /cc-core/system/comment.ajax.php
comment.ajax.login_check - /cc-core/system/comment.ajax.php
comment.ajax.before_post_comment - /cc-core/system/comment.ajax.php
comment.ajax.post_comment - /cc-core/system/comment.ajax.php


post.ajax.start - /cc-core/system/post.ajax.php
post.ajax.before_post_update - /cc-core/system/post.ajax.php
post.ajax.post_update - /cc-core/system/post.ajax.php


favorite.ajax.start - /cc-core/system/favorite.ajax.php
favorite.ajax.login_check - /cc-core/system/favorite.ajax.php
favorite.ajax.favorite_video - /cc-core/system/favorite.ajax.php


subscribe.ajax.start - /cc-core/system/subscribe.ajax.php
subscribe.ajax.login_check - /cc-core/system/subscribe.ajax.php
subscribe.ajax.subscribe - /cc-core/system/subscribe.ajax.php
subscribe.ajax.unsubscribe - /cc-core/system/subscribe.ajax.php


upload.ajax.start - /cc-core/system/upload.ajax.php
upload.ajax.load_video - /cc-core/system/upload.ajax.php
upload.ajax.before_move_video - /cc-core/system/upload.ajax.php
upload.ajax.before_change_permissions - /cc-core/system/upload.ajax.php
upload.ajax.before_update_video - /cc-core/system/upload.ajax.php
upload.ajax.before_encode - /cc-core/system/upload.ajax.php
upload.ajax.encode - /cc-core/system/upload.ajax.php


grab.ajax.start - /cc-core/system/grab.ajax.php
grab.ajax.load_video - /cc-core/system/grab.ajax.php
grab.ajax.before_validate_video - /cc-core/system/grab.ajax.php
grab.ajax.before_update_video - /cc-core/system/grab.ajax.php
grab.ajax.before_grab - /cc-core/system/grab.ajax.php
grab.ajax.grab - /cc-core/system/grab.ajax.php


grab.start - /cc-core/system/grab.php
grab.parse - /cc-core/system/grab.php
grab.load_video - /cc-core/system/grab.php
grab.before_download - /cc-core/system/grab.php
grab.download - /cc-core/system/grab.php
grab.before_encode - /cc-core/system/grab.php
grab.encode - /cc-core/system/grab.php


encode.start - /cc-core/system/encode.php
encode.parse - /cc-core/system/encode.php
encode.load_video - /cc-core/system/encode.php
encode.before_flv_encode - /cc-core/system/encode.php
encode.flv_encode - /cc-core/system/encode.php
encode.before_mp4_encode - /cc-core/system/encode.php
encode.mp4_encode - /cc-core/system/encode.php
encode.before_get_duration - /cc-core/system/encode.php
encode.get_duration - /cc-core/system/encode.php
encode.before_create_thumbnail - /cc-core/system/encode.php
encode.create_thumbnail - /cc-core/system/encode.php
encode.before_update - /cc-core/system/encode.php
encode.update - /cc-core/system/encode.php
encode.complete - /cc-core/system/encode.php


### CLASS LIBRARY

user.get - /cc-core/lib/User.php
user.create - /cc-core/lib/User.php
user.update - /cc-core/lib/User.php
user.delete - /cc-core/lib/User.php
user.login - /cc-core/lib/User.php
user.logout - /cc-core/lib/User.php
user.reset_password - /cc-core/lib/User.php
user.activate - /cc-core/lib/User.php
user.before_approve - /cc-core/lib/User.php
user.approve - /cc-core/lib/User.php
user.approve_required - /cc-core/lib/User.php
user.release- /cc-core/lib/User.php
user.reapprove - /cc-core/lib/User.php


video.get - /cc-core/lib/Video.php
video.create - /cc-core/lib/Video.php
video.update - /cc-core/lib/Video.php
video.delete - /cc-core/lib/Video.php
video.notify_subscribers - /cc-core/lib/Video.php
video.approve - /cc-core/lib/Video.php
video.before_approve - /cc-core/lib/Video.php
video.approve_required - /cc-core/lib/Video.php
video.release - /cc-core/lib/Video.php
video.reapprove - /cc-core/lib/Video.php


comment.get - /cc-core/lib/Comment.php
comment.create - /cc-core/lib/Comment.php
comment.update - /cc-core/lib/Comment.php
comment.delete - /cc-core/lib/Comment.php
comment.notify_member - /cc-core/lib/Comment.php
comment.before_approve - /cc-core/lib/Comment.php
comment.approve - /cc-core/lib/Comment.php
comment.approve_required - /cc-core/lib/Comment.php
comment.release - /cc-core/lib/Comment.php
comment.reapprove - /cc-core/lib/Comment.php


post.get - /cc-core/lib/Post.php
post.create - /cc-core/lib/Post.php
post.update - /cc-core/lib/Post.php
post.delete - /cc-core/lib/Post.php


message.get - /cc-core/lib/Message.php
message.create - /cc-core/lib/Message.php
message.update - /cc-core/lib/Message.php
message.delete - /cc-core/lib/Message.php


favorite.get - /cc-core/lib/Favorite.php
favorite.create - /cc-core/lib/Favorite.php
favorite.update - /cc-core/lib/Favorite.php
favorite.delete - /cc-core/lib/Favorite.php


flag.get - /cc-core/lib/Flag.php
flag.create - /cc-core/lib/Flag.php
flag.update - /cc-core/lib/Flag.php
flag.delete - /cc-core/lib/Flag.php


subscription.get - /cc-core/lib/Subscription.php
subscription.create - /cc-core/lib/Subscription.php
subscription.update - /cc-core/lib/Subscription.php
subscription.delete - /cc-core/lib/Subscription.php


category.get - /cc-core/lib/Category.php
category.create - /cc-core/lib/Category.php
category.update - /cc-core/lib/Category.php
category.delete - /cc-core/lib/Category.php


privacy.get - /cc-core/lib/Privacy.php
privacy.create - /cc-core/lib/Privacy.php
privacy.update - /cc-core/lib/Privacy.php
privacy.delete - /cc-core/lib/Privacy.php


rating.get - /cc-core/lib/Rating.php
rating.create - /cc-core/lib/Rating.php
rating.update - /cc-core/lib/Rating.php
rating.delete - /cc-core/lib/Rating.php


page.get - /cc-core/lib/Page.php
page.create - /cc-core/lib/Page.php
page.update - /cc-core/lib/Page.php
page.delete - /cc-core/lib/Page.php


avatar.before_save - /cc-core/lib/Avatar.php
avatar.save - /cc-core/lib/Avatar.php


pagination.start - /cc-core/lib/Pagination.php
pagination.paginate - /cc-core/lib/Pagination.php


view.init - /cc-core/lib/View.php
view.render - /cc-core/lib/View.php
view.set_layout - /cc-core/lib/View.php
view.header - /cc-core/lib/View.php
view.footer - /cc-core/lib/View.php
view.block - /cc-core/lib/View.php
view.repeating_block - /cc-core/lib/View.php
view.repeating_block_loop - /cc-core/lib/View.php
view.add_sidebar_block - /cc-core/lib/View.php
view.output_sidebar_blocks - /cc-core/lib/View.php
view.output_sidebar_blocks_loop - /cc-core/lib/View.php
view.add_css - /cc-core/lib/View.php
view.write_css - /cc-core/lib/View.php
view.write_css_loop - /cc-core/lib/View.php
view.add_js - /cc-core/lib/View.php
view.write_js - /cc-core/lib/View.php
view.write_js_loop - /cc-core/lib/View.php
view.add_meta - /cc-core/lib/View.php
view.write_meta - /cc-core/lib/View.php
view.write_meta_loop - /cc-core/lib/View.php


### CONTROLLERS

index.start - /cc-core/controllers/index.php
index.before_render - /cc-core/controllers/index.php


videos.start - /cc-core/controllers/videos.php
videos.before_render - /cc-core/controllers/videos.php


members.start - /cc-core/controllers/members.php
members.before_render - /cc-core/controllers/members.php


member_videos.start - /cc-core/controllers/member_videos.php
member_videos.before_render - /cc-core/controllers/member_videos.php


profile.start - /cc-core/controllers/profile.php
profile.before_render - /cc-core/controllers/profile.php
profile.load_member - /cc-core/controllers/profile.php
profile.load_recent_videos - /cc-core/controllers/profile.php
profile.load_posts - /cc-core/controllers/profile.php


play.start - /cc-core/controllers/play.php
play.before_render - /cc-core/controllers/play.php
play.load_video - /cc-core/controllers/play.php
play.load_suggestions - /cc-core/controllers/play.php
play.comment_count - /cc-core/controllers/play.php
play.load_comments - /cc-core/controllers/play.php


comments.start - /cc-core/controllers/comments.php
comments.before_render - /cc-core/controllers/comments.php


contact.start - /cc-core/controllers/contact.php
contact.before_render - /cc-core/controllers/contact.php
contact.send - /cc-core/controllers/contact.php


activate.start - /cc-core/controllers/activate.php
activate.before_render - /cc-core/controllers/activate.php
activate.activate - /cc-core/controllers/activate.php


opt_out.start - /cc-core/controllers/opt_out.php
opt_out.before_render - /cc-core/controllers/opt_out.php
opt_out.opt_out - /cc-core/controllers/opt_out.php


search.start - /cc-core/controllers/search.php
search.before_render - /cc-core/controllers/search.php
search.search_count - /cc-core/controllers/search.php
search.search - /cc-core/controllers/search.php


login.start - /cc-core/controllers/login.php
login.before_render - /cc-core/controllers/login.php
login.login - /cc-core/controllers/login.php
login.remember_me - /cc-core/controllers/login.php
login.password_reset - /cc-core/controllers/login.php


register.start - /cc-core/controllers/register.php
register.before_render - /cc-core/controllers/register.php
register.before_create - /cc-core/controllers/register.php
register.create - /cc-core/controllers/register.php


page.start - /cc-core/system/page.php
page.before_render - /cc-core/system/page.php


system_404.start - /cc-core/controllers/system_404.php
system_404.before_render - /cc-core/controllers/system_404.php


system_error.start - /cc-core/controllers/system_error.php
system_error.before_render - /cc-core/controllers/system_error.php


page.start - /cc-core/system/page.php
page.before_render - /cc-core/system/page.php


### MYACCOUNT CONTROLLERS

myaccount.start - /cc-core/controllers/myaccount/myaccount.php
myaccount.before_render - /cc-core/controllers/myaccount/myaccount.php


upload.start - /cc-core/controllers/myaccount/upload.php
upload.before_render - /cc-core/controllers/myaccount/upload.php
upload.before_create_video - /cc-core/controllers/myaccount/upload.php
upload.create_video - /cc-core/controllers/myaccount/upload.php


upload_video.start - /cc-core/controllers/myaccount/upload_video.php
upload_video.before_render - /cc-core/controllers/myaccount/upload_video.php


upload_complete.start - /cc-core/controllers/myaccount/upload_complete.php
upload_complete.before_render - /cc-core/controllers/myaccount/upload_complete.php


edit_video.start - /cc-core/controllers/myaccount/edit_video.php
edit_video.before_render - /cc-core/controllers/myaccount/edit_video.php
edit_video.edit - /cc-core/controllers/myaccount/edit_video.php


myvideos.start - /cc-core/controllers/myaccount/myvideos.php
myvideos.before_render - /cc-core/controllers/myaccount/myvideos.php
myvideos.delete_video - /cc-core/controllers/myaccount/myvideos.php


myfavorites.start - /cc-core/controllers/myaccount/myfavorites.php
myfavorites.before_render - /cc-core/controllers/myaccount/myfavorites.php
myfavorites.remove_favorite - /cc-core/controllers/myaccount/myfavorites.php


update_profile.start - /cc-core/controllers/myaccount/update_profile.php
update_profile.before_render - /cc-core/controllers/myaccount/update_profile.php
update_profile.update_profile - /cc-core/controllers/myaccount/update_profile.php
update_profile.update_avatar - /cc-core/controllers/myaccount/update_profile.php
update_profile.reset_avatar - /cc-core/controllers/myaccount/update_profile.php


privacy_settings.start - /cc-core/controllers/myaccount/privacy_settings.php
privacy_settings.before_render - /cc-core/controllers/myaccount/privacy_settings.php
privacy_settings.update_privacy - /cc-core/controllers/myaccount/privacy_settings.php


change_password.start - /cc-core/controllers/myaccount/change_password.php
change_password.before_render - /cc-core/controllers/myaccount/change_password.php
change_password.change_password - /cc-core/controllers/myaccount/change_password.php


subscriptions.start - /cc-core/controllers/myaccount/subscriptions.php
subscriptions.before_render - /cc-core/controllers/myaccount/subscriptions.php
subscriptions.unsubscribe - /cc-core/controllers/myaccount/subscriptions.php


subscribers.start - /cc-core/controllers/myaccount/subscribers.php
subscribers.before_render - /cc-core/controllers/myaccount/subscribers.php


message_inbox.start - /cc-core/controllers/myaccount/message_inbox.php
message_inbox.before_render - /cc-core/controllers/myaccount/message_inbox.php
message_inbox.purge_single_message - /cc-core/controllers/myaccount/message_inbox.php
message_inbox.purge_all_messages - /cc-core/controllers/myaccount/message_inbox.php
message_inbox.delete_message - /cc-core/controllers/myaccount/message_inbox.php


message_read.start - /cc-core/controllers/myaccount/message_read.php
message_read.before_render - /cc-core/controllers/myaccount/message_read.php


message_send.start - /cc-core/controllers/myaccount/message_send.php
message_send.before_render - /cc-core/controllers/myaccount/message_send.php
message_send.load_original_message - /cc-core/controllers/myaccount/message_send.php
message_send.before_send_message - /cc-core/controllers/myaccount/message_send.php
message_send.send_message - /cc-core/controllers/myaccount/message_send.php


### ADMIN

admin.plugin_settings.start - /cc-admin/plugins_settings.php
admin.[PLUGIN NAME].before_render - /cc-admin/plugins_settings.php
admin.[PLUGIN NAME].settings - /cc-admin/plugins_settings.php







[ADMIN PANEL FEATURES]

Videos
    Approved Videos
        Edit
        Delete
    Pending Videos
        Approve
        Edit
        Delete
    Processing Videos
        Edit
        Delete
    Banned Videos
        Unban
        Edit
        Delete



Members
    Approved Members
        Edit
        Delete
    Pending Members
        Edit
        Delete
    Banned Members
        Unban
        Edit
        Delete



Comments
    Approved Comments
        Edit
        Delete
    Pending Comments
        Approve
        Edit
        Delete
    Banned Comments
        Unban
        Edit
        Delete
    SPAM Comments
        Edit
        Delete



Flags
    Flagged Videos
        Approve
        Dismiss
    Flagged Member
        Approve
        Dismiss
    Flagged Comments
        Approve
        Dismiss



Pages
    Browse / Search Pages
        Edit Page
        Delete Page
    Add Page



Themes
Plugins
Settings





http://themeforest.net/item/facile-admin/full_screen_preview/141010
http://themeforest.net/item/adminica-the-professional-admin-template/full_screen_preview/160638


JQ Plugins
http://fancybox.net/
http://lab.mattvarone.com/projects/jquery/totop/
http://onehackoranother.com/projects/jquery/tipsy/
http://www.datatables.net/

Buttons, Icons & PSDs
http://www.tricycle.ie/adminica/js/jqueryFileTree/images/spinner.gif
http://www.sketchdock.com/freebies/36-web-buttons-collection.html
http://365psd.com/day/2-37/
http://365psd.com/day/147/








/**********
PLUGIN CODE
**********/

class SamplePlugin {

    /**
     * Attach plugin methods to hooks throughout the codebase. Method is called
     * when the plugin system is initialized (cc-core/config/bootstrap.php).
     *
     * It is recommended you put all your attachment calls here for the sake of
     * keeping your sanity. It is possible to attach to hooks later on
     * within your plugin methods, but at the very least attach your
     * Init/Bootstrap method at this point.
     *
     * @example Plugin::Attach ( 'EVENT_NAME' , array( __CLASS__ , 'METHOD_NAME' ) );
     */
    public function Load() {}




    /**
     * Provide information about the plugin
     * @return array Returns an array with information about the plugin.
     * @example return array ('name' => 'Test Plugin', 'author' => 'CumulusClips.org');
     *      Required items are:
     *          name - Formal name for the plugin
     *      Optional items are:
     *          author - Person or organization who created plugin
     *          version - Version number of the plugin in 3 place format e.g: 5.1.7
     *          notes - Notes about or description of the plugin to the end user
     *          site - URL where more documentation / information about the plugin can be obtained
     *          Any other custom information can be added for internal use
     */
    static function Info() {}




    /**
     * Output settings page for the plugin if applicable
     * @return string Returns the HTML for the settings page of the plugin, If
     * is ommited then no settings page is displayed
     *
     * NOTE: This method is called after headers are sent, you will not be able
     * to modify header information with this method
     */
    static function Settings() {}




    /**
     * Perform additional actions required for plugin installation. This method
     * is called when a plugin is enabled for the first time. This is where you
     * would execute for example any create any database tables or write files, etc.
     *
     * This method is not required and can be ommited. It will only execute if
     * exists during plugin enablement.
     */
    static function Install() {}




    /**
     * Revert any additional actions made by the plugin during it's installation.
     * This method is called when a plugin is deleted. This is where you would
     * for example remove any database tables or delete files, etc.
     *
     * This method is not required and can be ommited. It will only execute if
     * exists during plugin deletion.
     */
    static function Uninstall() {}

}