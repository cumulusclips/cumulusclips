/*
SQLyog Community v8.63 
MySQL - 5.1.54-1ubuntu4 : Database - cumulus-dump
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`cumulus-dump` /*!40100 DEFAULT CHARACTER SET latin1 */;

USE `cumulus-dump`;

/*Table structure for table `categories` */

DROP TABLE IF EXISTS `categories`;

CREATE TABLE `categories` (
  `cat_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `cat_name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  PRIMARY KEY (`cat_id`),
  UNIQUE KEY `channel_id` (`cat_id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

/*Data for the table `categories` */

insert  into `categories`(`cat_id`,`cat_name`,`slug`) values (1,'General','general'),(9,'Horror','horror'),(8,'Alien','alien'),(5,'Action','action'),(6,'Sports','sports'),(7,'Animated','animated'),(10,'Comedy','comedy');

/*Table structure for table `comments` */

DROP TABLE IF EXISTS `comments`;

CREATE TABLE `comments` (
  `comment_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL DEFAULT '0',
  `video_id` bigint(20) NOT NULL,
  `comments` longtext NOT NULL,
  `date_created` datetime NOT NULL,
  `status` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `ip` varchar(255) DEFAULT NULL,
  `user_agent` longtext,
  `released` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`comment_id`),
  UNIQUE KEY `indexer` (`comment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `comments` */

/*Table structure for table `favorites` */

DROP TABLE IF EXISTS `favorites`;

CREATE TABLE `favorites` (
  `fav_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `video_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `date_created` date NOT NULL,
  PRIMARY KEY (`fav_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `favorites` */

/*Table structure for table `flags` */

DROP TABLE IF EXISTS `flags`;

CREATE TABLE `flags` (
  `flag_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `id` bigint(20) NOT NULL,
  `type` varchar(255) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `date_created` date NOT NULL,
  `status` varchar(255) DEFAULT 'pending',
  PRIMARY KEY (`flag_id`),
  UNIQUE KEY `indexer` (`flag_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `flags` */

/*Table structure for table `messages` */

DROP TABLE IF EXISTS `messages`;

CREATE TABLE `messages` (
  `message_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `recipient` bigint(20) NOT NULL DEFAULT '0',
  `subject` text NOT NULL,
  `message` text NOT NULL,
  `status` varchar(255) NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`message_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `messages` */

/*Table structure for table `pages` */

DROP TABLE IF EXISTS `pages`;

CREATE TABLE `pages` (
  `page_id` bigint(11) NOT NULL AUTO_INCREMENT,
  `title` text NOT NULL,
  `content` longtext NOT NULL,
  `slug` text NOT NULL,
  `layout` varchar(255) NOT NULL DEFAULT 'default',
  `date_created` datetime NOT NULL,
  `status` varchar(255) NOT NULL,
  PRIMARY KEY (`page_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

/*Data for the table `pages` */

insert  into `pages`(`page_id`,`title`,`content`,`slug`,`layout`,`date_created`,`status`) values (1,'Test page','','test-page','default','2011-06-21 07:05:01','Test page'),(2,'Terms of Use','<div class=\"long-block\">\r\n<ul id=\"top-level\" class=\"list-push\">\r\n<li class=\"terms-item\">\r\n<h2>Your Acceptance</h2>\r\n<ul class=\"mid-level\">\r\n<li>By using and/or visiting this website (collectively, including all content and functionality available through the TechieVideos.com domain name, the \"TechieVideos Website\", or \"Website\"), you signify your agreement to (1) these terms and conditions (the \"Terms of Use\"), (2)TechieVideos&rsquo; privacy policy, found at <a href=\"http://www.techievideos.com/privacy/\">http://www.TechieVideos.com/privacy</a> and incorporated here by reference, and (3) TechieVideos&rsquo; Copyright Policy, found at <a href=\"http://www.techievideos.com/copyright/\">http://www.TechieVideos.com/copyright</a> and also incorporated here by reference. If you do not agree to any of these terms, the TechieVideos privacy policy, or the TechieVideos Copyright Policy, please do not use the TechieVideos Website.</li>\r\n<li>Although we may attempt to notify you when major changes are made to these Terms of Use, you should periodically review the most up-to-date version <a href=\"http://www.techievideos.com/terms/\">http://www.TechieVideos.com/terms</a>. TechieVideos may, in its sole discretion, modify or revise these Terms of Use and policies at any time, and you agree to be bound by such modifications or revisions. Nothing in this Agreement shall be deemed to confer any third-party rights or benefits.</li>\r\n</ul>\r\n</li>\r\n<li class=\"terms-item\">\r\n<h2>Ability to Accept Terms of Use</h2>\r\n<ul class=\"mid-level\">\r\n<li>You affirm that you are either more than 18 years of age, or an emancipated minor, or possess legal parental or guardian consent, and are fully able and competent to enter into the terms, conditions, obligations, affirmations, representations, and warranties set forth in these Terms of Use, and to abide by and comply with these Terms of Use. In any case, you affirm that you are over the age of 13, as the TechieVideos Website is not intended for children under 13. If you are under 13 years of age, then please do not use the TechieVideos Website. There are lots of other great web sites for you. Talk to your parents about what sites are appropriate for you.</li>\r\n</ul>\r\n</li>\r\n<li class=\"terms-item\">\r\n<h2>TechieVideos Website</h2>\r\n<ul class=\"mid-level\">\r\n<li>These Terms of Use apply to all users of the TechieVideos Website, including users who are also contributors of video content, information, and other materials or services on the Website. The TechieVideos Website includes all aspects of TechieVideos, including but not limited to all products, software and services offered via the website such as the TechieVideos channels,&nbsp;the TechieVideos \"Embeddable Player,\" and other applications.</li>\r\n<li>The TechieVideos Website may contain links to third party websites that are not owned or controlled by TechieVideos. TechieVideos has no control over, and assumes no responsibility for, the content, privacy policies, or practices of any third party websites. In addition, TechieVideos will not and cannot censor or edit the content of any third-party site. By using the Website, you expressly relieve TechieVideos from any and all liability arising from your use of any third-party website.</li>\r\n<li>Accordingly, we encourage you to be aware when you leave the TechieVideos Website and to read the terms and conditions and privacy policy of each other website that you visit.</li>\r\n</ul>\r\n</li>\r\n<li class=\"terms-item\">\r\n<h2>TechieVideos Accounts</h2>\r\n<ul class=\"mid-level\">\r\n<li>In order to access some features of the Website, you will have to create a TechieVideos account. You may never use another\'s account without permission. When creating your account, you must provide accurate and complete information. You are solely responsible for the activity that occurs on your account, and you must keep your account password secure. You must notify TechieVideos immediately of any breach of security or unauthorized use of your account.</li>\r\n<li>Although TechieVideos will not be liable for your losses caused by any unauthorized use of your account, you may be liable for the losses of TechieVideos or others due to such unauthorized use.</li>\r\n</ul>\r\n</li>\r\n<li class=\"terms-item\">\r\n<h2>Account Termination Policy</h2>\r\n<ul class=\"mid-level\">\r\n<li>TechieVideos reserves the right to terminate a user\'s account without notice if, under appropriate circumstances, the user is determined, in TechieVideos&rsquo; sole discretion, to be a repeat infringer of these Terms of Use.</li>\r\n<li>TechieVideos reserves the right to decide whether Content (defined below) or a User Submission (defined below) is appropriate and complies with these Terms of Use for violations other than copyright infringement, such as, but not limited to, pornography, obscene or defamatory material, or excessive length. TechieVideos may remove such User Submissions (defined below) and/or terminate a User\'s access for uploading such material in violation of these Terms of Use at any time, without prior notice and at its sole discretion.</li>\r\n</ul>\r\n</li>\r\n<li class=\"terms-item\">\r\n<h2>Your User Submissions and Conduct</h2>\r\n<ul class=\"mid-level\">\r\n<li>As a TechieVideos account holder you may submit video content (\"User Videos\") and textual content (\"User Comments\"). User Videos and User Comments are collectively referred to as \"User Submissions.\" You understand that whether or not such User Submissions are published, TechieVideos does not guarantee any confidentiality with respect to any User Submissions.</li>\r\n<li>You shall be solely responsible for your own User Submissions and the consequences of posting or publishing them. In connection with User Submissions, you affirm, represent, and/or warrant that: you own or have the necessary licenses, rights, consents, and permissions to use and authorize TechieVideos to use all patent, trademark, trade secret, copyright or other proprietary rights in and to any and all User Submissions to enable inclusion and use of the User Submissions in the manner contemplated by the Website and these Terms of Use.</li>\r\n<li>For clarity, you retain all of your ownership rights in your User Submissions. However, by submitting User Submissions to TechieVideos, you hereby grant TechieVideos a worldwide, non-exclusive, royalty-free, sublicenseable and transferable license to use, reproduce, distribute, prepare derivative works of, display, and perform the User Submissions in connection with the TechieVideos Website and TechieVideos\' (and its successors\' and affiliates\') business, including without limitation for promoting and redistributing part or all of the TechieVideos Website (and derivative works thereof) in any media formats and through any media channels. You also hereby grant each user of the TechieVideos Website a non-exclusive license to access your User Submissions through the Website, and to use, reproduce, distribute, display and perform such User Submissions as permitted through the functionality of the Website and under these Terms of Use. The above licenses granted by you in User Videos terminate within a commercially reasonable time after you remove or delete your User Videos from the TechieVideos Service. You understand and agree, however, that TechieVideos may retain, but not display, distribute, or perform, server copies of User Submissions that have been removed or deleted. The above licenses granted by you in User Comments are perpetual and irrevocable.</li>\r\n<li>In connection with User Submissions, you further agree that you will not submit material that is copyrighted, protected by trade secret or otherwise subject to third party proprietary rights, including privacy and publicity rights, unless you are the owner of such rights or have permission from their rightful owner to post the material and to grant TechieVideos all of the license rights granted herein.</li>\r\n<li>You further agree that you will not, in connection with User Submissions, submit material that is contrary to these Terms of Use or the TechieVideos Copyright Policy, found at <a href=\"http://www.techievideos.com/copyright/\">http://www.TechieVideos.com/copyright</a>, which may be updated from time to time, or contrary to applicable local, national, and international laws and regulations.</li>\r\n<li>TechieVideos does not endorse any User Submission or any opinion, recommendation, or advice expressed therein, and TechieVideos expressly disclaims any and all liability in connection with User Submissions. TechieVideos does not permit copyright infringing activities and infringement of intellectual property rights on its Website, and TechieVideos will remove all Content (defined below) and User Submissions if properly notified that such Content (defined below) or User Submission infringes on another\'s intellectual property rights. TechieVideos reserves the right to remove Content (defined below) and User Submissions without prior notice.</li>\r\n</ul>\r\n</li>\r\n<li class=\"terms-item\">\r\n<h2>General Use of the Website&mdash;Permissions and Restrictions</h2>\r\n<p>TechieVideos hereby grants you permission to access and use the Website as set forth in these Terms of Use, provided that:</p>\r\n<br />\r\n<ul class=\"mid-level\">\r\n<li>You agree not to distribute in any medium any part of the Website, including but not limited to User Submissions, without TechieVideos\' prior written authorization.</li>\r\n<li>You agree not to alter or modify any part of the Website, including but not limited to TechieVideos\' Embeddable Player or any of its related technologies.</li>\r\n<li>You agree not to access User Submissions or TechieVideos Content (defined below) through any technology or means other than the video playback pages of the Website itself, the TechieVideos Embeddable Player, or other explicitly authorized means TechieVideos may designate.</li>\r\n<li>You agree not to use the Website, including the TechieVideos Embeddable Player for any commercial use, without the prior written authorization of TechieVideos. Prohibited commercial uses include any of the following actions taken without TechieVideos&rsquo; express approval:\r\n<ul class=\"lower-level\">\r\n<li>sale of access to the Website or its related services (such as the Embeddable Player) on another website;</li>\r\n<li>use of the Website or its related services (such as the Embeddable Player), for the primary purpose of gaining advertising or subscription revenue;</li>\r\n<li>the sale of advertising, on the TechieVideos website or any third-party website, targeted to the content of specific User Submissions or TechieVideos content;</li>\r\n<li>and any use of the Website or its related services (such as the Embeddable player) that TechieVideos finds, in its sole discretion, to use TechieVideos&rsquo; resources or User Submissions with the effect of competing with or displacing the market for TechieVideos, TechieVideos content, or its User Submissions.</li>\r\n</ul>\r\n</li>\r\n<li>Prohibited commercial uses do not include:\r\n<ul class=\"lower-level\">\r\n<li>uploading an original video to TechieVideos, or maintaining an original channel on TechieVideos, to promote your business or artistic enterprise;</li>\r\n<li>using the Embeddable Player to show TechieVideos videos on an ad-enabled blog or website, provided the primary purpose of using the Embeddable Player is not to gain advertising revenue or compete with TechieVideos;</li>\r\n<li>any use that TechieVideos expressly authorizes in writing.</li>\r\n</ul>\r\n</li>\r\n<li>If you use the TechieVideos Embeddable Player on your website, you must include a prominent link back to the TechieVideos website on the pages containing the Embeddable Player and you may not modify, build upon, or block any portion of the Embeddable Player in any way.</li>\r\n<li>You agree not to use or launch any automated system, including without limitation, \"robots,\" \"spiders,\" or \"offline readers,\" that accesses the Website in a manner that sends more request messages to the TechieVideos servers in a given period of time than a human can reasonably produce in the same period by using a conventional on-line web browser. Notwithstanding the foregoing, TechieVideos grants the operators of public search engines permission to use spiders to copy materials from the site for the sole purpose of and solely to the extent necessary for creating publicly available searchable indices of the materials, but not caches or archives of such materials. TechieVideos reserves the right to revoke these exceptions either generally or in specific cases. You agree not to collect or harvest any personally identifiable information, including account names, from the Website, nor to use the communication systems provided by the Website (e.g. comments, email) for any commercial solicitation purposes. You agree not to solicit, for commercial purposes, any users of the Website with respect to their User Submissions.</li>\r\n<li>In your use of the website, you will otherwise comply with the terms and conditions of these Terms of Use, TechieVideos Copyright Policy, and all applicable local, national, and international laws and regulations.</li>\r\n<li>TechieVideos reserves the right to discontinue any aspect of the TechieVideos Website at any time.</li>\r\n</ul>\r\n</li>\r\n<li class=\"terms-item\">\r\n<h2>Your Use of Content on the Site</h2>\r\n<p>In addition to the general restrictions above, the following restrictions and conditions apply specifically to your use of content on the TechieVideos Website.</p>\r\n<br />\r\n<ul class=\"mid-level\">\r\n<li>The content on the TechieVideos Website, except all User Submissions, including without limitation, the text, software, scripts, graphics, photos, sounds, music, videos, interactive features and the like (\"Content\") and the trademarks, service marks and logos contained therein (\"Marks\"), are owned by or licensed to TechieVideos, subject to copyright and other intellectual property rights under the law. Content on the Website is provided to you AS IS for your information and personal use only and may not be downloaded, copied, reproduced, distributed, transmitted, broadcast, displayed, sold, licensed, or otherwise exploited for any other purposes whatsoever without the prior written consent of the respective owners. TechieVideos reserves all rights not expressly granted in and to the Website and the Content.</li>\r\n<li>You may access User Submissions solely:\r\n<ul class=\"lower-level\">\r\n<li>for your information and personal use;</li>\r\n<li>as intended through the normal functionality of the TechieVideos Service;</li>\r\n<li>and for Streaming.</li>\r\n</ul>\r\n<p>\"Streaming\" means a contemporaneous digital transmission of an audiovisual work via the Internet from the TechieVideos Service to a user\'s device in such a manner that the data is intended for real-time viewing and not intended to be copied, stored, permanently downloaded, or redistributed by the user. Accessing User Videos for any purpose or in any manner other than Streaming is expressly prohibited. User Videos are made available \"as is.\"</p>\r\n</li>\r\n<li>User Comments are made available to you for your information and personal use solely as intended through the normal functionality of the TechieVideos Service. User Comments are made available \"as is\", and may not be used, copied, reproduced, distributed, transmitted, broadcast, displayed, sold, licensed, downloaded, or otherwise exploited in any manner not intended by the normal functionality of the TechieVideos Service or otherwise as prohibited under this Agreement.</li>\r\n<li>You may access TechieVideos Content, User Submissions and other content only as permitted under this Agreement. TechieVideos reserves all rights not expressly granted in and to the TechieVideos Content and the TechieVideos Service.</li>\r\n<li>You agree to not engage in the use, copying, or distribution of any of the Content other than expressly permitted herein, including any use, copying, or distribution of User Submissions of third parties obtained through the Website for any commercial purposes.</li>\r\n<li>You agree not to circumvent, disable or otherwise interfere with security-related features of the TechieVideos Website or features that prevent or restrict use or copying of any Content or enforce limitations on use of the TechieVideos Website or the Content therein.</li>\r\n<li>You understand that when using the TechieVideos Website, you will be exposed to User Submissions from a variety of sources, and that TechieVideos is not responsible for the accuracy, usefulness, safety, or intellectual property rights of or relating to such User Submissions. You further understand and acknowledge that you may be exposed to User Submissions that are inaccurate, offensive, indecent, or objectionable, and you agree to waive, and hereby do waive, any legal or equitable rights or remedies you have or may have against TechieVideos with respect thereto, and agree to indemnify and hold TechieVideos, its Owners/Operators, affiliates, and/or licensors, harmless to the fullest extent allowed by law regarding all matters related to your use of the site.</li>\r\n</ul>\r\n</li>\r\n<li class=\"terms-item\">\r\n<h2>Warranty Disclaimer</h2>\r\n<p>YOU AGREE THAT YOUR USE OF THE TECHIEVIDEOS WEBSITE SHALL BE AT YOUR SOLE RISK. TO THE FULLEST EXTENT PERMITTED BY LAW, TECHIEVIDEOS, ITS OFFICERS, DIRECTORS, EMPLOYEES, AND AGENTS DISCLAIM ALL WARRANTIES, EXPRESS OR IMPLIED, IN CONNECTION WITH THE WEBSITE AND YOUR USE THEREOF. TECHIEVIDEOS MAKES NO WARRANTIES OR REPRESENTATIONS ABOUT THE ACCURACY OR COMPLETENESS OF THIS SITE\'S CONTENT OR THE CONTENT OF ANY SITES LINKED TO THIS SITE AND ASSUMES NO LIABILITY OR RESPONSIBILITY FOR ANY (I) ERRORS, MISTAKES, OR INACCURACIES OF CONTENT, (II) PERSONAL INJURY OR PROPERTY DAMAGE, OF ANY NATURE WHATSOEVER, RESULTING FROM YOUR ACCESS TO AND USE OF OUR WEBSITE, (III) ANY UNAUTHORIZED ACCESS TO OR USE OF OUR SECURE SERVERS AND/OR ANY AND ALL PERSONAL INFORMATION AND/OR FINANCIAL INFORMATION STORED THEREIN, (IV) ANY INTERRUPTION OR CESSATION OF TRANSMISSION TO OR FROM OUR WEBSITE, (IV) ANY BUGS, VIRUSES, TROJAN HORSES, OR THE LIKE WHICH MAY BE TRANSMITTED TO OR THROUGH OUR WEBSITE BY ANY THIRD PARTY, AND/OR (V) ANY ERRORS OR OMISSIONS IN ANY CONTENT OR FOR ANY LOSS OR DAMAGE OF ANY KIND INCURRED AS A RESULT OF THE USE OF ANY CONTENT POSTED, EMAILED, TRANSMITTED, OR OTHERWISE MADE AVAILABLE VIA THE TECHIEVIDEOS WEBSITE. TECHIEVIDEOS DOES NOT WARRANT, ENDORSE, GUARANTEE, OR ASSUME RESPONSIBILITY FOR ANY PRODUCT OR SERVICE ADVERTISED OR OFFERED BY A THIRD PARTY THROUGH THE TECHIEVIDEOS WEBSITE OR ANY HYPERLINKED WEBSITE OR FEATURED IN ANY BANNER OR OTHER ADVERTISING, AND TECHIEVIDEOS WILL NOT BE A PARTY TO OR IN ANY WAY BE RESPONSIBLE FOR MONITORING ANY TRANSACTION BETWEEN YOU AND THIRD-PARTY PROVIDERS OF PRODUCTS OR SERVICES. AS WITH THE PURCHASE OF A PRODUCT OR SERVICE THROUGH ANY MEDIUM OR IN ANY ENVIRONMENT, YOU SHOULD USE YOUR BEST JUDGMENT AND EXERCISE CAUTION WHERE APPROPRIATE.</p>\r\n</li>\r\n<li class=\"terms-item\">\r\n<h2>Limitation of Liability</h2>\r\n<p>IN NO EVENT SHALL TECHIEVIDEOS, ITS PARENT COMPANIES, OFFICERS, DIRECTORS, EMPLOYEES, OR AGENTS, BE LIABLE TO YOU FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, PUNITIVE, OR CONSEQUENTIAL DAMAGES WHATSOEVER RESULTING FROM ANY (I) ERRORS, MISTAKES, OR INACCURACIES OF CONTENT, (II) PERSONAL INJURY OR PROPERTY DAMAGE, OF ANY NATURE WHATSOEVER, RESULTING FROM YOUR ACCESS TO AND USE OF OUR WEBSITE, (III) ANY UNAUTHORIZED ACCESS TO OR USE OF OUR SECURE SERVERS AND/OR ANY AND ALL PERSONAL INFORMATION AND/OR FINANCIAL INFORMATION STORED THEREIN, (IV) ANY INTERRUPTION OR CESSATION OF TRANSMISSION TO OR FROM OUR WEBSITE, (IV) ANY BUGS, VIRUSES, TROJAN HORSES, OR THE LIKE, WHICH MAY BE TRANSMITTED TO OR THROUGH OUR WEBSITE BY ANY THIRD PARTY, AND/OR (V) ANY ERRORS OR OMISSIONS IN ANY CONTENT OR FOR ANY LOSS OR DAMAGE OF ANY KIND INCURRED AS A RESULT OF YOUR USE OF ANY CONTENT POSTED, EMAILED, TRANSMITTED, OR OTHERWISE MADE AVAILABLE VIA THE TECHIEVIDEOS WEBSITE, WHETHER BASED ON WARRANTY, CONTRACT, TORT, OR ANY OTHER LEGAL THEORY, AND WHETHER OR NOT THE COMPANY IS ADVISED OF THE POSSIBILITY OF SUCH DAMAGES. THE FOREGOING LIMITATION OF LIABILITY SHALL APPLY TO THE FULLEST EXTENT PERMITTED BY LAW IN THE APPLICABLE JURISDICTION. <br /> YOU SPECIFICALLY ACKNOWLEDGE THAT TECHIEVIDEOS SHALL NOT BE LIABLE FOR USER SUBMISSIONS OR THE DEFAMATORY, OFFENSIVE, OR ILLEGAL CONDUCT OF ANY THIRD PARTY AND THAT THE RISK OF HARM OR DAMAGE FROM THE FOREGOING RESTS ENTIRELY WITH YOU. <br /> The Website is controlled and offered by TechieVideos from its facilities in the United States of America. TechieVideos makes no representations that the TechieVideos Website is appropriate or available for use in other locations. Those who access or use the TechieVideos Website from other jurisdictions do so at their own volition and are responsible for compliance with local law.</p>\r\n</li>\r\n<li class=\"terms-item\">\r\n<h2>Indemnity</h2>\r\n<p>You agree to defend, indemnify and hold harmless TechieVideos, its parent companies, officers, directors, employees and agents, from and against any and all claims, damages, obligations, losses, liabilities, costs or debt, and expenses (including but not limited to attorney\'s fees) arising from: (i) your use of and access to the TechieVideos Website; (ii) your violation of any term of these Terms of Use; (iii) your violation of any third party right, including without limitation any copyright, property, or privacy right; or (iv) any claim that one of your User Submissions caused damage to a third party. This defense and indemnification obligation will survive these Terms of Use and your use of the TechieVideos Website.</p>\r\n</li>\r\n<li class=\"terms-item\">\r\n<h2>Assignment</h2>\r\n<p>These Terms of Use, and any rights and licenses granted hereunder, may not be transferred or assigned by you, but may be assigned by TechieVideos without restriction.</p>\r\n</li>\r\n<li class=\"terms-item\">\r\n<h2>General</h2>\r\n<p>You agree that: (i) the TechieVideos Website shall be deemed solely based in Florida; and (ii) the TechieVideos Website shall be deemed a passive website that does not give rise to personal jurisdiction over TechieVideos, either specific or general, in jurisdictions other than Florida. These Terms of Use shall be governed by the internal substantive laws of the State of Florida, without respect to its conflict of laws principles. Any claim or dispute between you and TechieVideos that arises in whole or in part from the TechieVideos Website shall be decided exclusively by a court of competent jurisdiction located in Orange County, Florida. These Terms of Use, together with the privacy policy at <a href=\"http://www.techievideos.com/privacy/\">http://www.TechieVideos.com/privacy</a> and any other legal notices published by TechieVideos on the Website, shall constitute the entire agreement between you and TechieVideos concerning the TechieVideos Website. If any provision of these Terms of Use is deemed invalid by a court of competent jurisdiction, the invalidity of such provision shall not affect the validity of the remaining provisions of these Terms of Use, which shall remain in full force and effect. No waiver of any term of this these Terms of Use shall be deemed a further or continuing waiver of such term or any other term, and TechieVideos&rsquo; failure to assert any right or provision under these Terms of Use shall not constitute a waiver of such right or provision. TechieVideos reserves the right to amend these Terms of Use at any time and without notice, and it is your responsibility to review these Terms of Use for any changes. Your use of the TechieVideos Website following any amendment of these Terms of Use will signify your assent to and acceptance of its revised terms. YOU AND TECHIEVIDEOS AGREE THAT ANY CAUSE OF ACTION ARISING OUT OF OR RELATED TO THE TECHIEVIDEOS WEBSITE MUST COMMENCE WITHIN NINETY (90) DAYS AFTER THE CAUSE OF ACTION ACCRUES. OTHERWISE, SUCH CAUSE OF ACTION IS PERMANENTLY BARRED.</p>\r\n</li>\r\n</ul>\r\n</div>','terms','full','2011-06-30 07:04:03','published'),(4,'How to install CumulusClips','<ol>\r\n<li>\r\n<h2>Requirements</h2>\r\n</li>\r\n<li>\r\n<h2>Downloading the Software</h2>\r\n</li>\r\n<li>\r\n<h2>Permissions</h2>\r\n</li>\r\n<li>\r\n<h2>Steps</h2>\r\n<ol>\r\n<li>Unzip</li>\r\n<li>Visit install</li>\r\n<li>Follow instructions</li>\r\n<li>Done!</li>\r\n</ol></li>\r\n</ol>','how-to-install-cumulusclips','default','2011-07-01 06:28:49','published');

/*Table structure for table `posts` */

DROP TABLE IF EXISTS `posts`;

CREATE TABLE `posts` (
  `post_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `post` longtext NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`post_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `posts` */

/*Table structure for table `privacy` */

DROP TABLE IF EXISTS `privacy`;

CREATE TABLE `privacy` (
  `privacy_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `video_comment` varchar(100) NOT NULL DEFAULT 'yes',
  `new_message` varchar(100) NOT NULL DEFAULT 'yes',
  `newsletter` varchar(100) NOT NULL DEFAULT 'yes',
  `new_video` varchar(100) NOT NULL DEFAULT 'yes',
  PRIMARY KEY (`privacy_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `privacy` */

/*Table structure for table `ratings` */

DROP TABLE IF EXISTS `ratings`;

CREATE TABLE `ratings` (
  `rating_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `video_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `date_created` datetime NOT NULL,
  `rating` tinyint(4) NOT NULL,
  PRIMARY KEY (`rating_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `ratings` */

/*Table structure for table `settings` */

DROP TABLE IF EXISTS `settings`;

CREATE TABLE `settings` (
  `setting_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `value` longtext NOT NULL,
  PRIMARY KEY (`setting_id`)
) ENGINE=MyISAM AUTO_INCREMENT=43 DEFAULT CHARSET=utf8;

/*Data for the table `settings` */

insert  into `settings`(`setting_id`,`name`,`value`) values (1,'active_theme','cumulus'),(2,'default_language','english'),(3,'enabled_plugins','a:0:{}'),(4,'auto_approve_users','0'),(5,'auto_approve_videos','0'),(6,'auto_approve_comments','0'),(7,'debug_conversion','1'),(9,'video_size_limit','102000000'),(11,'pagination_page_limit','9'),(40,'qt_faststart','/usr/local/bin/qt-faststart'),(15,'base_url','http://192.168.1.106'),(16,'secret_key','75ddabc22ef65219ca07012bd3270936'),(17,'sitename','CumulusClips'),(18,'admin_email','cumulus.admin@mailinator.com'),(19,'enable_uploads','0'),(20,'ffmpeg','/usr/local/bin/ffmpeg'),(21,'php','/usr/bin/php'),(22,'active_mobile_theme','mobile'),(23,'active_languages','a:3:{s:7:\"english\";a:2:{s:9:\"lang_name\";s:7:\"English\";s:11:\"native_name\";s:7:\"English\";}s:10:\"portuguese\";a:2:{s:9:\"lang_name\";s:10:\"Portuguese\";s:11:\"native_name\";s:10:\"PortuguÃªs\";}s:7:\"spanish\";a:2:{s:9:\"lang_name\";s:7:\"Spanish\";s:11:\"native_name\";s:8:\"EspaÃ±ol\";}}'),(24,'installed_plugins','a:1:{i:0;s:10:\"HelloWorld\";}'),(25,'h264_url',''),(26,'theora_url',''),(27,'mobile_url',''),(28,'thumb_url',''),(29,'h264_options','-vcodec libx264 -b 1600k -acodec libfaac -ac 2 -ab 128k -ar 44100 -f mp4'),(30,'theora_options','-vcodec libtheora -b 1600k -acodec libvorbis -ac 2 -ab 128k -ar 44100 -f ogg'),(31,'mobile_options','-vf scale=480:-1 -vcodec libx264 -x264opts level=30:cabac=0:bframes=0:weightb=0:weightp=0:8x8dct=0 -b 1000k -acodec libfaac -ac 2 -ab 96k -ar 44100 -f mp4'),(32,'thumb_options','-vf scale=640:-1 -t 1 -r 1 -f mjpeg'),(33,'smtp','O:8:\"stdClass\":5:{s:7:\"enabled\";s:1:\"0\";s:4:\"host\";s:0:\"\";s:4:\"port\";i:25;s:8:\"username\";s:0:\"\";s:8:\"password\";s:0:\"\";}'),(34,'alerts_videos','1'),(35,'alerts_comments','1'),(37,'alerts_users','1'),(38,'from_name',''),(39,'from_address',''),(41,'roles','a:2:{s:5:\"admin\";a:2:{s:4:\"name\";s:13:\"Administrator\";s:11:\"permissions\";a:1:{i:0;s:11:\"admin_panel\";}}s:4:\"user\";a:2:{s:4:\"name\";s:4:\"User\";s:11:\"permissions\";a:0:{}}} '),(42,'alerts_flags','1');

/*Table structure for table `subscriptions` */

DROP TABLE IF EXISTS `subscriptions`;

CREATE TABLE `subscriptions` (
  `sub_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `member` bigint(20) NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`sub_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `subscriptions` */

/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `user_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `username` varchar(80) NOT NULL,
  `email` varchar(80) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'user',
  `date_created` date NOT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `users` */

/*Table structure for table `videos` */

DROP TABLE IF EXISTS `videos`;

CREATE TABLE `videos` (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `videos` */

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
