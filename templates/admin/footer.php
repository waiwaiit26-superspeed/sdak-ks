<?php
/**
 * Admin Template — Footer (AdminLTE 3)
 * Closes container-fluid, content section, content-wrapper + document
 */
?>
            </div><!-- /.container-fluid -->
        </section><!-- /.content -->
    </div><!-- /.content-wrapper -->

    <!-- Main Footer -->
    <footer class="main-footer text-center">
        <div class="mb-1">
            <div id="footer-embed-stats"></div>
        </div>
        <strong>&copy; <?php echo date('Y') + 543; ?> <a href="../"><?php echo defined('SITE_NAME_SHORT') ? SITE_NAME_SHORT : 'ส.ร.ม.ก.'; ?></a></strong> <?php echo defined('SITE_NAME') ? SITE_NAME : 'สมาคมรองผู้อำนวยการโรงเรียนมัธยมศึกษาจังหวัดกาฬสินธุ์'; ?>
        <div class="d-none d-sm-block mt-1"><small>Dev by <a href="https://www.facebook.com/kroowaiwai" target="_blank" rel="noopener">Waiwai jaidee</a></small></div>
    </footer>
</div><!-- ./wrapper -->

<?php include __DIR__ . '/../_shared/pdpa.php'; ?>
</body>
</html>
