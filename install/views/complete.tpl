<?php include_once (INSTALL . '/views/header.php'); ?>

    <div id="complete" class="welcome-done requirements-done ftp-done database-done site-details-done">

        <?php include_once (INSTALL . '/views/sidebar.php'); ?>

        <div id="main">
            <h1>Install Complete</h1>
            <div class="block">
                <p>All done! Your video sharing website is now ready to use. Your
                login for the main site and the admin panel are one in the same.</p>
                <p>To enter the admin panel simply login and click on 'Admin'.</p>
                <p><a href="<?php echo $settings->base_url; ?>/" class="button">View My Site</a></p>
            </div>
        </div>

    </div>

<?php include_once (INSTALL . '/views/footer.php'); ?>