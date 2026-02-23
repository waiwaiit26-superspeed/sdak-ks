<?php
/**
 * Member Router — /member/?page=xxx
 * Member pages: home (dashboard), profile, fees, receipts, finance, payment-approval
 */
define('ROOT_PATH', __DIR__ . '/../');
require_once ROOT_PATH . 'config/config.php';

$basePath = '../';
$page = $_GET['page'] ?? 'home';

$allowed = [
    'home',
    'profile',
    'fees',
    'receipts',
    'finance',
    'payment-approval'
];

if (in_array($page, $allowed)) {
    include ROOT_PATH . 'views/member/pages/' . $page . '.php';
} else {
    header('Location: ../');
    exit;
}
