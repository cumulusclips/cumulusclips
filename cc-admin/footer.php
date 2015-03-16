

    </main>
    <!-- End Content Col -->

</div>
<!-- End Container -->
<footer class="footer">Footer</footer>




<!--


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
        &nbsp;&nbsp;|&nbsp;&nbsp; <a href="http://cumulusclips.org/docs/" title="Documentation">Documentation</a>
    </div>
</div>

-->

<script type="text/javascript" src="<?=ADMIN?>/js/jquery.min.js"></script>
<script type="text/javascript" src="<?=ADMIN?>/js/cookie.plugin.js"></script>
<script type="text/javascript" src="<?=ADMIN?>/extras/tipsy/javascripts/jquery.tipsy.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
<script type="text/javascript" src="<?=ADMIN?>/js/admin.js"></script>
<?php Functions::AdminOutputJS(); ?>
</body>
</html>
