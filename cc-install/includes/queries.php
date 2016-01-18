<?php

/*Table structure for table `categories` */

$_DROP_CATEGORIES_TABLE = <<<DROP_CATEGORIES_TABLE
DROP TABLE IF EXISTS `{DB_PREFIX}categories`
DROP_CATEGORIES_TABLE;

$_CREATE_CATEGORIES_TABLE = <<<CREATE_CATEGORIES_TABLE

CREATE TABLE `{DB_PREFIX}categories` (
  `category_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(252) NOT NULL,
  `slug` varchar(255) NOT NULL,
  PRIMARY KEY (`category_id`)
) DEFAULT CHARSET=utf8

CREATE_CATEGORIES_TABLE;

$_POPULATE_CATEGORIES_TABLE = <<<POPULATE_CATEGORIES_TABLE
INSERT INTO `{DB_PREFIX}categories` (`name`,`slug`) VALUES ('General','general')
POPULATE_CATEGORIES_TABLE;





/*Table structure for table `comments` */

$_DROP_COMMENTS_TABLE = <<<DROP_COMMENTS_TABLE
DROP TABLE IF EXISTS `{DB_PREFIX}comments`
DROP_COMMENTS_TABLE;

$_CREATE_COMMENTS_TABLE = <<<CREATE_COMMENTS_TABLE

CREATE TABLE `{DB_PREFIX}comments` (
  `comment_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL DEFAULT '0',
  `video_id` bigint(20) NOT NULL,
  `parent_id` bigint(20) DEFAULT '0',
  `comments` longtext NOT NULL,
  `date_created` datetime NOT NULL,
  `status` varchar(255) NOT NULL,
  `released` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`comment_id`)
) DEFAULT CHARSET=utf8

CREATE_COMMENTS_TABLE;





/*Table structure for table `flags` */

$_DROP_FLAGS_TABLE = <<<DROP_FLAGS_TABLE
DROP TABLE IF EXISTS `{DB_PREFIX}flags`
DROP_FLAGS_TABLE;

$_CREATE_FLAGS_TABLE = <<<CREATE_FLAGS_TABLE

CREATE TABLE `{DB_PREFIX}flags` (
  `flag_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `object_id` bigint(20) NOT NULL,
  `type` varchar(255) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `date_created` date NOT NULL,
  `status` varchar(255) DEFAULT 'pending',
  PRIMARY KEY (`flag_id`)
) DEFAULT CHARSET=utf8

CREATE_FLAGS_TABLE;





/*Table structure for table `messages` */

$_DROP_MESSAGES_TABLE = <<<DROP_MESSAGES_TABLE
DROP TABLE IF EXISTS `{DB_PREFIX}messages`
DROP_MESSAGES_TABLE;

$_CREATE_MESSAGES_TABLE = <<<CREATE_MESSAGES_TABLE

CREATE TABLE `{DB_PREFIX}messages` (
  `message_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `recipient` bigint(20) NOT NULL DEFAULT '0',
  `subject` text NOT NULL,
  `message` text NOT NULL,
  `status` varchar(255) NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`message_id`)
) DEFAULT CHARSET=utf8

CREATE_MESSAGES_TABLE;





/*Table structure for table `pages` */

$_DROP_PAGES_TABLE = <<<DROP_PAGES_TABLE
DROP TABLE IF EXISTS `{DB_PREFIX}pages`
DROP_PAGES_TABLE;

$_CREATE_PAGES_TABLE = <<<CREATE_PAGES_TABLE

CREATE TABLE `{DB_PREFIX}pages` (
  `page_id` bigint(11) NOT NULL AUTO_INCREMENT,
  `title` text NOT NULL,
  `content` longtext NOT NULL,
  `slug` text NOT NULL,
  `layout` varchar(255) NOT NULL DEFAULT 'default',
  `date_created` datetime NOT NULL,
  `status` varchar(255) NOT NULL,
  PRIMARY KEY (`page_id`)
) DEFAULT CHARSET=utf8

CREATE_PAGES_TABLE;

