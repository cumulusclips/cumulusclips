<?php

$ftp_host = 'localhost';
$ftp_username = 'miguel';
$ftp_password = 'Damian646';
$remote_file = '/var/www/cumulus/test.php';

// Connect to FTP host
$ftp_stream = ftp_connect ($ftp_host) or die ('Unable to connect to FTP host');

// Login with username and password
$login_result = ftp_login ($ftp_stream, $ftp_username, $ftp_password) or die ('Unable to login to FTP server');

// Change to Cumulus directory
//ftp_chdir ($connection_id, '/var/www/cumulus');


$handle = fopen ('http://cumulusclips.org/index.php', 'rb');
$result = @ftp_fput ($ftp_stream, $remote_file, $handle, FTP_BINARY);
ftp_chmod ($ftp_stream, 0644, $remote_file);

echo ($result) ? 'Success' : 'Failed';

// Close connection
fclose ($handle);
ftp_close ($ftp_stream);


// Check for updates
// Load updates.xml
// Loop through xml contents performing changes
    // Updates - Load new contents from stream and overwrite content
    // Additions - Load new file contents from stream and save to HDD
    // Removals - Delete files

?>