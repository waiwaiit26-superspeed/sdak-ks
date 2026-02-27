<?php
/**
 * Admin Template — Header (AdminLTE 3)
 * Opens HTML document, loads AdminLTE CSS, opens <body> + admin wrapper
 * Variables: $basePath, $pageTitle, $extraCss, $page
 */
$basePath = $basePath ?? './';
$page = $page ?? '';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ($pageTitle ?? 'Admin') . ' | ' . siteConfig('site_name_short') . ' Admin'; ?></title>
    <?php $__fav = siteConfig('logo_favicon') ?: siteConfig('logo_web'); ?>
    <link id="dynamic-favicon" rel="icon" type="image/png" href="<?php echo $__fav ? htmlspecialchars($basePath . $__fav) : ''; ?>">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome (AdminLTE dependency) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- AdminLTE 3.2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/css/adminlte.min.css">

    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <!-- Custom AdminLTE Theme -->
    <link href="<?php echo $basePath; ?>assets/css/adminlte-custom.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="<?php echo $basePath; ?>assets/css/admin.css?v=<?php echo time(); ?>" rel="stylesheet">

    <!-- PDPA CSS -->
    <link href="<?php echo $basePath; ?>assets/css/pdpa.css?v=<?php echo time(); ?>" rel="stylesheet">

    <?php if (!empty($extraCss)) echo $extraCss; ?>
</head>
<body class="hold-transition sidebar-mini layout-fixed adminlte-body">
<div class="wrapper">
<?php include __DIR__ . '/navbar-top.php'; ?>
<?php include __DIR__ . '/sidebar.php'; ?>
