<?php include ('header.php'); ?>

    <div id="site-details" class="welcome-done requirements-done ftp-done database-done">

        <?php include ('sidebar.php'); ?>

        <div id="container">
            <div id="main">

                <h1>Site Details</h1>
                <div class="block">
                    <div class="row"><label>Base URL:</label><input type="text" class="text" name="url" value="<?php echo HOST; ?>" /></div>
                    <div class="row"><label>Site Name:</label><input type="text" class="text" name="name" /></div>
                    <div class="row"><label>Admin Username:</label><input type="text" class="text" name="user" /></div>
                    <div class="row"><label>Password:</label><input type="text" class="text" name="pass" /></div>
                    <div class="row"><label>E-mail:</label><input type="text" class="text" name="email" /></div>
                    <div class="row-shift"><input type="submit" class="button" value="Submit" /></div>
                    <div class="row-shift"><a href="<?php echo HOST; ?>/install/?complete" class="button">Continue</a></div>
                </div>

            </div>
        </div>

    </div>

<?php include ('footer.php'); ?>