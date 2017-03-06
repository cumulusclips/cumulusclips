# CumulusClips Video CMS

### CONTENTS OF THIS FILE

 * About CumulusClips
 * Server Requirements
 * PHP Requirements
 * File Permissions


### ABOUT CUMULUSCLIPS

CumulusClips is an open source video content management platform capable of
supporting from small video microsites to large scale video websites. For more
information, see the CumulusClips website at http://cumulusclips.org/.


### SERVER REQUIREMENTS

CumulusClips requires:

* Linux operating system (Kernel 2.6.32 or greater)
* Apache web server version 2.0 (or greater) (http://httpd.apache.org/)
  * Apache mod_rewrite module
* PHP 5.3 (or greater) (http://www.php.net/)
* MySQL 5.0 (or greater) (http://www.mysql.com/)


### PHP REQUIREMENTS

The following PHP modules are required:

* GD
* POSIX
* SimpleXML
* ZIP
* CURL

The following PHP settings are required:

```
short_open_tags = on
upload_max_filesize = 110M
post_max_size = 110M
max_execution_time = 1500
safe_mode = off
register _globals = off
```


### FILE PERMISSIONS

CumulusClips needs to modify files occasionally. To do this it needs access
to the filesystem. There are two options to achive this:

1) Make the CumulusClips be owned by the user PHP is running as. Typically
this the Apache user (www-data, apache, nobody, etc.) Giving the files
777 permissions is not enough. The files have to be owned by PHP.

2) Alternatively, if there is an FTP server installed, simply provide the
FTP credentials to the installer. The user provided needs access to modify
the CumulusClips via FTP.