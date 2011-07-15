<?php

if (isset ($_POST['submitted'])) {

    // Validate Database host
    if (!empty ($_POST['db_host']) && !ctype_space ()) {
        $settings->db_host = mysql_real_escape_string (trim ($_POST['db_host']));
    } else {
        $errors[] = "A valid database host is needed";
    }


    // Validate Database user
    if (!empty ($_POST['db_user']) && !ctype_space ()) {
        $settings->db_user = mysql_real_escape_string (trim ($_POST['db_user']));
    } else {
        $errors[] = "A valid database username is needed";
    }


    // Validate Database password
    if (!empty ($_POST['db_pass']) && !ctype_space ()) {
        $settings->db_pass = mysql_real_escape_string (trim ($_POST['db_pass']));
    } else {
        $errors[] = "A valid database password is needed";
    }
    

    // Execute queries if no form errors were found
    if (empty ($errors)) {

        include ('queries.php');

        try {

            // Connect to user's database server
            $dbc = mysql_connect ($settings->db_host, $settings->db_user, $settings->db_pass);
            if (!$dbc) throw new Exception('connect');

            // Select user's database for operation
            $select = mysql_select_db($settings->db_name, $dbc);
            if (!$select) throw new Exception ('connect');

            // Perform install queries
            foreach ($install_queries as $query) {
                $query = mysql_real_escape_string (str_replace ('{DB_PREFIX}', $settings->db_prefix, $query));
                $result = mysql_query ($query);
                if (!$result) throw new Exception ('Unable to execture query');
            }

        } catch (Exception $e) {
            if ($e->getMessage() == 'connect') {
                $error_msg = 'We were unable to connect to you database. Please verify you provided the login information';
            }
        }

    } else {
        $error_msg = '<p>The following errors were found, please correct them and try again:<br /><br />- ';
        $error_msg .= implode ('<br />- ', $errors);
    }



}


?>