<?php
/**
 * Global Scripts — ALL CDN & app script references
 * ไฟล์รวม script ทั้งหมดที่ใช้ทั่วทั้งระบบ (CDN + app scripts)
 * Included by each role's scripts.php
 * Variables: $basePath (default './')
 */
$basePath = $basePath ?? './';
?>

<!-- jQuery 3.7.1 -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>

<!-- jQuery Validate -->
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.20.0/dist/jquery.validate.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.20.0/dist/additional-methods.min.js"></script>

<!-- Bootstrap 5.3 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- App Config & Scripts -->
<script>const BASE_PATH = '<?php echo $basePath; ?>';</script>
<script src="<?php echo $basePath; ?>assets/js/api.js?v=<?php echo time(); ?>"></script>
<script src="<?php echo $basePath; ?>assets/js/app.js?v=<?php echo time(); ?>"></script>
<script src="<?php echo $basePath; ?>assets/js/modules.js?v=<?php echo time(); ?>"></script>
