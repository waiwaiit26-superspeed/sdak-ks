<?php
/**
 * Public Template — Footer
 * Visual footer HTML content (social links, menus, contact)
 * Variables: $basePath
 */
$basePath = $basePath ?? './';
?>
<!-- Footer -->
<footer class="footer-main mt-auto">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <h5>
                    <img id="footer-logo" src="" alt="" style="display:none;height:32px;width:auto;object-fit:contain;margin-right:8px;vertical-align:middle;">
                    <i class="bi bi-mortarboard-fill me-2" id="footer-icon"></i><span id="footer-brand-text">ส.ร.ม.ก.</span>
                </h5>
                <p id="footer-description">สมาคมรองผู้อำนวยการโรงเรียนมัธยมศึกษาจังหวัดกาฬสินธุ์<br>
                   Secondary Deputy Administrator of Kalasin</p>
                <div id="footer-social" class="d-flex gap-3 mt-3" style="display:none!important">
                </div>
            </div>
            <div class="col-lg-3">
                <h5>เมนู</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="<?php echo $basePath; ?>">หน้าแรก</a></li>
                    <li class="mb-2"><a href="<?php echo $basePath; ?>web/?page=news">ข่าวสาร</a></li>
                    <li class="mb-2"><a href="<?php echo $basePath; ?>web/?page=activities">กิจกรรม</a></li>
                    <li class="mb-2"><a href="<?php echo $basePath; ?>auth/?page=register">สมัครสมาชิก</a></li>
                    <li class="mb-2"><a href="<?php echo $basePath; ?>web/?page=privacy-policy">นโยบายความเป็นส่วนตัว</a></li>
                </ul>
            </div>
            <div class="col-lg-2">
                <h5>ประเภทสมาชิก</h5>
                <ul class="list-unstyled">
                    <li class="mb-2">สมาชิกสามัญ</li>
                    <li class="mb-2">สมาชิกวิสามัญ</li>
                    <li class="mb-2">สมาชิกสมทบ</li>
                    <li class="mb-2">สมาชิกกิตติมศักดิ์</li>
                </ul>
            </div>
            <div class="col-lg-3">
                <h5>ติดต่อเรา</h5>
                <ul class="list-unstyled" id="footer-contact">
                    <li class="mb-2" id="footer-address"><i class="bi bi-geo-alt me-2"></i><span>จังหวัดกาฬสินธุ์</span></li>
                    <li class="mb-2" id="footer-email"><i class="bi bi-envelope me-2"></i><span>contact@sdak-ks.org</span></li>
                    <li class="mb-2" id="footer-phone" style="display:none"><i class="bi bi-telephone me-2"></i><span></span></li>
                </ul>
                <div id="footer-embed-stats"></div>
            </div>
        </div>
    </div>
</footer>
<div class="footer-bottom text-center">
    <div class="container">
        &copy; <?php echo date('Y') + 543; ?> <span id="footer-copyright-text"><?php echo htmlspecialchars(SITE_NAME . ' (' . SITE_NAME_SHORT . ')'); ?></span> | <?php echo htmlspecialchars(SITE_NAME_EN); ?>
        <br><a href="<?php echo $basePath; ?>web/?page=privacy-policy" class="small text-white-50"><i class="bi bi-shield-lock me-1"></i>นโยบายความเป็นส่วนตัว (PDPA)</a>
        <br><small class="text-white-50">Dev by <a href="https://www.facebook.com/kroowaiwai" target="_blank" rel="noopener" class="text-white-50" style="text-decoration:underline">Waiwai jaidee</a></small>
    </div>
</div>

<?php include __DIR__ . '/../_shared/pdpa.php'; ?>
</body>
</html>
