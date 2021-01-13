<?php


namespace KrokoImport;


use KrokoImport\Controller\Error_Controller;
use KrokoImport\Controller\Feed_Controller;
use KrokoImport\Controller\Import_Controller;
use KrokoImport\Controller\Index_Controller;
use KrokoImport\Exceptions\Exception;
use KrokoImport\Model\Holder;

class Route
{

    public static function route()
    {
        try {
            $holder = new Holder;
            if (!is_null(filter_input(INPUT_GET, Constants::ROUTE_FEED))) {
                $feedController = new Feed_Controller($holder);
                if (!is_null(filter_input(INPUT_GET, Constants::ROUTE_FEED_CREATE))) {
                    echo $feedController->create();
                } else if (!is_null(filter_input(INPUT_GET, Constants::ROUTE_FEED_UPDATE))) {
                    echo $feedController->update();
                } else if (!is_null(filter_input(INPUT_GET, Constants::ROUTE_FEED_DELETE))) {
                    echo $feedController->delete();
                } else if (!is_null(filter_input(INPUT_GET, Constants::ROUTE_FEED_SHOW_POSTS))) {
                    echo $feedController->showPosts();
                } else if (!is_null(filter_input(INPUT_GET, Constants::ROUTE_FEED_SAVE))) {
                    echo $feedController->save();
                } else {
                    throw new \Exception('action not found');
                }
            } else if (!is_null(filter_input(INPUT_GET, Constants::ROUTE_IMPORT))) {
                $postController = new Import_Controller($holder);
                if (!is_null(filter_input(INPUT_GET, Constants::ROUTE_IMPORT_MANUAL))) {
                    echo $postController->manual();
                } else {
                    throw new \Exception('action not found');
                }
            } else {
                if (!is_null(filter_input(INPUT_GET, Constants::ROUTE_FEED_DROP_ALL))) {
                    $holder->getFeedStorage()->clearDB();
                }
                echo (new Index_Controller($holder))->listFeeds();
            }

        } catch (Exception $e) {
            echo (new Error_Controller($holder))->error($e);
        }
    }

    public
    static function pluginUrlPath(): string
    {
        return admin_url('admin.php?page=' . Constants::PLUGIN_URL_SLUG);
    }
}