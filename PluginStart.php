<?php

/*
  Plugin Name: Kroko Import
  Description: Импорт постов и комментариев
  Author: Krokodilushka
  Version: 1.0
 */

use KrokoImport\Constants;
use KrokoImport\Model\FeedStorage;
use KrokoImport\Model\Import\Import;
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

// время для крон задачи
add_filter('cron_schedules', function ($schedules) {
    $schedules['krokoimport_interval'] = array(
        'interval' => Constants::CRON_INTERVAL_SEC,
        'display' => __('Every ' . Constants::CRON_INTERVAL_SEC . ' sec')
    );
    return $schedules;
});
// если нет крона, то запланировать
$cronNextTime = wp_next_scheduled(Constants::CRON_NEW_POST_HOOK_NAME, []);
if ($cronNextTime === false) {
    wp_schedule_event(time() + Constants::CRON_INTERVAL_SEC, 'krokoimport_interval', Constants::CRON_NEW_POST_HOOK_NAME, []);
}
add_action(Constants::CRON_NEW_POST_HOOK_NAME, function () {
    $feedStorage = new FeedStorage();
    $allFeeds = $feedStorage->getAll();
    if (!empty($allFeeds)) {
        $import = new Import;
        /** @var \KrokoImport\Data\FeedOptions $feed */
        foreach ($allFeeds as $feed) {
            $lastUpdateTime = $feed->leftUntilUpdateSec();
            $dt1 = new DateTime("@0");
            $interval = $dt1->diff(new DateTime("@" . $feed->leftUntilUpdateSec()))->format(' %aд %hч %iм %sс');
            echo 'До обновления: ' . $interval . ' [' . $feed->getID() . ': ' . $feed->getTitle() . "]\n";
            if ($lastUpdateTime == 0) {
                $import->processFeed($feed);
                $feedStorage->setLastUpdateTime($feed->getID());
                print_r($import->getLogs());
                $import->clearLogs();
            }
        }
    }
});

// добавление элемента меню
add_action('admin_menu', function () {
    add_menu_page(
        'Импорт постов и комметариев',
        'Импорт постов и комметариев',
        'manage_options',
        Constants::PLUGIN_URL_SLUG,
        [Route::class, 'route']
    );
});

// изменения контента для разных типов xml
add_filter('the_content', function ($content) {
    $newContent = '';
    $youtubeVideoId = get_post_meta(get_the_ID(), 'youtube_video_id', true);
    if (!empty($youtubeVideoId)) {
        $newContent .= ' < div class="youtube_video" >
        <iframe width = "560" height = "315" src = "https://www.youtube.com/embed/' . $youtubeVideoId . '" frameborder = "0" allow = "accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen ></iframe >
        </div > ';
    }
    $newContent .= $content;
    return $newContent;
});