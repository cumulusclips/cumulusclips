<?php include (INSTALL . '/views/header.php'); ?>

    <div id="welcome">

        <?php include (INSTALL . '/views/sidebar.php'); ?>

        <div id="container">
            <div id="main">

                <h1>Welcome</h1>
                <div class="block">
                    <p>Welcome to CumulusClips. This wizard will guide you
                        through the process for installing our video platform.
                        We'll gather a few details about your site over the next
                        few steps, and after a couple of minutes your video site
                        will be ready for use.
                    </p>
                    <p><a href="<?php echo HOST; ?>/install/?requirements" class="button">Continue</a></p>
                </div>

            </div>
        </div>

    </div>

<?php include (INSTALL . '/views/footer.php'); ?>