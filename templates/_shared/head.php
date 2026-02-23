<?php
/**
 * Shared <head> content — meta, CSS CDNs, fonts
 * Included by each role's header.php
 * Variables: $basePath (default './'), $pageTitle
 */
$basePath = $basePath ?? './';
?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? (SITE_NAME_SHORT . ' | ' . SITE_NAME_EN); ?></title>
    <link id="dynamic-favicon" rel="icon" type="image/png" href="">
    <meta name="description" content="สมาคมรองผู้อำนวยการโรงเรียนมัธยมศึกษาจังหวัดกาฬสินธุ์ (ส.ร.ม.ก.) SDAK-KS">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="<?php echo $basePath; ?>assets/css/style.css" rel="stylesheet">

    <!-- PDPA CSS -->
    <link href="<?php echo $basePath; ?>assets/css/pdpa.css" rel="stylesheet">