$_POPULATE_PAGES_TABLE = <<<POPULATE_PAGES_TABLE
INSERT INTO `{DB_PREFIX}pages`(`page_id`,`title`,`content`,`slug`,`layout`,`date_created`,`status`) values (1,'Sample Page','<p>This is a sample page. You can create custom static pages like this in the Admin Panel.</p>','sample-page','default','2011-09-05 06:28:49','published');
POPULATE_PAGES_TABLE;





/*Table structure for table `files` */

$_DROP_FILES_TABLE = <<<DROP_FILES_TABLE
DROP TABLE IF EXISTS `{DB_PREFIX}files`
DROP_FILES_TABLE;

$_CREATE_FILES_TABLE = <<<CREATE_FILES_TABLE

CREATE TABLE `{DB_PREFIX}files` (
  `file_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `title` text NOT NULL,
  `description` text,
  `filesize` bigint(20) NOT NULL,
  `extension` varchar(255) NOT NULL DEFAULT '',
  `attachable` tinyint(1) NOT NULL DEFAULT '0',
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`file_id`),
  KEY `tenant_id` (`user_id`),
  KEY `user_id` (`user_id`),
  KEY `filename` (`filename`)
) DEFAULT CHARSET=utf8;

CREATE_FILES_TABLE;





/*Table structure for table `playlists` */

$_DROP_PLAYLISTS_TABLE = <<<DROP_PLAYLISTS_TABLE
DROP TABLE IF EXISTS `{DB_PREFIX}playlists`
DROP_PLAYLISTS_TABLE;

$_CREATE_PLAYLISTS_TABLE = <<<CREATE_PLAYLISTS_TABLE

CREATE TABLE `{DB_PREFIX}playlists` (
  `playlist_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(20) NOT NULL DEFAULT 'list',
  `public` tinyint(1) DEFAULT '1',
  `date_created` date NOT NULL,
  PRIMARY KEY (`playlist_id`)
) DEFAULT CHARSET=utf8

CREATE_PLAYLISTS_TABLE;





/*Table structure for table `playlist_entries` */

$_DROP_PLAYLIST_ENTRIES_TABLE = <<<DROP_PLAYLIST_ENTRIES_TABLE
DROP TABLE IF EXISTS `{DB_PREFIX}playlist_entries`
DROP_PLAYLIST_ENTRIES_TABLE;

$_CREATE_PLAYLIST_ENTRIES_TABLE = <<<CREATE_PLAYLIST_ENTRIES_TABLE

CREATE TABLE `{DB_PREFIX}playlist_entries` (
  `playlist_entry_id` int(11) NOT NULL AUTO_INCREMENT,
  `playlist_id` int(11) NOT NULL,
  `video_id` int(11) NOT NULL,
  `date_created` date NOT NULL,
  PRIMARY KEY (`playlist_entry_id`)
) DEFAULT CHARSET=utf8

CREATE_PLAYLIST_ENTRIES_TABLE;





/*Table structure for table `privacy` */

$_DROP_PRIVACY_TABLE = <<<DROP_PRIVACY_TABLE
DROP TABLE IF EXISTS `{DB_PREFIX}privacy`
DROP_PRIVACY_TABLE;

$_CREATE_PRIVACY_TABLE = <<<CREATE_PRIVACY_TABLE

CREATE TABLE `{DB_PREFIX}privacy` (
  `privacy_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `video_comment` tinyint(1) NOT NULL DEFAULT '1',
  `new_message` tinyint(1) NOT NULL DEFAULT '1',
  `new_video` tinyint(1) NOT NULL DEFAULT '1',
  `video_ready` tinyint(1) NOT NULL DEFAULT '1',
  `comment_reply` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`privacy_id`)
) DEFAULT CHARSET=utf8

CREATE_PRIVACY_TABLE;





/*Table structure for table `ratings` */

$_DROP_RATINGS_TABLE = <<<DROP_RATINGS_TABLE
DROP TABLE IF EXISTS `{DB_PREFIX}ratings`
DROP_RATINGS_TABLE;

$_CREATE_RATINGS_TABLE = <<<CREATE_RATINGS_TABLE

CREATE TABLE `{DB_PREFIX}ratings` (
  `rating_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `video_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `date_created` datetime NOT NULL,
  `rating` tinyint(4) NOT NULL,
  PRIMARY KEY (`rating_id`)
) DEFAULT CHARSET=utf8

CREATE_RATINGS_TABLE;





/*Table structure for table `settings` */

$_DROP_SETTINGS_TABLE = <<<DROP_SETTINGS_TABLE
DROP TABLE IF EXISTS `{DB_PREFIX}settings`
DROP_SETTINGS_TABLE;

$_CREATE_SETTINGS_TABLE = <<<CREATE_SETTINGS_TABLE

CREATE TABLE `{DB_PREFIX}settings` (
  `setting_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `value` longtext NOT NULL,
  PRIMARY KEY (`setting_id`)
) DEFAULT CHARSET=utf8

