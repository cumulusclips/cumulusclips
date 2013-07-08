            </div>
            <div class="right">
                <?php View::WriteSidebarBlocks(); ?>
                <?php View::Block ('ad300.tpl'); ?>
            </div>
        </div>
    </div>
    <!-- END MAIN CONTAINER -->
    
</div>
<!-- END WRAPPER -->
    
<?php View::Block ('footer_nav.tpl'); ?>

<script type="text/javascript" src="<?=$config->theme_url?>/js/jquery.min.js"></script>
<script type="text/javascript" src="<?=$config->theme_url?>/js/general.js"></script>
<?php View::WriteJs(); ?>

</body>
</html>