<?php
/**
 * Auth Router — /auth/?page=xxx
 * Auth pages: login, register, forgetpass
 */
define('ROOT_PATH', __DIR__ . '/../');
require_once ROOT_PATH . 'config/config.php';

$basePath = '../';
$page = $_GET['page'] ?? '';

$allowed = [
    'login',
    'register',
    'forgetpass',
    'resetpass'
];

if (in_array($page, $allowed)) {
    include ROOT_PATH . 'views/auth/pages/' . $page . '.php';
} else {
    header('Location: ../');
    exit;
}
