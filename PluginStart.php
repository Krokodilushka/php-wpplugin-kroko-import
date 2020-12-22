<?php

/*
  Plugin Name: Kroko Import
  Description: Kroko kroko kroko Import import import
  Author: Krokodilushka
  Version: 1.0
 */

if (!function_exists("get_option") || !function_exists("add_filter")) {
    wp_die();
}
spl_autoload_register('autoload');

function autoload($className) {
    if (strpos($className, 'KrokoImport')===false){
        return;
    }
    $dir = plugin_dir_path(__FILE__);
    $path = $dir . str_replace('\\', '/', $className) . '.php';
    if (file_exists($path)) {
        include_once $path;
    }
}

function setupAdminMenu() {
    add_menu_page('Kroko Import', 'Kroko Import', 'manage_options', 'kroko-import-main-menu', array('\KrokoImport\Controller', 'controller'));
}


add_action('admin_menu', 'setupAdminMenu');
add_action('init', '\KrokoImport\Controller::checkUpdatesByCron');
add_filter('get_the_post_thumbnail_url', 'custom_thumbnail_tag_filter', 10, 3);
function custom_thumbnail_tag_filter($html, $postid, $thumbnailid) {
	echo 1;
    if (!$thumbnailid) {
        $src = get_post_meta($postid, 'image_url', true);
        if ($src) {$html = "<img src='" . $src . "'>";}
    }
    return $html;
}