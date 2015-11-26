<?php include_once (INSTALL . '/views/header.php'); ?>

    <div id="database" class="welcome-done requirements-done ftp-done">

        <?php include_once (INSTALL . '/views/sidebar.php'); ?>

        <!-- BEGIN MAIN -->
        <div id="main">

            <h1>Database Setup</h1>

            <?php if ($error_msg): ?>
                <div class="error"><?php echo $error_msg; ?></div>
            <?php endif; ?>

            <div class="block">

                <form method="post" action="<?php echo HOST; ?>/cc-install/?database">
                <div class="row-shift">An asterisk (*) denotes required field</div>

                <div class="row <?php echo (isset ($errors['hostname'])) ? 'errors' : ''; ?>"><label>*Database Host:</label><input type="text" class="text" name="hostname" value="<?php echo (isset ($hostname)) ? $hostname : 'localhost'; ?>" /></div>
                <div class="row-shift">The hostname of your database server. If you're not sure just use localhost.</div>

                <div class="row <?php echo (isset ($errors['name'])) ? 'errors' : ''; ?>"><label>*Database Name:</label><input type="text" class="text" name="name" value="<?php echo (isset ($name)) ? $name : ''; ?>" /></div>
                <div class="row-shift">Name of your database.</div>

                <div class="row <?php echo (isset ($errors['username'])) ? 'errors' : ''; ?>"><label>*Database User:</label><input type="text" class="text" name="username" value="<?php echo (isset ($username)) ? $username : ''; ?>" /></div>
                <div class="row-shift">Username you use to connect to your database.</div>

                <div class="row <?php echo (isset ($errors['password'])) ? 'errors' : ''; ?>"><label>*Database Password:</label><input type="password" class="text mask" name="password" value="<?php echo (isset ($password)) ? $password : ''; ?>" /></div>
                <div class="row-shift">Password you use to connect to your database.</div>

                <div class="row <?php echo (isset ($errors['prefix'])) ? 'errors' : ''; ?>"><label>Table Prefix:</label><input type="text" class="text" name="prefix" value="<?php echo (isset ($prefix)) ? $prefix : ''; ?>" /></div>
                <div class="row-shift">Prefix you wish to prepend new table names with.</div>

                <div class="row-shift">
                    <input type="submit" class="button" value="Submit Database Login" />
                    <input type="hidden" name="submitted" value="TRUE" />
                </div>
                </form>
                
            </div>

        </div>
        <!-- END MAIN -->

    </div>

<?php include_once (INSTALL . '/views/footer.php'); ?>