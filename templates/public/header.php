<?php
/**
 * Public Template — Header
 * Opens HTML document, loads CSS, opens <body>
 * Variables: $basePath, $pageTitle, $extraCss
 */
$basePath = $basePath ?? './';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <?php include __DIR__ . '/../_shared/head.php'; ?>

    <?php if (!empty($extraCss)) echo $extraCss; ?>
</head>
<body>
<?php include __DIR__ . '/navbar.php'; ?>
