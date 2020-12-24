<?php

/*
  Plugin Name: Kroko Import
  Description: Импорт постов и комментариев
  Author: Krokodilushka
  Version: 1.0
 */

use KrokoImport\Constants;
use KrokoImport\Route;

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
        Constants::PLUGIN_URL_SLUG,
        [Route::class, 'route']
    );
});
add_filter('the_content', function ($content) {
    $newContent = '';
    $youtubeVideoId = get_post_meta(get_the_ID(), 'youtube_video_id', true);
    if (!empty($youtubeVideoId)) {
        $newContent .= '<div class="youtube_video">
        <iframe width="560" height="315" src="https://www.youtube.com/embed/' . $youtubeVideoId . '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>';
    }
    $newContent .= $content;
    return $newContent;
});
//add_filter('get_the_post_thumbnail_url', function ($html, $postid, $thumbnailid) {
//    if (!$thumbnailid) {
//        $src = get_post_meta($postid, 'image_url', true);
//        if ($src) {
//            $html = "<img src='" . $src . "'>";
//        }
//    }
//    return $html;
//}, 10, 3);