<?php
/**
 * Auth Template — Scripts (AdminLTE 3)
 * Loads jQuery, Bootstrap 4.6, AdminLTE 3.2, app scripts
 * Variables: $basePath
 */
$basePath = $basePath ?? './';
?>

<!-- jQuery 3.7.1 -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>

<!-- jQuery Validate -->
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.20.0/dist/jquery.validate.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.20.0/dist/additional-methods.min.js"></script>

<!-- Bootstrap 4.6 JS (AdminLTE 3 requires BS4) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- AdminLTE 3.2 JS -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/js/adminlte.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- App Config & Scripts -->
<script>const BASE_PATH = '<?php echo $basePath; ?>';</script>
<script src="<?php echo $basePath; ?>assets/js/api.js?v=<?php echo time(); ?>"></script>
<script src="<?php echo $basePath; ?>assets/js/app.js?v=<?php echo time(); ?>"></script>
<script src="<?php echo $basePath; ?>assets/js/modules.js?v=<?php echo time(); ?>"></script>
