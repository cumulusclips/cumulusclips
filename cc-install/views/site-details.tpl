<?php include_once (INSTALL . '/views/header.php'); ?>

    <div id="site-details" class="welcome-done requirements-done ftp-done database-done">

        <?php include_once (INSTALL . '/views/sidebar.php'); ?>

        <!-- BEGIN MAIN -->
        <div id="main">

            <h1>Site Details</h1>

            <?php if ($error_msg): ?>
                <div class="error"><?php echo $error_msg; ?></div>
            <?php endif; ?>

            <div class="block">

                <form method="post" action="<?php echo HOST; ?>/cc-install/?site-details">

                <div class="row-shift"><h2>Site Configuration</h2></div>
                <div class="row <?php echo (isset ($errors['url'])) ? 'errors' : ''; ?>"><label>Base URL:</label><input type="text" class="text" name="url" value="<?php echo (isset ($url)) ? $url : HOST; ?>" /></div>
                <div class="row-shift">The full address your users will type to visit your video website.</div>

                <div class="row <?php echo (isset ($errors['sitename'])) ? 'errors' : ''; ?>"><label>Sitename:</label><input type="text" class="text" name="sitename" value="<?php echo (isset ($sitename)) ? $sitename : ''; ?>" /></div>
                <div class="row-shift">Name of your video website.</div>

                <div class="row-shift"><h2>Admin Account</h2></div>
                <div class="row <?php echo (isset ($errors['username'])) ? 'errors' : ''; ?>"><label>Admin Username:</label><input type="text" class="text" name="username" value="<?php echo (isset ($username)) ? $username : ''; ?>" /></div>
                <div class="row-shift">
                    Username for your admin account; used to access the admin panel.<br />
                    Only letters &amp; numbers, special characters are not allowed.
                </div>

                <div class="row <?php echo (isset ($errors['password'])) ? 'errors' : ''; ?>"><label>Admin Password:</label><input type="password" class="text mask" name="password" value="<?php echo (isset ($password)) ? $password : ''; ?>" /></div>
                <div class="row-shift">Password for your admin account.</div>

                <div class="row <?php echo (isset ($errors['email'])) ? 'errors' : ''; ?>"><label>Admin E-mail:</label><input type="text" class="text" name="email" value="<?php echo (isset ($email)) ? $email : ''; ?>" /></div>
                <div class="row-shift">Admin account e-mail address. All site alerts will be sent here.</div>

                <div class="row-shift">
                    <input type="submit" class="button" value="Submit Site Details" />
                    <input type="hidden" name="submitted" value="TRUE" />
                </div>
                </form>

            </div>

        </div>
        <!-- END MAIN -->

    </div>

<?php include_once (INSTALL . '/views/footer.php'); ?>