CREATE_SETTINGS_TABLE;

$_POPULATE_SETTINGS_TABLE = <<<POPULATE_SETTINGS_TABLE

INSERT INTO `{DB_PREFIX}settings` (`name`,`value`) VALUES
('active_theme','default'),
('active_mobile_theme','mobile-default'),
('default_language', 'english'),
('active_languages', '[{"system_name":"english","lang_name":"English","native_name":"English"}]'),
('installed_plugins','[]'),
('enabled_plugins','[]'),
('roles','{"admin":{"name":"Administrator","permissions":["admin_panel","manage_settings"]},"mod":{"name":"Moderator","permissions":["admin_panel"]},"user":{"name":"User","permissions":[]}}'),
('debug_conversion','0'),
('video_size_limit','102000000'),
('file_size_limit','102000000'),
('keep_original_video','1'),
('enable_encoding','1'),
('mobile_site','1'),
('h264_encoding_options','-vcodec libx264 -vf "scale=min(640\\\,iw):trunc(ow/a/2)*2" -vb 800k -acodec libvo_aacenc -ab 96k -ar 44100 -f mp4'),
('webm_encoding_enabled','0'),
('webm_encoding_options','-vcodec libvpx -vf "scale=min(640\\\,iw):trunc(ow/a/2)*2" -vb 800k -acodec libvorbis -ab 96k -ar 44100 -f webm'),
('theora_encoding_enabled','0'),
('theora_encoding_options','-vcodec libtheora -vf "scale=min(640\\\,iw):trunc(ow/a/2)*2" -qscale 8 -vb 800k -acodec libvorbis -ab 96k -ar 44100 -f ogg'),
('mobile_encoding_enabled','1'),
('mobile_encoding_options','-vcodec libx264 -vf "scale=min(480\\\,iw):trunc(ow/a/2)*2" -vb 600k -ac 2 -ab 96k -ar 44100 -f mp4'),
('thumb_encoding_options','-vf "scale=min(640\\\,iw):trunc(ow/a/2)*2" -t 1 -r 1 -f mjpeg'),
('auto_approve_users','1'),
('auto_approve_videos','1'),
('auto_approve_comments','1'),
('alerts_videos','1'),
('alerts_comments','1'),
('alerts_users','1'),
('alerts_flags','1'),
('from_name',''),
('from_address',''),
('smtp','{"enabled":false,"host":"","port":25,"username":"","password":""}'),
('user_registrations', '1'),
('user_uploads', '1')

POPULATE_SETTINGS_TABLE;





/*Table structure for table `subscriptions` */

$_DROP_SUBSCRIPTIONS_TABLE = <<<DROP_SUBSCRIPTIONS_TABLE
DROP TABLE IF EXISTS `{DB_PREFIX}subscriptions`
DROP_SUBSCRIPTIONS_TABLE;

$_CREATE_SUBSCRIPTIONS_TABLE = <<<CREATE_SUBSCRIPTIONS_TABLE

