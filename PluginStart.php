<?php

/*
  Plugin Name: Kroko Import
  Description: Импорт постов и комментариев
  Author: Krokodilushka
  Version: 1.0
 */

if (!function_exists("get_option") || !function_exists("add_filter")) {
    wp_die();
}
spl_autoload_register(function ($className) {
    if (strpos($className, 'KrokoImport') === false) {
        return;
    }
    $dir = plugin_dir_path(__FILE__);
    $path = $dir . str_replace('\\', '/', $className) . '.php';
    if (file_exists($path)) {
        include_once $path;
    }
});

//add_action('init', '\KrokoImport\Controller::checkUpdatesByCron');
add_action('admin_menu', function () {
    add_menu_page(
        'Импорт постов и комметариев',
        'Импорт постов и комметариев',
        'manage_options',
        'kroko-import-main-menu',
        [\KrokoImport\Route::class, 'route']
    );
});
//add_filter('get_the_post_thumbnail_url', function ($html, $postid, $thumbnailid) {
//    echo 1;
//    if (!$thumbnailid) {
//        $src = get_post_meta($postid, 'image_url', true);
//        if ($src) {
//            $html = "<img src='" . $src . "'>";
//        }
//    }
//    return $html;
//}, 10, 3);