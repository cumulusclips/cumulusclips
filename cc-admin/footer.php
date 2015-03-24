
    </main>
    <!-- End Main Content -->
    
    <?php if (isset($pagination) && $pagination->displayPagination()): ?>
        <?=$pagination->paginate()?>
    <?php endif; ?>

</div>
<!-- End Container -->

<footer>
    <div id="footer">
        <?php if (!empty ($_SESSION['updates_available']) && !isset ($dont_show_update_prompt)): ?>
            *New version (<?=$updates_available->version?>) available - <a href="<?=ADMIN?>/updates.php">Update Now</a>
        <?php else: ?>
            Version <?=CURRENT_VERSION?> (Latest) 
        <?php endif; ?>
        &nbsp;&nbsp;|&nbsp;&nbsp; <a href="http://cumulusclips.org/docs/" title="Documentation">Documentation</a>
    </div>
</footer>

<script type="text/javascript" src="<?=ADMIN?>/js/jquery.min.js"></script>
<script type="text/javascript" src="<?=ADMIN?>/js/cookie.plugin.js"></script>
<script type="text/javascript" src="<?=ADMIN?>/extras/tipsy/javascripts/jquery.tipsy.js"></script>
<script src="<?=ADMIN?>/extras/bootstrap-3.3.4/js/bootstrap.min.js"></script>
<script type="text/javascript" src="<?=ADMIN?>/js/admin.js?v<?=CURRENT_VERSION?>"></script>
<?php Functions::AdminOutputJS(); ?>
</body>
</html>