<?php include_once (INSTALL . '/views/header.php'); ?>

    <div id="complete" class="welcome-done requirements-done ftp-done database-done site-details-done">

        <?php include_once (INSTALL . '/views/sidebar.php'); ?>

        <div id="main">
            <h1>Install Complete</h1>

            <?php if ($error_msg): ?>
                <div class="notice"><?php echo $error_msg; ?></div>
                <div class="block">
                    <p>Almost done! We had a little trouble cleaning up after the install.
                    You must remove the install directory manually before you continue.</p>
                    <p>Afterwards your video sharing website will be ready to use. Your
                    login for the main site and the admin panel are one in the same.</p>
                    <p>To enter the admin panel simply login and click on 'Admin'.</p>
                    <p><a href="<?php echo $settings->base_url; ?>/" class="button">View My Site</a></p>
                </div>
            <?php else: ?>
                <div class="block">
                    <p>All done! Your video sharing website is now ready to use. Your
                    login for the main site and the admin panel are one in the same.</p>
                    <p>To enter the admin panel simply login and click on 'Admin'.</p>
                    <p><a href="<?php echo $settings->base_url; ?>/" class="button">View My Site</a></p>
                </div>
            <?php endif; ?>
                
        </div>

    </div>

<?php include_once (INSTALL . '/views/footer.php'); ?>