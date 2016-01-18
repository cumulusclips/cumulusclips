<?php include_once (INSTALL . '/views/header.php'); ?>

    <div id="requirements" class="welcome-done">

        <?php include_once (INSTALL . '/views/sidebar.php'); ?>

        <div id="container">
            <div id="main">

                <h1>Requirements</h1>

                <?php if ($errors): ?>
                <div class="error">
                    Not all the requirements have been met by your server. You
                    cannot continue with the install until these have been fixed.
                    Please correct them and try again.
                </div>
                <?php endif; ?>

                <?php if ($warnings): ?>
                <div class="notice">
                    Some important (<em>but not required</em>) items were not
                    found on your server. You can continue without them, however
                    we strongly recommend you resolve them. Your system may not
                    perform as expected in some cases.
                </div>
                <?php endif; ?>
                
                <div class="block">
                    
                    
                    <h2>Software</h2>
                    <table>
                        <tr>
                            <td class="server-setting">PHP 5.3+</td>
                            <td>
                                <?php if (!$version): ?>
                                    <img src="images/cross.png" />
                                    CumulusClips requires at least PHP 5.3 to run. (Current version <?php echo $current_version; ?>)
                                <?php elseif (!$php_path): ?>
                                    <img src="images/flag_yellow.png" />
                                    PHP path was not found. Video uploads have been disabled. <a href="#" class="more-info" data-content="php" title="More Info">More Info</a>
                                <?php else: ?>
                                    <img src="images/tick.png" />
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>


                    
                    
                    
                    <h2>PHP Modules</h2>
                    <table>
                        <tr>
                            <td class="server-setting">POSIX</td>
                            <td>
                                <img src="images/<?php echo ($posix) ? 'tick.png' : 'cross.png'; ?>" />
                                <?php if (!$posix): ?>
                                This needs to be enabled
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="server-setting">cURL</td>
                            <td>
                                <img src="images/<?php echo ($curl) ? 'tick.png' : 'cross.png'; ?>" />
                                <?php if (!$curl): ?>
                                This needs to be enabled
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="server-setting">GD</td>
                            <td>
                                <img src="images/<?php echo ($gd) ? 'tick.png' : 'cross.png'; ?>" />
                                <?php if (!$gd): ?>
                                This needs to be enabled
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="server-setting">FTP</td>
                            <td>
                                <img src="images/<?php echo ($ftp) ? 'tick.png' : 'cross.png'; ?>" />
                                <?php if (!$ftp): ?>
                                This needs to be enabled
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>JSON</td>
                            <td>
                                <img src="images/<?php echo ($json) ? 'tick.png' : 'cross.png'; ?>" />
                                <?php if (!$json): ?>
                                This needs to be enabled
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Zip</td>
                            <td>
                                <img src="images/<?php echo ($zip) ? 'tick.png' : 'cross.png'; ?>" />
                                <?php if (!$zip): ?>
                                This needs to be enabled
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>SimpleXML</td>
                            <td>
                                <img src="images/<?php echo ($simplexml) ? 'tick.png' : 'cross.png'; ?>" />
                                <?php if (!$simplexml): ?>
                                This needs to be enabled
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                    
                    
                    
                    
                    




                    <h2>PHP Functions</h2>
                    <table>
                        <tr>
                            <td class="server-setting">exec()</td>
                            <td>
                                <img src="images/<?php echo ($exec) ? 'tick.png' : 'cross.png'; ?>" />
                                <?php if (!$exec): ?>
                                This needs to be enabled
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>                  
                    

                    






                    <h2>PHP Settings</h2>
                    <table>
                        <tr>
                            <td class="server-setting">short_open_tags</td>
                            <td>
                                <img src="images/<?php echo ($short_open_tag) ? 'tick.png' : 'cross.png'; ?>" />
                                <?php if (!$short_open_tag): ?>
                                This needs to be enabled
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>file_uploads</td>
                            <td>
                                <img src="images/<?php echo ($file_uploads) ? 'tick.png' : 'cross.png'; ?>" />
                                <?php if (!$file_uploads): ?>
                                This needs to be enabled
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>post_max_size</td>
                            <td>
                                <img src="images/<?php echo ($post_max_size) ? 'tick.png' : 'flag_yellow.png'; ?>" />
                                <?php if (!$post_max_size): ?>
                                    Setting is too small. Server only allows <?php echo ini_get('upload_max_filesize'); ?>. <a href="#" class="more-info" data-content="filesize" title="More Info">More Info</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>upload_max_filesize</td>
                            <td>
                                <img src="images/<?php echo ($upload_max_filesize) ? 'tick.png' : 'flag_yellow.png'; ?>" />
                                <?php if (!$upload_max_filesize): ?>
                                    Setting is too small. Server only allows <?php echo ini_get('upload_max_filesize'); ?>. <a href="#" class="more-info" data-content="filesize" title="More Info">More Info</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>max_execution_time</td>
                            <td>
                                <img src="images/<?php echo ($max_execution_time) ? 'tick.png' : 'flag_yellow.png'; ?>" />
                                <?php if (!$max_execution_time): ?>
                                Too short, currently <?php echo ini_get('max_execution_time'); ?> seconds. <a href="#" class="more-info" data-content="max_execution_time" title="More Info">More Info</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>


                    <p>
                        <?php if ($continue): ?>
                            <a href="<?php echo HOST; ?>/cc-install/?ftp" class="button">Continue to next step</a>
                        <?php else: ?>
                            <a href="<?php echo HOST; ?>/cc-install/?requirements" class="button">Check Again</a>
                        <?php endif; ?>
                    </p>






                    <div class="more-info-content" id="max_execution_time">
                        <span class="tip">At least 1200 seconds (20 minutes) is recommended for this
                        setting. This is to prevent timeouts during updates, and
                        video upload / conversion.</span>
                    </div>

                    <div class="more-info-content" id="filesize">
                        <span class="tip">At least '100M' is recommended, this is to allow maximum video uploads of 100MB.</span>
                    </div>

                    <div class="more-info-content" id="ffmpeg">
                        <span class="tip">FFMPEG is used to convert uploaded videos.
                        You won't be able to upload videos without this. You can
                        add this later in the admin panel, or use a plugin or
                        service instead.</span>
                    </div>

                    <div class="more-info-content" id="php">
                        <span class="tip">PHP path is needed to trigger video conversion.
                        You won't be able to upload videos without this. You can
                        add this later in the admin panel, or use a plugin or
                        service instead.</span>
                    </div>



                    

                </div>

            </div>
        </div>

    </div>

<?php include_once (INSTALL . '/views/footer.php'); ?>