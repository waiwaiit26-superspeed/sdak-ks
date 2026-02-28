<?php
/**
 * Admin Router — /admin/?page=xxx
 * Admin pages: dashboard, members, news, activities
 */
define('ROOT_PATH', __DIR__ . '/../');
require_once ROOT_PATH . 'config/config.php';
require_once ROOT_PATH . 'config/database.php';

$basePath = '../';
$isAdmin = true;
$page = $_GET['page'] ?? 'dashboard';

$allowed = [
    'dashboard',
    'members',
    'news',
    'activities',
    'navigation',
    'pages',
    'logs',
    'settings',
    'fees',
    'receipts',
    'logo',
    'finance',
    'member-types',
    'telegram-send'
];

if (in_array($page, $allowed)) {
    include ROOT_PATH . 'views/admin/pages/' . $page . '.php';
} else {
    header('Location: ./?page=dashboard');
    exit;
}
