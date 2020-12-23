<?php


namespace KrokoImport;


use KrokoImport\Controller\Controller;
use KrokoImport\Exceptions\Exception;

class Route
{

    private static $_controller;

    // все действия распределяются отсюда
    public static function route()
    {
        if (is_null(self::$_controller)) {
            self::$_controller = new Controller();
        }
        try {
            // create page
            $newXml = filter_input(INPUT_POST, 'new_xml');
            $xmlUrl = filter_input(INPUT_POST, 'xml_url');
            if ($newXml !== NULL && $xmlUrl !== NULL) {
                self::$_controller->feedOptionsInsert($xmlUrl);
            } else {
                // insert, update, delete
                $getFeedID = filter_input(INPUT_GET, 'feed_id');
                $getDropFeeds = filter_input(INPUT_GET, 'drop_feeds');
                $getUpdateFeedID = filter_input(INPUT_GET, 'update_posts');
                $postDeleteFeed = filter_input(INPUT_POST, 'delete_feed', FILTER_VALIDATE_BOOLEAN);
                $updateFeedSettings = filter_input(INPUT_POST, 'update_feed_settings', FILTER_VALIDATE_BOOLEAN);
                $feedID = filter_input(INPUT_POST, 'feed_id', FILTER_VALIDATE_INT);
                $feedUrl = filter_input(INPUT_POST, 'feed_url', FILTER_VALIDATE_URL);
                $feedTitle = filter_input(INPUT_POST, 'feed_title');
                $feedIntervalMin = filter_input(INPUT_POST, 'feed_interval_min', FILTER_VALIDATE_INT);
                $onExistsUpdate = filter_input(INPUT_POST, 'on_exists_update', FILTER_VALIDATE_BOOLEAN);
                if ($getFeedID !== NULL && $postDeleteFeed !== NULL) {
                    self::$_controller->feedOptionsOnDelete($getFeedID);
                } else if ($updateFeedSettings !== NULL && $feedUrl !== NULL && $feedTitle !== NULL) {
                    self::$_controller->feedOptionsOnInsertOrUpdate($feedID, $feedUrl, $feedTitle, $feedIntervalMin, $onExistsUpdate);
                } else if ($getFeedID !== NULL) {
                    self::$_controller->feedOptionsUpdate($getFeedID);
                } else if ($getDropFeeds !== NULL) {
                    self::$_controller->dropFeeds();
                    self::$_controller->options();
                } else if ($getUpdateFeedID !== NULL) {
                    self::$_controller->updatePosts($getUpdateFeedID);
                } else {
                    self::$_controller->options();
                }
            }
        } catch (Exception $e) {
            self::$_controller->error($e);
        }
    }
}