<?php
/**
 * Member Template — Footer (AdminLTE 3)
 * Closes content-wrapper, renders main-footer, closes wrapper + body + html
 * Variables: $basePath
 */
$basePath = $basePath ?? './';
?>
        </div><!-- /.container -->
    </div><!-- /.content -->
</div><!-- /.content-wrapper -->

    <!-- Main Footer -->
    <footer class="main-footer text-center">
        <div><div id="footer-embed-stats" style="display:inline-block"></div></div>
        <div class="mt-1">
            <strong>&copy; <?php echo date('Y') + 543; ?> <?php echo siteConfig('site_name_short'); ?></strong> <?php echo siteConfig('site_name'); ?>
            <span class="d-none d-sm-inline ml-2">| Dev by <a href="https://www.facebook.com/kroowaiwai" target="_blank" rel="noopener">Waiwai jaidee</a></span>
        </div>
    </footer>
</div><!-- ./wrapper -->

<?php include __DIR__ . '/../_shared/pdpa.php'; ?>
</body>
</html>
