---------------------
CONTENTS OF THIS FILE
---------------------

 * About CumulusClips
 * Server Requirements
 * PHP Requirements
 * File Permissions





------------------
ABOUT CUMULUSCLIPS
------------------

CumulusClips is an open source video content management platform capable of
supporting from small video microsites to large scale video websites. For more
information, see the CumulusClips website at http://cumulusclips.org/.





-------------------
SERVER REQUIREMENTS
-------------------

CumulusClips requires:

- Apache web server version 2.0 (or greater) (http://httpd.apache.org/).
    - Apache mod_rewrite module
- PHP 5.2 (or greater) (http://www.php.net/).
- MySQL 5.0 (or greater) (http://www.mysql.com/).

For video encoding:

- FFMPEG (http://ffmpeg.org/ - videolan mirror recommended).
- qt-faststart (ships with FFMPEG).
- x264 (http://www.videolan.org/).
- faac (http://www.audiocoding.com/).
- Theora (http://www.theora.org/).
- Vorbis (http://www.vorbis.com/).





----------------
PHP REQUIREMENTS
----------------

The following PHP modules are required:

- SimpleXML
- ZIP
- CURL

The following PHP settings are required:

short_open_tags = on
upload_max_filesize = 100M
post_max_size = 100M
max_execution_time = 1500





----------------
FILE PERMISSIONS
----------------

Make sure the following directories are writeable by PHP and the webserver:

/cc-core/log
/cc-content/uploads/temp
/cc-content/uploads/flv
/cc-content/uploads/mp4
/cc-content/uploads/thumbs