CREATE TABLE `{DB_PREFIX}subscriptions` (
  `subscription_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `member` bigint(20) NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`subscription_id`)
) DEFAULT CHARSET=utf8

CREATE_SUBSCRIPTIONS_TABLE;





/*Table structure for table `users` */

$_DROP_USERS_TABLE = <<<DROP_USERS_TABLE
DROP TABLE IF EXISTS `{DB_PREFIX}users`
DROP_USERS_TABLE;

$_CREATE_USERS_TABLE = <<<CREATE_USERS_TABLE

CREATE TABLE `{DB_PREFIX}users` (
  `user_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `username` varchar(80) NOT NULL,
  `email` varchar(80) NOT NULL,
  `password` varchar(32) NOT NULL,
  `status` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'user',
  `date_created` date NOT NULL,
  `first_name` varchar(255),
  `last_name` varchar(255),
  `about_me` text,
  `website` text,
  `confirm_code` varchar(255) DEFAULT NULL,
  `views` bigint(20) DEFAULT '0',
  `last_login` date DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `released` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`),
  KEY `username` (`username`),
  KEY `email` (`email`)
) DEFAULT CHARSET=utf8

CREATE_USERS_TABLE;





/*Table structure for table `videos` */

$_DROP_VIDEOS_TABLE = <<<DROP_VIDEOS_TABLE
DROP TABLE IF EXISTS `{DB_PREFIX}videos`
DROP_VIDEOS_TABLE;

$_CREATE_VIDEOS_TABLE = <<<CREATE_VIDEOS_TABLE

CREATE TABLE `{DB_PREFIX}videos` (
  `video_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `category_id` bigint(20) NOT NULL,
  `title` text NOT NULL,
  `description` text NOT NULL,
  `tags` text NOT NULL,
  `date_created` datetime NOT NULL,
  `duration` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL,
  `views` bigint(20) NOT NULL DEFAULT '0',
  `featured` tinyint(1) NOT NULL DEFAULT '0',
  `original_extension` varchar(255) DEFAULT NULL,
  `job_id` bigint(20) DEFAULT NULL,
  `released` tinyint(1) NOT NULL DEFAULT '0',
  `disable_embed` TINYINT(1) DEFAULT '0' NOT NULL,
  `gated` TINYINT(1) DEFAULT '0' NOT NULL,
  `private` TINYINT(1) DEFAULT '0' NOT NULL,
  `private_url` VARCHAR(255) NULL,
  `comments_closed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`video_id`),
  KEY `user_id` (`user_id`),
  FULLTEXT KEY `title_description_tags` (`title`,`description`,`tags`)
) ENGINE=MYISAM DEFAULT CHARSET=utf8

CREATE_VIDEOS_TABLE;





$install_queries = array (
    $_DROP_CATEGORIES_TABLE,
    $_CREATE_CATEGORIES_TABLE,
    $_POPULATE_CATEGORIES_TABLE,
    $_DROP_COMMENTS_TABLE,
    $_CREATE_COMMENTS_TABLE,
    $_DROP_FLAGS_TABLE,
    $_CREATE_FLAGS_TABLE,
    $_DROP_MESSAGES_TABLE,
    $_CREATE_MESSAGES_TABLE,
    $_DROP_PAGES_TABLE,
    $_CREATE_PAGES_TABLE,
    $_POPULATE_PAGES_TABLE,
    $_DROP_FILES_TABLE,
    $_CREATE_FILES_TABLE,
    $_DROP_PLAYLISTS_TABLE,
    $_CREATE_PLAYLISTS_TABLE,
    $_DROP_PLAYLIST_ENTRIES_TABLE,
    $_CREATE_PLAYLIST_ENTRIES_TABLE,
    $_DROP_PRIVACY_TABLE,
    $_CREATE_PRIVACY_TABLE,
    $_DROP_RATINGS_TABLE,
    $_CREATE_RATINGS_TABLE,
    $_DROP_SETTINGS_TABLE,
    $_CREATE_SETTINGS_TABLE,
    $_POPULATE_SETTINGS_TABLE,
    $_DROP_SUBSCRIPTIONS_TABLE,
    $_CREATE_SUBSCRIPTIONS_TABLE,
    $_DROP_USERS_TABLE,
    $_CREATE_USERS_TABLE,
    $_DROP_VIDEOS_TABLE,
    $_CREATE_VIDEOS_TABLE
);

?>