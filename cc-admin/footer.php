        </div>
    </div>

    <div id="footer-spacer"></div>

</div>

<div id="footer">
    <div id="footer-left">CumulusClips</div>
    <div id="footer-right">
        <?php if (!empty ($_SESSION['updates_available']) && !isset ($dont_show_update_prompt)): ?>
            *New version (<?=$updates_available->version?>) available - <a href="<?=ADMIN?>/updates.php">Update Now</a>
        <?php else: ?>
            Version <?=CURRENT_VERSION?> (Latest) 
        <?php endif; ?>
        &nbsp;&nbsp;|&nbsp;&nbsp; <a href="" title="Documentation">Documentation</a>
    </div>
</div>

<script type="text/javascript" src="<?=ADMIN?>/js/jquery.min.js"></script>
<script type="text/javascript" src="<?=ADMIN?>/js/cookie.plugin.js"></script>
<script type="text/javascript" src="<?=ADMIN?>/js/admin.js"></script>
<?php if (isset ($admin_js)) Functions::AdminOutputJS ($admin_js); ?>
</body>
</html>