[PERMISSIONS]

Make sure the following directories are writeable by PHP and the webserver:

/cc-core/log
/cc-content/uploads/temp
/cc-content/uploads/flv
/cc-content/uploads/mp4
/cc-content/uploads/thumbs


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

app.start - /cc-core/config/bootstrap.php


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


index.start - /cc-core/controllers/index.php
index.pre_render - /cc-core/controllers/index.php

videos.start - /cc-core/controllers/videos.php
videos.pre_render - /cc-core/controllers/videos.php

members.start - /cc-core/controllers/members.php
members.pre_render - /cc-core/controllers/members.php

member_videos.start - /cc-core/controllers/member_videos.php
member_videos.pre_render - /cc-core/controllers/member_videos.php

profile.start - /cc-core/controllers/profile.php
profile.pre_render - /cc-core/controllers/profile.php

play.start - /cc-core/controllers/play.php
play.pre_render - /cc-core/controllers/play.php

comments.start - /cc-core/controllers/comments.php
comments.pre_render - /cc-core/controllers/comments.php

contact.start - /cc-core/controllers/contact.php
contact.pre_render - /cc-core/controllers/contact.php

activate.start - /cc-core/controllers/activate.php
activate.pre_render - /cc-core/controllers/activate.php

opt_out.start - /cc-core/controllers/opt_out.php
opt_out.pre_render - /cc-core/controllers/opt_out.php

search.start - /cc-core/controllers/search.php
search.pre_render - /cc-core/controllers/search.php

login.start - /cc-core/controllers/login.php
login.pre_render - /cc-core/controllers/login.php

register.start - /cc-core/controllers/register.php
register.pre_render - /cc-core/controllers/register.php

system_404.start - /cc-core/controllers/system_404.php
system_404.pre_render - /cc-core/controllers/system_404.php

system_error.start - /cc-core/controllers/system_error.php
system_error.pre_render - /cc-core/controllers/system_error.php


myaccount.start - /cc-core/controllers/myaccount/myaccount.php
myaccount.pre_render - /cc-core/controllers/myaccount/myaccount.php

upload.start - /cc-core/controllers/myaccount/upload.php
upload.pre_render - /cc-core/controllers/myaccount/upload.php

upload_video.start - /cc-core/controllers/myaccount/upload_video.php
upload_video.pre_render - /cc-core/controllers/myaccount/upload_video.php

upload_complete.start - /cc-core/controllers/myaccount/upload_complete.php
upload_complete.pre_render - /cc-core/controllers/myaccount/upload_complete.php

edit_video.start - /cc-core/controllers/myaccount/edit_video.php
edit_video.pre_render - /cc-core/controllers/myaccount/edit_video.php

myvideos.start - /cc-core/controllers/myaccount/myvideos.php
myvideos.pre_render - /cc-core/controllers/myaccount/myvideos.php

myfavorites.start - /cc-core/controllers/myaccount/myfavorites.php
myfavorites.pre_render - /cc-core/controllers/myaccount/myfavorites.php

update_profile.start - /cc-core/controllers/myaccount/update_profile.php
update_profile.pre_render - /cc-core/controllers/myaccount/update_profile.php

privacy_settings.start - /cc-core/controllers/myaccount/privacy_settings.php
privacy_settings.pre_render - /cc-core/controllers/myaccount/privacy_settings.php

subscriptions.start - /cc-core/controllers/myaccount/subscriptions.php
subscriptions.pre_render - /cc-core/controllers/myaccount/subscriptions.php

subscribers.start - /cc-core/controllers/myaccount/subscribers.php
subscribers.pre_render - /cc-core/controllers/myaccount/subscribers.php

message_inbox.start - /cc-core/controllers/myaccount/message_inbox.php
message_inbox.pre_render - /cc-core/controllers/myaccount/message_inbox.php

message_read.start - /cc-core/controllers/myaccount/message_read.php
message_read.pre_render - /cc-core/controllers/myaccount/message_read.php

message_send.start - /cc-core/controllers/myaccount/message_send.php
message_send.pre_render - /cc-core/controllers/myaccount/message_send.php




[POSSIBLE HOOKS]

404
system error
opt out
contact
pagination
	load
	output
search
play
	load video
	load suggestions
	load comments
profile
	load member
	load recent videos
	load recent updates


register
login
logout
activate
flag
rate
comment
post update
favorite
subscribe