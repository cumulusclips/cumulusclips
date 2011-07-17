<?php include_once (INSTALL . '/views/header.php'); ?>

    <div id="ftp" class="welcome-done requirements-done">

        <?php include_once (INSTALL . '/views/sidebar.php'); ?>

        <!-- BEGIN MAIN -->
        <div id="main">

            <h1>FTP Connection</h1>

            <?php if ($error_msg): ?>
                <div class="error"><?php echo $error_msg; ?></div>
            <?php endif; ?>

            <div class="block">

                <form method="post" action="<?php echo HOST; ?>/install/?ftp">
                <div class="row-shift"><a href="#" class="more-info why" data-content="why" title="Why do we ask">Why do we ask</a></div>

                <div class="row <?php echo (isset ($errors['hostname'])) ? 'errors' : ''; ?>"><label>Hostname:</label><input type="text" class="text" name="hostname" value="<?php echo (isset ($hostname)) ? $hostname : 'localhost'; ?>" /></div>
                <div class="row-shift">The hostname of your FTP server. If you're not sure just use localhost.</div>

                <div class="row <?php echo (isset ($errors['username'])) ? 'errors' : ''; ?>"><label>Username:</label><input type="text" class="text" name="username" value="<?php echo (isset ($username)) ? $username : ''; ?>" /></div>
                <div class="row-shift">FTP username you use to upload files to your website.</div>

                <div class="row <?php echo (isset ($errors['password'])) ? 'errors' : ''; ?>"><label>Password:</label><input type="text" class="text" name="password" value="<?php echo (isset ($password)) ? $password : ''; ?>" /></div>
                <div class="row-shift">Password you use to upload files to your website.</div>

                <div class="row <?php echo (isset ($errors['protocol'])) ? 'errors' : ''; ?>">
                    <label>Protocol:</label>
                    <div id="protocol">
                        <input id="ftp-method" type="radio" name="protocol" value="ftp" <?php echo (!isset ($protocol) || (isset ($protocol) && $protocol == 'ftp')) ? 'checked="checked"' : ''; ?> /><label for="ftp-method">FTP</label>
                        <input id="ftps-method"  type="radio" name="protocol" value="ftps" <?php echo (isset ($protocol) && $protocol == 'ftps') ? 'checked="checked"' : ''; ?> /><label for="ftps-method">FTPS</label>
                    </div>
                </div>

                <div class="row-shift">
                    <input type="submit" class="button" value="Submit FTP Login" />
                    <input type="hidden" name="submitted" value="TRUE" />
                </div>
                </form>
                
            </div>


            <div class="more-info-content" id="why">
                <span class="tip">We need your FTP credentials in order to
                access the filesystem during updates and when installing plugins.</span>
            </div>


        </div>
        <!-- END MAIN -->

    </div>

<?php include_once (INSTALL . '/views/footer.php'); ?>