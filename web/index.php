<?php
/**
 * Web Router — /web/?page=xxx
 * Public pages: news, news-detail, activities, activity-detail
 */
define('ROOT_PATH', __DIR__ . '/../');
require_once ROOT_PATH . 'config/config.php';
require_once ROOT_PATH . 'config/database.php';

$basePath = '../';
$page = $_GET['page'] ?? '';

$allowed = [
    'news',
    'news-detail',
    'activities',
    'activity-detail',
    'activity-participants',
    'dynamic',
    'privacy-policy'
];

if (in_array($page, $allowed)) {
    include ROOT_PATH . 'views/web/pages/' . $page . '.php';
} elseif ($page !== '') {
    // Check if page matches a nav alias → load as dynamic page
    require_once ROOT_PATH . 'config/database.php';
    $db = getDB();
    $nav = $db->get('nav_items', ['alias', 'page_id'], ['alias' => $page, 'is_active' => 1]);

    if ($nav && $nav['page_id']) {
        $slug = $db->get('pages', 'slug', ['id' => $nav['page_id']]);
        if ($slug) {
            $_GET['slug'] = $slug;
            include ROOT_PATH . 'views/web/pages/dynamic.php';
        } else {
            header('Location: ../');
            exit;
        }
    } else {
        header('Location: ../');
        exit;
    }
} else {
    header('Location: ../');
    exit;
}
