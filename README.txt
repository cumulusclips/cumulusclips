[PERMISSIONS]

Make sure the following directories are writeable by PHP and the webserver:

/cc-core/log
/cc-content/uploads/temp
/cc-content/uploads/flv
/cc-content/uploads/mp4
/cc-content/uploads/thumbs





[REQUIREMENTS]

System

FFMPEG
MYSQL
APACHE 2.X
    MOD_REWRITE
PHP5
    XML
    ZIP
    CURL
    PHP5-FFMPEG





[META & TITLE TAGS]

http://www.seomoz.org/learn-seo/meta-description





[VIDEO STATUS CODES]

1 - New
2 - Pending Upload
3 - Pending Download
4 - Pending Conversion
5 - Processing
6 - Approved
7 - Banned
8 - MIA





[PLUGIN HOOK LOCATIONS]

### AJAX & SYSTEM FILES

app.start - /cc-core/config/bootstrap.php

username.ajax.start -  - /cc-core/system/username.ajax.php


flag.ajax.start - /cc-core/system/flag.ajax.php
flag.ajax.login_check - /cc-core/system/flag.ajax.php
flag.ajax.flag_video - /cc-core/system/flag.ajax.php
flag.ajax.flag_member - /cc-core/system/flag.ajax.php
flag.ajax.flag_comment - /cc-core/system/flag.ajax.php


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


### CLASS LIBRARY

user.get - /cc-core/lib/User.php
user.create - /cc-core/lib/User.php
user.update - /cc-core/lib/User.php
user.delete - /cc-core/lib/User.php
user.login - /cc-core/lib/User.php
user.logout - /cc-core/lib/User.php
user.reset_password - /cc-core/lib/User.php
user.activate - /cc-core/lib/User.php


video.get - /cc-core/lib/Video.php
video.create - /cc-core/lib/Video.php
video.update - /cc-core/lib/Video.php
video.delete - /cc-core/lib/Video.php


comment.get - /cc-core/lib/Comment.php
comment.create - /cc-core/lib/Comment.php
comment.update - /cc-core/lib/Comment.php
comment.delete - /cc-core/lib/Comment.php


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


picture.before_save - /cc-core/lib/Picture.php
picture.save - /cc-core/lib/Picture.php


pagination.start - /cc-core/lib/Pagination.php
pagination.paginate - /cc-core/lib/Pagination.php


view.init - /cc-core/lib/View.php
view.load_page - /cc-core/lib/View.php
view.render - /cc-core/lib/View.php
view.set_layout - /cc-core/lib/View.php
view.header - /cc-core/lib/View.php
view.footer - /cc-core/lib/View.php
view.body - /cc-core/lib/View.php
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


system_404.start - /cc-core/controllers/system_404.php
system_404.before_render - /cc-core/controllers/system_404.php


system_error.start - /cc-core/controllers/system_error.php
system_error.before_render - /cc-core/controllers/system_error.php


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
update_profile.update_picture - /cc-core/controllers/myaccount/update_profile.php
update_profile.reset_picture - /cc-core/controllers/myaccount/update_profile.php


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





[POSSIBLE HOOKS]

NOTES:
- Before Updates occur right before query
- Updates occur after update query at last possible location
- Deletes / Removals occur after delete at last possible location
- Before Additons occur right before query
- Additions occur after create at last possible location


upload video
    after upload
    validate video
    move to temp
grab video
    verify video
    before download
    after download
encoding
    before encode
    after encode
    before move
    after move