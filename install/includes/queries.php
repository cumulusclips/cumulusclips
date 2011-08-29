<?php

/*Table structure for table `categories` */

$_DROP_CATEGORIES_TABLE = <<<DROP_CATEGORIES_TABLE
DROP TABLE IF EXISTS `{DB_PREFIX}categories`
DROP_CATEGORIES_TABLE;

$_CREATE_CATEGORIES_TABLE = <<<CREATE_CATEGORIES_TABLE

CREATE TABLE `{DB_PREFIX}categories` (
  `cat_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `cat_name` varchar(250) NOT NULL,
  `cat_description` text,
  `date_created` date NOT NULL,
  PRIMARY KEY (`cat_id`),
  UNIQUE KEY `channel_id` (`cat_id`)
) DEFAULT CHARSET=utf8

CREATE_CATEGORIES_TABLE;

$_POPULATE_CATEGORIES_TABLE = <<<POPULATE_CATEGORIES_TABLE
INSERT INTO `{DB_PREFIX}categories` (`cat_name`) VALUES ('General')
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
  `comments` text NOT NULL,
  `date_created` datetime NOT NULL,
  `status` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `ip` varchar(255) DEFAULT NULL,
  `released` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`comment_id`),
  UNIQUE KEY `indexer` (`comment_id`)
) DEFAULT CHARSET=utf8

CREATE_COMMENTS_TABLE;





/*Table structure for table `favorites` */

$_DROP_FAVORITES_TABLE = <<<DROP_FAVORITES_TABLE
DROP TABLE IF EXISTS `{DB_PREFIX}favorites`
DROP_FAVORITES_TABLE;

$_CREATE_FAVORITES_TABLE = <<<CREATE_FAVORITES_TABLE

CREATE TABLE `{DB_PREFIX}favorites` (
  `fav_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `video_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `date_created` date NOT NULL,
  PRIMARY KEY (`fav_id`)
) DEFAULT CHARSET=utf8

CREATE_FAVORITES_TABLE;





/*Table structure for table `flags` */

$_DROP_FLAGS_TABLE = <<<DROP_FLAGS_TABLE
DROP TABLE IF EXISTS `{DB_PREFIX}flags`
DROP_FLAGS_TABLE;

$_CREATE_FLAGS_TABLE = <<<CREATE_FLAGS_TABLE

CREATE TABLE `{DB_PREFIX}flags` (
  `flag_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `id` bigint(20) NOT NULL,
  `type` varchar(255) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `date_created` date NOT NULL,
  `status` varchar(255) DEFAULT 'pending',
  PRIMARY KEY (`flag_id`),
  UNIQUE KEY `indexer` (`flag_id`)
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





/*Table structure for table `posts` */

$_DROP_POSTS_TABLE = <<<DROP_POSTS_TABLE
DROP TABLE IF EXISTS `{DB_PREFIX}posts`
DROP_POSTS_TABLE;

$_CREATE_POSTS_TABLE = <<<CREATE_POSTS_TABLE

CREATE TABLE `{DB_PREFIX}posts` (
  `post_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `post` longtext NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`post_id`)
) DEFAULT CHARSET=utf8

CREATE_POSTS_TABLE;





/*Table structure for table `privacy` */

$_DROP_PRIVACY_TABLE = <<<DROP_PRIVACY_TABLE
DROP TABLE IF EXISTS `{DB_PREFIX}privacy`
DROP_PRIVACY_TABLE;

$_CREATE_PRIVACY_TABLE = <<<CREATE_PRIVACY_TABLE

CREATE TABLE `{DB_PREFIX}privacy` (
  `privacy_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `video_comment` varchar(100) NOT NULL DEFAULT 'yes',
  `new_message` varchar(100) NOT NULL DEFAULT 'yes',
  `newsletter` varchar(100) NOT NULL DEFAULT 'yes',
  `new_video` varchar(100) NOT NULL DEFAULT 'yes',
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
('active_theme','cumulus'),
('active_mobile_theme','mobile'),
('default_language', 'english'),
('active_languages', 'a:2:{i:0;s:7:"english";i:1;s:19:"chinese_traditional";}'),
('installed_plugins','a:1:{i:0;s:10:"HelloWorld";}'),
('enabled_plugins','a:1:{i:0;s:10:"HelloWorld";}'),
('auto_approve_users','1'),
('auto_approve_videos','1'),
('auto_approve_comments','1'),
('debug_conversion','0'),
('live','true'),
('video_size_limit','102000000'),
('pagination_page_limit','9'),
('h264_url',''),
('theora_url',''),
('mobile_url',''),
('thumb_url',''),
('h264_options','-vcodec libx264 -b 1600k -acodec libfaac -ac 2 -ab 128k -ar 44100 -f mp4'),
('theora_options','-vcodec libtheora -b 1600k -acodec libvorbis -ac 2 -ab 128k -ar 44100 -f ogg'),
('mobile_options','-vf scale=480:-1 -vcodec libx264 -x264opts level=30:cabac=0:bframes=0:weightb=0:weightp=0:8x8dct=0 -b 1000k -acodec libfaac -ac 2 -ab 96k -ar 44100 -f mp4'),
('thumb_options','-vf scale=640:-1 -t 1 -r 1 -f mjpeg'),
('alerts_videos','1'),
('alerts_comments','1'),
('alerts_ratings','1'),
('alerts_users','1'),
('from_name',''),
('from_address',''),
('smtp','O:8:"stdClass":5:{s:7:"enabled";s:1:"0";s:4:"host";s:0:"";s:4:"port";i:25;s:8:"username";s:0:"";s:8:"password";s:0:"";}')

POPULATE_SETTINGS_TABLE;





/*Table structure for table `subscriptions` */

$_DROP_SUBSCRIPTIONS_TABLE = <<<DROP_SUBSCRIPTIONS_TABLE
DROP TABLE IF EXISTS `{DB_PREFIX}subscriptions`
DROP_SUBSCRIPTIONS_TABLE;

$_CREATE_SUBSCRIPTIONS_TABLE = <<<CREATE_SUBSCRIPTIONS_TABLE

CREATE TABLE `{DB_PREFIX}subscriptions` (
  `sub_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `member` bigint(20) NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`sub_id`)
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
  `password` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `date_created` date NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `about_me` text,
  `website` text,
  `confirm_code` varchar(255) DEFAULT NULL,
  `views` bigint(20) DEFAULT '0',
  `last_login` date DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `admin` tinyint(1) NOT NULL DEFAULT '0',
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
  `cat_id` bigint(20) NOT NULL,
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
    $_DROP_FAVORITES_TABLE,
    $_CREATE_FAVORITES_TABLE,
    $_DROP_FLAGS_TABLE,
    $_CREATE_FLAGS_TABLE,
    $_DROP_MESSAGES_TABLE,
    $_CREATE_MESSAGES_TABLE,
    $_DROP_PAGES_TABLE,
    $_CREATE_PAGES_TABLE,
    $_DROP_POSTS_TABLE,
    $_CREATE_POSTS_TABLE,
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